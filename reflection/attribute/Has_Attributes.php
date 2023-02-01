<?php
namespace ITRocks\Framework\Reflection\Attribute;

use Attribute;
use Error;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Attribute;
use ReflectionAttribute;
use ReflectionClass;

/**
 * For reflection elements that have attributes
 */
trait Has_Attributes
{

	//---------------------------------------------------------------------------------- getAttribute
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $name  class-string<T>
	 * @param $flags integer
	 * @return T|T[]|null
	 * @template T
	 */
	public function getAttribute(string $name, int $flags = 0) : array|object|null
	{
		static $cache = [];
		$cache_key    = strval($this);
		$class        = ($this instanceof Reflection_Property) ? $this->getFinalClass() : $this;
		if (isset($cache[$cache_key][$name])) {
			return $cache[$cache_key][$name];
		}
		$attributes = $this->getAttributes($name, $flags, $this, $class);
		$attributes = reset($attributes);
		if (!$attributes) {
			if ($this->isAttributeRepeatable($name)) {
				$attributes = [];
			}
			elseif (is_a($name, Reflection\Attribute::class, true)) {
				$attributes = new Reflection_Attribute($name, $this, $this, $class);
				/** @noinspection PhpUnhandledExceptionInspection is_a */
				$attributes = $attributes->newInstance(true);
			}
			else {
				$attributes = null;
			}
		}
		elseif (class_exists($name)) {
			if (is_array($attributes)) {
				foreach ($attributes as &$attribute) {
					/** @noinspection PhpUnhandledExceptionInspection class_exists */
					$attribute = $attribute->newInstance();
				}
			}
			else {
				/** @noinspection PhpUnhandledExceptionInspection class_exists */
				$attributes = $attributes->newInstance();
			}
		}
		$cache[$cache_key][$name] = $attributes;
		return $attributes;
	}

	//--------------------------------------------------------------------------------- getAttributes
	/**
	 * Gets the attributes list associated to the element
	 *
	 * _INHERITABLE attributes : parent (and interface and class) attributes are scanned too.
	 *
	 * The returned array key is the name of the attribute.
	 *
	 * The value of each returned array element is :
	 * - !Attribute::IS_REPEATABLE attributes : a single Reflection_Attribute.
	 * - Attribute::IS_REPEATABLE attributes : an array of Reflection_Attribute.
	 *
	 * @param $name  string|null
	 * @param $flags integer
	 * @param $final Interfaces\Reflection|null
	 * @param $class Interfaces\Reflection_Class|null
	 * @return Reflection_Attribute[]|Reflection_Attribute[][]
	 */
	public function getAttributes(
		string $name = null, int $flags = 0,
		Interfaces\Reflection $final = null,
		Interfaces\Reflection_Class $class = null
	) : array
	{
		$attributes = [];
		if (!$final) {
			$final = $this;
		}
		/** @noinspection PhpMultipleClassDeclarationsInspection All parents use Has_Attributes */
		foreach (parent::getAttributes($name, $flags) as $attribute) {
			if ((!$attribute instanceof Reflection_Attribute)) {
				$attribute = new Reflection_Attribute($attribute, $this, $final, $class);
			}
			$attribute_name = $attribute->getName();
			if ($attribute->isRepeatable()) {
				$attributes[$attribute_name][] = $attribute;
			}
			else {
				$attributes[$attribute_name] = $attribute;
			}
		}
		return $attributes;
	}

	//------------------------------------------------------------------------------ isAttributeLocal
	public function isAttributeLocal(?string $name) : bool
	{
		return $name
			&& class_exists($name)
			&& (new ReflectionClass($name))->getAttributes(Local::class);
	}

	//------------------------------------------------------------------------- isAttributeRepeatable
	public function isAttributeRepeatable(?string $name) : bool
	{
		/** @var $attribute ReflectionAttribute[] */
		return $name
			&& class_exists($name)
			&& ($attribute = (new ReflectionClass($name))->getAttributes(Attribute::class))
			&& ($attribute[0]->newInstance()->flags & Attribute::IS_REPEATABLE);
	}

	//------------------------------------------------------------------------------- mergeAttributes
	/**
	 * @param $attributes        Reflection_Attribute[]|Reflection_Attribute[][]
	 * @param $name              ?string
	 * @param $parent_attributes Reflection_Attribute[]|Reflection_Attribute[][]
	 */
	private function mergeAttributes(array &$attributes, ?string $name, array $parent_attributes)
		: void
	{
		foreach ($parent_attributes as $parent_name => $attribute) {
			if (isset($attributes[$parent_name])) {
				if (is_array($attribute)) {
					$attributes[$parent_name] = array_merge($attributes[$parent_name], $attribute);
				}
			}
			elseif ($name || !$attribute->isLocal()) {
				$attributes[$parent_name] = $attribute;
			}
		}
	}

	//------------------------------------------------------------------------------ newInstanceError
	public static function newInstanceError(Error $error) : bool
	{
		$message = $error->getMessage();
		return preg_match('/^Attribute class ".*" not found$/', $message)
			|| preg_match('/^Attempting to use non-attribute class ".*" as attribute$/', $message);
	}

}
