<?php
namespace ITRocks\Framework\Locale\Translation;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Validate\Property\Max_Length;
use ITRocks\Framework\Locale\Language;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Attribute\Class_\Representative;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Reflection\Attribute\Property\Multiline;
use ITRocks\Framework\Reflection\Attribute\Property\Setter;

/**
 * Application data translation
 */
#[Representative('class_name', 'property_name', 'language.code', 'translation')]
#[Store('data_translations')]
class Data
{

	//----------------------------------------------------------------------------------- $class_name
	/** Needed into storage for abstraction of object */
	#[Mandatory]
	public string $class_name;

	//------------------------------------------------------------------------------------- $language
	public Language $language;

	//--------------------------------------------------------------------------------------- $object
	#[Getter, Mandatory, Setter]
	public object $object;

	//-------------------------------------------------------------------------------- $property_name
	#[Mandatory]
	public string $property_name;

	//---------------------------------------------------------------------------------- $translation
	#[Mandatory, Max_Length(50000), Multiline]
	public string $translation = '';

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->class_name
			? join(
				SP, [$this->class_name, $this->property_name, $this->language->code, $this->translation]
			)
			: '';
	}

	//------------------------------------------------------------------------------------- getObject
	protected function getObject() : object
	{
		if (!is_object($this->object) && !empty($this->id_object) && $this->class_name) {
			$this->object = Dao::read($this->id_object, $this->class_name);
			unset($this->id_object);
		}
		return $this->object;
	}

	//------------------------------------------------------------------------------------- setObject
	/** @noinspection PhpUnused #Setter */
	protected function setObject(object $value) : void
	{
		$this->class_name = Builder::current()->sourceClassName(get_class($value));
		$this->object     = $value;
	}

	//-------------------------------------------------------------------------------------------- tr
	/** Quick value search and translate */
	public static function tr(object $object, string $property_name, string $language = null) : string
	{
		if (!$language) {
			$language = Loc::language();
		}
		$class_name  = Builder::current()->sourceClassName(get_class($object));
		$translation = Dao::searchOne(
			[
				'class_name'    => $class_name,
				'language.code' => $language,
				'object'        => $object,
				'property_name' => $property_name
			],
			static::class
		);
		return $translation ? $translation->translation : $object->$property_name;
	}

}
