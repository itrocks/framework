<?php
namespace ITRocks\Framework\Reflection\Attribute;

use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Attribute;

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
	 * - !Attribute::IS_REPEATABLE attributes : a single Reflection_Attribute.
	 * - Attribute::IS_REPEATABLE attributes : an array of Reflection_Attribute.
	 *
	 * @param $name  string|null
	 * @param $flags integer
	 * @param $final Reflection_Property|null
	 * @return Reflection_Attribute[]|Reflection_Attribute[][]
	 */
	public function getAttributes(
		?string $name = null, int $flags = 0, Reflection_Property $final = null
	) : array
	{
		static $cache = [];
		$cache_key = strval($this);
		if (isset($cache[$cache_key][$name ?: ''][$flags])) {
			return $cache[$cache_key][$name ?: ''][$flags];
		}
		// TODO get #[Override] attributes here
		$attributes = $this->getAttributesCommon($name, $flags, $final ?: $this);
		$cache[$cache_key][$name ?: ''][$flags] = $attributes;
		if (
			!$this->isAttributeLocal($name)
			&& !($attributes && !$this->isAttributeRepeatable($name))
			&& ($overridden_property = $this->getOverriddenProperty())
		) {
			$this->mergeAttributes(
				$attributes, $name, $overridden_property->getAttributes($name, $flags, $final)
			);
		}
		return $attributes;
	}

}
