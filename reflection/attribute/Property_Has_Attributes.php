<?php
namespace ITRocks\Framework\Reflection\Attribute;

use ITRocks\Framework\Reflection\Attribute\Class_\Override;
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
		$cache_key    = strval($this);
		if (isset($cache[$cache_key][$name ?: ''][$flags])) {
			return $cache[$cache_key][$name ?: ''][$flags];
		}
		$attributes    = $this->getAttributesCommon($name, $flags, $final ?: $this);
		$is_repeatable = $this->isAttributeRepeatable($name);
		if (
			$this->isAttributeInheritable($name)
			&& !($attributes && $is_repeatable)
			&& ($overridden_property = $this->getParent())
		) {
			$this->mergeAttributes(
				$attributes, $name, $overridden_property->getAttributes($name, $flags, $final)
			);
		}
		$cache[$cache_key][$name ?: ''][$flags] = $attributes;
		// get overrides
		$property_name = $this->getName();
		$overrides     = $this->getFinalClass()->getAttributes(Override::class);
		$overrides     = reset($overrides);
		if (!$overrides) return $attributes;
		// keep property overrides only
		$overrides = array_filter(
			$overrides,
			function(Reflection_Attribute $override) use($property_name) {
				$arguments = $override->getArguments();
				return reset($arguments) === $property_name;
			}
		);
		if (!$overrides) return $attributes;
		// keep $name overrides only
		$override_attributes = [];
		if ($name) {
			$overrides = array_filter(
				$overrides,
				function(Reflection_Attribute $override)
				use($final, $is_repeatable, $name, &$override_attributes) {
					foreach (array_slice($override->getArguments(), 1) as $attribute) {
						if (!is_a($attribute, $name, true)) {
							continue;
						}
						$attribute = new Reflection_Attribute(
							$attribute, $this, $final ?: $this, $override->getDeclaringClass(false)
						);
						if ($is_repeatable) {
							$override_attributes[$name][] = $attribute;
						}
						elseif (!isset($override_attributes[$name])) {
							$override_attributes[$name] = $attribute;
						}
						return true;
					}
					return false;
				}
			);
			if (!$overrides) return $attributes;
		}
		else {
			foreach ($overrides as $override) {
				foreach (array_slice($override->getArguments(), 1) as $attribute) {
					$attribute = new Reflection_Attribute(
						$attribute, $this, $final ?: $this, $override->getDeclaringClass(false)
					);
					$attribute_name = $attribute->getName();
					if ($this->isAttributeRepeatable($attribute_name)) {
						$override_attributes[$attribute_name][] = $attribute;
					}
					elseif (!isset($override_attributes[$attribute_name])) {
						$override_attributes[$attribute_name] = $attribute;
					}
				}
			}
		}
		$this->mergeAttributes($attributes, $name, $override_attributes);
		return $attributes;
	}

}
