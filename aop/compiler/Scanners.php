<?php
namespace ITRocks\Framework\AOP\Compiler;

use ITRocks\Framework\AOP\Weaver\Handler;
use ITRocks\Framework\Mapper;
use ITRocks\Framework\PHP\Reflection_Class;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Attribute\Property\Default_;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Reflection\Attribute\Property\Setter;
use ITRocks\Framework\Tools\Names;

/**
 * Some methods that scan for getters, setters, overrides, etc. globally properties annotations
 */
trait Scanners
{

	//------------------------------------------------------------------------------- scanForDefaults
	private function scanForDefaults(array &$properties, Reflection_Class $class) : void
	{
		foreach ($class->getProperties([]) as $property) {
			$advice = Default_::of($property)?->callable;
			if (!$advice) {
				continue;
			}
			$properties[$property->name]['default'] = $advice;
		}
	}

	//-------------------------------------------------------------------------------- scanForGetters
	private function scanForGetters(array &$properties, Reflection_Class $class) : void
	{
		foreach ($class->getProperties([]) as $property) {
			if (!($getter = Getter::of($property)->callable)) continue;
			$properties[$property->name][] = [Handler::READ, $getter];
		}
	}

	//---------------------------------------------------------------------------------- scanForLinks
	private function scanForLinks(array &$properties, Reflection_Class $class) : void
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
			if (isset($disable[$property->name])) {
				continue;
			}
			$type = $property->getType();
			if (!$type->isClass()) {
				continue;
			}
			$link_annotation = Link_Annotation::of($property);
			if (!$link_annotation->value) {
				continue;
			}
			$advice = [Mapper\Getter::class, 'get' . $link_annotation->value];
			$properties[$property->name][] = [Handler::READ, $advice];
		}
		$annotations = [Link_Annotation::ANNOTATION];
		foreach ($this->scanForOverrides($class->getDocComment([]), $annotations, $disable) as $match) {
			$advice = [Mapper\Getter::class, 'get' . $match['method_name']];
			$properties[$match['property_name']][] = [Handler::READ, $advice];
		}
	}

	//------------------------------------------------------------------------------ scanForOverrides
	/**
	 * @param $documentation string @values default, link, replaces
	 * @param $annotations   string[]
	 * @param $disable       array
	 * @return array
	 */
	private function scanForOverrides(string $documentation, array $annotations, array $disable = [])
		: array
	{
		if (!str_contains($documentation, '@override')) return [];
		$overrides = [];
		$expr      = '%'
			. '\*\s+'               // each line beginning by '* '
			. '@override\s+'             // override annotation
			. '(\w+)\s+'                 // 1 : property name
			. '(?:'                      // begin annotations loop
			. '(?:@.*?\s+)?'             // others overridden annotations
			. '@annotation\s'            // overridden annotation
			. '(?:\s*(?:([\\\\\w]+)::)?' // 2 : class name
			. '(\w*)?)?'                 // 3 : method or function name, or empty value
			. ')+'                       // end annotations loop
			. '%';
		foreach ($annotations as $annotation) {
			preg_match_all(str_replace('@annotation', AT . $annotation, $expr), $documentation, $matches);
			if (!$matches[1]) continue;
			foreach ($matches[1] as $i => $match) {
				if (isset($disable[$match])) continue;
				$type     = Handler::READ;
				$override = [
					'class_name'    => $matches[2][$i],
					'method_name'   => $matches[3][$i],
					'property_name' => $matches[1][$i],
					'type'          => $type
				];
				if (!$override['class_name']) {
					$override['class_name'] = 'static';
				}
				$overrides[] = $override;
			}
		}
		return $overrides;
	}

	//------------------------------------------------------------------------------- scanForReplaces
	private function scanForReplaces(array &$properties, Reflection_Class $class) : void
	{
		foreach ($class->getProperties([T_USE]) as $property) {
			$expr = '%'
				. '\*\s+'        // each line beginning by '* '
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
		foreach ($this->scanForOverrides($class->getDocComment([]), ['replaces']) as $match) {
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
	private function scanForSetters(array &$properties, Reflection_Class $class) : void
	{
		foreach ($class->getProperties([]) as $property) {
			if (!($setter = Setter::of($property))) continue;
			$properties[$property->name][] = [Handler::WRITE, $setter->callable];
		}
	}

}
