<?php
namespace ITRocks\Framework\Reflection\Attribute;

use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Attribute;

trait Class_Has_Attributes
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
		$parent_class   = $this->getParentClass();
		$parent_classes = array_merge(
			$this->getTraits(),
			$this->getInterfaces(),
			$parent_class ? [$parent_class] : []
		);
		foreach ($parent_classes as $parent_class) {
			$this->mergeAttributes(
				$attributes, $name, $parent_class->getAttributes(
					$name,
					$flags,
					$final,
					(($parent_class->isClass() && !$parent_class->isAbstract()) ? $parent_class : $class)
				)
			);
		}
	}

}
