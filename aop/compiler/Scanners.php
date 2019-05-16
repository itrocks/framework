<?php
namespace ITRocks\Framework\AOP\Compiler;

use ITRocks\Framework\AOP\Weaver\Handler;
use ITRocks\Framework\Mapper\Getter;
use ITRocks\Framework\PHP\Reflection_Class;
use ITRocks\Framework\Reflection\Annotation\Property\Getter_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Tools\Names;

/**
 * Some methods that scan for getters, setters, overrides, etc. globally properties annotations
 */
trait Scanners
{

	//------------------------------------------------------------------------------- scanForDefaults
	/**
	 * @param $properties array
	 * @param $class      Reflection_Class
	 */
	private function scanForDefaults(array &$properties, Reflection_Class $class)
	{
		foreach ($class->getProperties([]) as $property) {
			$expr = '%'
				. '\n\s+\*\s+'                // each line beginning by '* '
				. '@default'                  // default annotation
				. '(?:\s+(?:([\\\\\w]+)::)?'  // 1 : class name
				. '(\w+)?)?'                  // 2 : method or function name
				. '%';
			preg_match($expr, $property->getDocComment(), $match);
			if ($match) {
				$advice = [
					empty($match[1]) ? '$this' : $match[1],
					$match[2]
				];
				$properties[$property->name]['default'] = $advice;
			}
		}
		foreach ($this->scanForOverrides($class->getDocComment(), ['default']) as $match) {
			$advice = [
				empty($match['class_name']) ? '$this' : $match['class_name'],
				$match['method_name']
			];
			$properties[$match['property_name']]['default'] = $advice;
		}
	}

	//-------------------------------------------------------------------------------- scanForGetters
	/**
	 * @param $properties array
	 * @param $class      Reflection_Class
	 */
	private function scanForGetters(array &$properties, Reflection_Class $class)
	{
		foreach ($class->getProperties([]) as $property) {
			$expr = '%'
				. '\n\s+\*\s+'               // each line beginning by '* '
				. '@getter'                  // getter annotation
				. '(?:\s+(?:([\\\\\w]+)::)?' // 1 : class name
				. '(\w+)?)?'                 // 2 : method or function name
				. '%';
			preg_match($expr, $property->getDocComment(), $match);
			if ($match) {
				$advice = [
					empty($match[1]) ? '$this' : $class->fullClassName($match[1]),
					empty($match[2]) ? Names::propertyToMethod($property->name, 'get') : $match[2]
				];
				$properties[$property->name][] = [Handler::READ, $advice];
			}
		}
		$overrides = $this->scanForOverrides($class->getDocComment(), ['getter']);
		foreach ($overrides as $match) {
			$advice = [
				empty($match['class_name']) ? '$this' : $match['class_name'],
				empty($match['method_name'])
					? Names::propertyToMethod($match['property_name'], 'get') : $match['method_name']
			];
			$properties[$match['property_name']][] = [Handler::READ, $advice];
		}
	}

	//---------------------------------------------------------------------------------- scanForLinks
	/**
	 * @param $properties array
	 * @param $class      Reflection_Class
	 */
	private function scanForLinks(array &$properties, Reflection_Class $class)
	{
		$disable = [];
		foreach ($properties as $property_name => $advices) {
			foreach ($advices as $key => $advice) if (is_numeric($key)) {
				if (is_array($advice) && (reset($advice) === Handler::READ)) {
					$disable[$property_name] = true;
					break;
				}
			}
		}
		foreach ($class->getProperties([]) as $property) {
			if (!isset($disable[$property->name]) && strpos($property->getDocComment(), '* @link')) {
				$expr = '%'
					. '\n\s+\*\s+'                           // each line beginning by '* '
					. '@link\s+'                             // link annotation
					. '(All|Collection|DateTime|Map|Object)' // 1 : link keyword
					. '%';
				preg_match($expr, $property->getDocComment(), $match);
				if ($match) {
					$advice = [Getter::class, 'get' . $match[1]];
				}
				else {
					trigger_error(
						'@link of ' . $property->class->name . '::' . $property->name
						. ' must be All, Collection, DateTime, Map or Object',
						E_USER_ERROR
					);
					$advice = null;
				}
				$properties[$property->name][] = [Handler::READ, $advice];
			}
		}
		$annotations = [Link_Annotation::ANNOTATION];
		foreach ($this->scanForOverrides($class->getDocComment(), $annotations, $disable) as $match) {
			$advice = [Getter::class, 'get' . $match['method_name']];
			$properties[$match['property_name']][] = [Handler::READ, $advice];
		}
	}

