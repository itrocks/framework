<?php
namespace ITRocks\Framework\Reflection;

use ITRocks\Framework\Reflection\Attribute\Has_Attributes;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;

trait Reflection_Class_Common
{
	use Has_Attributes { Has_Attributes::getAttributes as private getAttributesCommon; }

	//--------------------------------------------------------------------------------- getAttributes
	/**
	 * Gets the attributes list associated to the element
	 *
	 * Inheritable attributes : parent (and interface and class) attributes are scanned too.
	 *
	 * The returned array key is the name of the attribute.
	 *
	 * The value of each returned array element is :
	 * - !Attribute::IS_REPEATABLE attributes : a single Reflection_Attribute.
	 * - Attribute::IS_REPEATABLE attributes : an array of Reflection_Attribute.
	 *
	 * @param $name  string|null
	 * @param $flags integer
	 * @param $final Reflection|null
	 * @param $class Reflection_Class|null
	 * @return Reflection_Attribute[]|Reflection_Attribute[][]
	 */
	public function getAttributes(
		string $name = null, int $flags = 0, Reflection $final = null, Reflection_Class $class = null
	) : array
	{
		static $cache = [];
		$cache_key    = strval($this);
		if (isset($cache[$cache_key][$name ?: ''][$flags])) {
			return $cache[$cache_key][$name ?: ''][$flags];
		}
		if (!$final) {
			$final = $this;
		}
		$attributes = $this->getAttributesCommon($name, $flags, $final, $class);
		if (!$this->isAttributeLocal($name) && (!$attributes || $this->isAttributeRepeatable($name))) {
			$this->mergeParentAttributes($attributes, $name, $flags, $final, $class);
		}
		if (($this === $final) && ($final === $class)) {
			$cache[$cache_key][$name ?: ''][$flags] = $attributes;
		}
		return $attributes;
	}

	//--------------------------------------------------------------------------- getParentClassNames
	/**
	 * @return string[]
	 */
	public function getParentClassNames() : array
	{
		return array_keys($this->getParentClasses());
	}

	//------------------------------------------------------------------------------ getParentClasses
	/**
	 * Returns all parent classes, interfaces and traits class names, ordered by appliance priority
	 *
	 * @return static[]
	 */
	public function getParentClasses() : array
	{
		$parents = $results = [$this->getName() => $this];
		while ($parents) {
			$next = [];
			foreach ($parents as $class) {
				$parent         = $class->getParentClass();
				$parent_parents = array_merge(
					$class->getTraits(), $class->getInterfaces(), $parent ? [$parent] : []
				);
				foreach ($parent_parents as $parent) {
					$name = $parent->getName();
					if (!isset($results[$name])) {
						$next[$name]    = $parent;
						$results[$name] = $parent;
					}
				}
			}
			$parents = $next;
		}
		return $results;
	}

	//------------------------------------------------------------------------- mergeParentAttributes
	/**
	 * @param $attributes Reflection_Attribute[]|Reflection_Attribute[][]
	 * @param $name       ?string
	 * @param $flags      integer
	 * @param $final      Reflection
	 * @param $class      Reflection_Class|null
	 */
	public function mergeParentAttributes(
		array &$attributes, ?string $name, int $flags, Reflection $final, Reflection_Class $class = null
	) : void
	{
		$parent  = $this->getParentClass();
		$parents = array_merge($this->getTraits(), $this->getInterfaces(), $parent ? [$parent] : []
		);
		foreach ($parents as $parent) {
			$this->mergeAttributes(
				$attributes, $name, $parent->getAttributes(
					$name, $flags, $final, (($parent->isClass() && !$parent->isAbstract()) ? $parent : $class)
				)
			);
		}
	}

}
