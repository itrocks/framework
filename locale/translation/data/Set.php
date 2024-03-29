<?php
namespace ITRocks\Framework\Locale\Translation\Data;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Language;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Locale\Translation\Data;
use ITRocks\Framework\Reflection\Attribute\Class_\Display;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Reflection\Attribute\Property\Setter;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Tools\Names;

#[Display('translation data set')]
class Set
{

	//----------------------------------------------------------------------------------- $class_name
	public string $class_name;

	//------------------------------------------------------------------------------------- $elements
	/**
	 * @var Data[]
	 */
	#[Getter('getElements')]
	public array $elements;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @class class_name
	 */
	#[Setter('setObject')]
	public object $object;

	//-------------------------------------------------------------------------------- $property_name
	public string $property_name;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(
		object $object = null, string $property_name = null, array $elements = null
	) {
		if (isset($elements)) {
			$this->elements = $elements;
		}
		if (isset($object)) {
			$this->object = $object;
		}
		if (isset($property_name)) {
			$this->property_name = $property_name;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return trim($this->object . SP . Names::propertyToDisplay($this->property_name))
			. SP . Loc::tr('translations');
	}

	//----------------------------------------------------------------------------------- getElements
	/**
	 * Get elements that match the class_name + property_name couple (one per language)
	 *
	 * @return Data[]
	 */
	protected function getElements() : array
	{
		if ($this->elements) {
			return $this->elements;
		}
		$this->elements = Dao::search(
			[
				'class_name'    => $this->class_name,
				'object'        => $this->object,
				'property_name' => $this->property_name
			],
			Data::class,
			[Dao::key('language.code'), Dao::sort()]
		);
		foreach (Dao::readAll(Language::class, Dao::sort()) as $language) {
			if (!isset($this->elements[$language->code])) {
				$data                            = new Data();
				$data->object                    = $this->object;
				$data->language                  = $language;
				$data->property_name             = $this->property_name;
				$this->elements[$language->code] = $data;
			}
		}
		return $this->elements;
	}

	//------------------------------------------------------------------------------------- setObject
	protected function setObject(object $value) : void
	{
		$this->class_name = Builder::current()->sourceClassName(get_class($value));
		$this->object     = $value;
	}

	//------------------------------------------------------------------------------------- translate
	public function translate(
		Reflection_Property $property, string $value, string $language = null
	) : string
	{
		// pre-requisite : a property value containing the context object
		if (!($property instanceof Reflection_Property_Value) || $property->finalValue()) {
			return $value;
		}
		$translation = Dao::searchOne(
			[
				'class_name'    => Builder::current()->sourceClassName($property->getFinalClassName()),
				'object'        => $property->getObject(),
				'property_name' => $property->name,
				'language.code' => $language ?: Loc::language()
			],
			Data::class
		);
		return $translation?->translation ?: $value;
	}

}
