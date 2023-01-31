<?php
namespace ITRocks\Framework\Reflection\Attribute;

use ReflectionAttribute;

trait Property_Has_Attributes
{
	use Has_Attributes { Has_Attributes::getAttributes as private getAttributesCommon; }

	//--------------------------------------------------------------------------------- getAttributes
	/**
	 * Gets the attributes list associated to the element
	 *
	 * Inheritable attributes : parent property attributes are scanned too.
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
		// TODO get #[Override] attributes here
		$attributes = $this->getAttributesCommon($name, $flags);
		$cache[$cache_key][$name ?: ''][$flags] = $attributes;
		if (
			$this->isAttributeLocal($name)
			|| ($attributes && !$this->isAttributeRepeatable($name))
			|| !($overridden_property = $this->getOverriddenProperty())
		) {
			return $attributes;
		}
		$this->mergeAttributes($attributes, $name, $overridden_property->getAttributes($name, $flags));
		return $attributes;
	}

}
