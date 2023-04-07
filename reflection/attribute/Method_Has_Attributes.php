<?php
namespace ITRocks\Framework\Reflection\Attribute;

use ITRocks\Framework\Reflection\Interfaces\Reflection_Method;
use ITRocks\Framework\Reflection\Reflection_Attribute;

trait Method_Has_Attributes
{
	use Has_Attributes { Has_Attributes::getAttributes as private getAttributesCommon; }

	//--------------------------------------------------------------------------------- getAttributes
	/**
	 * Gets the attributes list associated to the element
	 *
	 * Inheritable attributes : parent method attributes are scanned too.
	 *
	 * The returned array key is the name of the attribute.
	 *
	 * The value of each returned array element is :
	 * - !Attribute::IS_REPEATABLE attributes : a single Reflection_Attribute.
	 * - Attribute::IS_REPEATABLE attributes : an array of Reflection_Attribute.
	 *
	 * @param $name  string|null
	 * @param $flags integer
	 * @param $final Reflection_Method|null
	 * @return Reflection_Attribute[]|Reflection_Attribute[][]
	 */
	public function getAttributes(
		?string $name = null, int $flags = 0, Reflection_Method $final = null
	) : array
	{
		static $cache = [];
		$cache_key    = strval($this);
		if (isset($cache[$cache_key][$name ?: ''][$flags])) {
			return $cache[$cache_key][$name ?: ''][$flags];
		}
		$attributes    = $this->getAttributesCommon($name, $flags, $final ?: $this);
		$is_repeatable = $this->isAttributeRepeatable($name);
		if (
			$this->isAttributeInheritable($name)
			&& !($attributes && $is_repeatable)
			&& ($overridden_method = $this->getParent())
		) {
			$this->mergeAttributes(
				$attributes, $name, $overridden_method->getAttributes($name, $flags, $final)
			);
		}
		$cache[$cache_key][$name ?: ''][$flags] = $attributes;
		return $attributes;
	}

}
