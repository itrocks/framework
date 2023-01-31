<?php
namespace ITRocks\Framework\Reflection\Attribute;

use ReflectionAttribute;

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
	 * - !Attribute::IS_REPEATABLE attributes : a single ReflectionAttribute.
	 * - Attribute::IS_REPEATABLE attributes : an array of ReflectionAttribute.
	 *
	 * @param $name  string|null
	 * @param $flags integer
	 * @return ReflectionAttribute[]|ReflectionAttribute[][]
	 */
	public function getAttributes(?string $name = null, int $flags = 0) : array
	{
		static $cache = [];
		$cache_key = strval($this);
		if (isset($cache[$cache_key][$name ?: ''][$flags])) {
			return $cache[$cache_key][$name ?: ''][$flags];
		}
		$attributes = $this->getAttributesCommon($name, $flags);
		$cache[$cache_key][$name ?: ''][$flags] = $attributes;
		if (
			$this->isAttributeLocal($name)
			|| ($attributes && !$this->isAttributeRepeatable($name))
		) {
			return $attributes;
		}
		$parent_class   = $this->getParentClass();
		$parent_classes = array_merge(
			$this->isInterface()                 ? [] : $this->getTraits(),
			$this->isTrait()                     ? [] : $this->getInterfaces(),
			($this->isTrait() || !$parent_class) ? [] : [$parent_class]
		);
		foreach ($parent_classes as $parent_class) {
			$this->mergeAttributes($attributes, $name, $parent_class->getAttributes($name, $flags));
		}
		return $attributes;
	}

}