	//------------------------------------------------------------------------------ scanForOverrides
	/**
	 * @param $documentation string
	 * @param $annotations   string[]
	 * @param $disable       array
	 * @return array
	 */
	private function scanForOverrides(
		$documentation,
		array $annotations = [
			Getter_Annotation::ANNOTATION, Link_Annotation::ANNOTATION, 'replaces', 'setter'
		],
		array $disable = []
	) {
		$overrides = [];
		if (strpos($documentation, '@override')) {
			$expr = '%'
				. '\n\s+\*\s+'               // each line beginning by '* '
				. '@override\s+'             // override annotation
				. '(\w+)\s+'                 // 1 : property name
				. '(?:'                      // begin annotations loop
				. '(?:@.*?\s+)?'             // others overridden annotations
				. '@annotation'              // overridden annotation
				. '(?:\s+(?:([\\\\\w]+)::)?' // 2 : class name
				. '(\w*)?)?'                 // 3 : method or function name, or empty value
				. ')+'                       // end annotations loop
				. '%';
			foreach ($annotations as $annotation) {
				preg_match_all(
					str_replace('@annotation', AT . $annotation, $expr), $documentation, $matches
				);
				if ($matches[1]) {
					foreach ($matches[1] as $i => $match) {
						if (($annotation !== Link_Annotation::ANNOTATION) || !isset($disable[$match])) {
							if ($annotation === Getter_Annotation::ANNOTATION) {
								$disable[$match] = true;
							}
							$type     = ($annotation === 'setter') ? Handler::WRITE : Handler::READ;
							$override = [
								'class_name'    => $matches[2][$i],
								'method_name'   => $matches[3][$i],
								'property_name' => $matches[1][$i],
								'type'          => $type
							];
							if (!$override['class_name']) {
								$override['class_name'] = 'static';
							}
							if (!$override['method_name']) {
								$override['method_name'] = ($annotation === 'setter')
									? Names::propertyToMethod('set' . '_' . $override['property_name'])
									: Names::propertyToMethod('get' . '_' . $override['property_name']);
							}
							$overrides[] = $override;
						}
					}
				}
			}
		}
		return $overrides;
	}

	//------------------------------------------------------------------------------- scanForReplaces
	/**
	 * @param $properties array
	 * @param $class      Reflection_Class
	 */
	private function scanForReplaces(array &$properties, Reflection_Class $class)
	{
		foreach ($class->getProperties([T_USE]) as $property) {
			$expr = '%'
				. '\n\s+\*\s+'   // each line beginning by '* '
				. '@replaces\s+' // alias annotation
				. '(\w+)'        // 1 : property name
				. '%';
			preg_match_all($expr, $property->getDocComment(), $matches, PREG_SET_ORDER);
			if ($matches) {
				foreach ($matches as $match) {
					$properties[$match[1]]['replaced'] = $property->name;
				}
			}
		}
		foreach ($this->scanForOverrides($class->getDocComment(), ['replaces']) as $match) {
			$properties[$match['method_name']]['replaced'] = $match['property_name'];
		}
		// copy-paste getters and setters from replaced to replacement properties
		// TODO HIGH this is not enough : the getter with access an unset property. See unit test
		/*
		foreach ($properties as $advices) {
			if (isset($advices['replaced'])) {
				foreach ($advices as $advice_key => $advice) {
					if (is_numeric($advice_key)) {
						$properties[$advices['replaced']][] = $advice;
					}
					elseif ($advice_key === 'implements') {
						foreach ($advice as $implements_key => $implements) {
							$properties[$advices['replaced']]['implements'][$implements_key] = $implements;
						}
					}
				}
			}
		}
		*/
	}

	//-------------------------------------------------------------------------------- scanForSetters
	/**
	 * @param $properties array
	 * @param $class      Reflection_Class
	 */
	private function scanForSetters(array &$properties, Reflection_Class $class)
	{
		foreach ($class->getProperties([]) as $property) {
			$expr = '%'
				. '\n\s+\*\s+'               // each line beginning by '* '
				. '@setter'                  // setter annotation
				. '(?:\s+(?:([\\\\\w]+)::)?' // 1 : class name
				. '(\w+)?)?'                 // 2 : method or function name
				. '%';
			preg_match($expr, $property->getDocComment(), $match);
			if ($match) {
				$advice = [
					empty($match[1]) ? '$this' : $class->source->fullClassName($match[1]),
					empty($match[2]) ? Names::propertyToMethod($property->name, 'set') : $match[2]
				];
				$properties[$property->name][] = [Handler::WRITE, $advice];
			}
		}
		foreach ($this->scanForOverrides($class->getDocComment(), ['setter']) as $match) {
			$advice = [
				empty($match['class_name']) ? '$this' : $match['class_name'],
				empty($match['method_name'])
					? Names::propertyToMethod($match['property_name'], 'set') : $match['method_name']
			];
			$properties[$match['property_name']][] = [Handler::WRITE, $advice];
		}
	}

}
