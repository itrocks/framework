<?php
namespace ITRocks\Framework\Reflection\Attribute;

use Attribute;
use Error;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection;
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
		$cache_key = strval($this);
		if (isset($cache[$cache_key][$name])) {
			return $cache[$cache_key][$name];
		}
		$attributes = $this->getAttributes($name, $flags);
		$attributes = reset($attributes);
		if (!$attributes) {
			if ($this->isAttributeRepeatable($name)) {
				$attributes = [];
			}
			elseif (is_a($name, Reflection\Attribute::class, true)) {
				/** @noinspection PhpAccessStaticViaInstanceInspection Inspector bug : $name is a string */
				/** @noinspection PhpUnhandledExceptionInspection is_a */
				$attributes = Builder::create(
					$name, method_exists($name, 'getDefaultArguments') ? $name::getDefaultArguments() : []
				);
				/** @var $attributes Reflection\Attribute */
				$attributes->setTarget($this);
			}
			else {
				$attributes = null;
			}
		}
		elseif (is_array($attributes)) {
			foreach ($attributes as &$attribute) {
				if (is_a($name, Reflection\Attribute::class, true)) {
					/** @noinspection PhpUnhandledExceptionInspection is_a */
					$attribute = Builder::create($name, $attribute->getArguments());
					/** @var $attribute Reflection\Attribute */
					$attribute->setTarget($this);
				}
				elseif (class_exists($name)) {
					/** @noinspection PhpUnhandledExceptionInspection class_exists */
					$attribute = Builder::create($name, $attribute->getArguments());
				}
			}
		}
		else {
			if (is_a($name, Reflection\Attribute::class, true)) {
				/** @noinspection PhpUnhandledExceptionInspection is_a */
				$attributes = Builder::create($name, $attributes->getArguments());
				/** @var $attribute Reflection\Attribute */
				$attributes->setTarget($this);
			}
			elseif (class_exists($name)) {
				/** @noinspection PhpUnhandledExceptionInspection class_exists */
				$attributes = Builder::create($name, $attributes->getArguments());
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
	 * - !Attribute::IS_REPEATABLE attributes : a single ReflectionAttribute.
	 * - Attribute::IS_REPEATABLE attributes : an array of ReflectionAttribute.
	 *
	 * @param $name  string|null
	 * @param $flags integer
	 * @return ReflectionAttribute[]|ReflectionAttribute[][]
	 */
	public function getAttributes(?string $name = null, int $flags = 0) : array
	{
		$attributes = [];
		/** @noinspection PhpMultipleClassDeclarationsInspection All parents use Has_Attributes */
		foreach (parent::getAttributes($name, $flags) as $attribute) {
			$attribute_name = $attribute->getName();
			if ($this->isAttributeRepeatable($attribute_name)) {
				$attributes[$attribute_name][] = $attribute;
			}
			else {
				$attributes[$attribute_name] = $attribute;
			}
		}
		return $attributes;
	}

	//----------------------------------------------------------------------------------- isAttribute
	public function isAttribute(?string $name) : bool
	{
		return $name
			&& class_exists($name)
			&& (new ReflectionClass($name))->getAttributes(Attribute::class);
	}

	//------------------------------------------------------------------------ isAttributeInheritable
	public function isAttributeInheritable(?string $name) : bool
	{
		return !$name
			|| !class_exists($name)
			|| !(new ReflectionClass($name))->getAttributes(Local::class);
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
	private function mergeAttributes(array &$attributes, ?string $name, array $parent_attributes)
		: void
	{
		foreach ($parent_attributes as $parent_name => $attribute) {
			if (isset($attributes[$parent_name])) {
				if (is_array($attribute)) {
					$attributes[$parent_name] = array_merge($attributes[$parent_name], $attribute);
				}
			}
			elseif ($name || !$this->isAttributeLocal($parent_name)) {
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
