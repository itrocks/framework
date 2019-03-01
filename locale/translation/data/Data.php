<?php
namespace ITRocks\Framework\Locale\Translation;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Language;
use ITRocks\Framework\Locale\Loc;

/**
 * Application data translation
 *
 * @business
 * @representative class_name, property_name, language.code, translation
 * @store_name data_translations
 */
class Data
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * Needed into storage for abstraction of object
	 *
	 * @mandatory
	 * @var string
	 */
	public $class_name;

	//------------------------------------------------------------------------------------- $language
	/**
	 * @link Object
	 * @mandatory
	 * @var Language
	 */
	public $language;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @getter
	 * @mandatory
	 * @setter
	 * @var object
	 */
	public $object;

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @mandatory
	 * @var string
	 */
	public $property_name;

	//---------------------------------------------------------------------------------- $translation
	/**
	 * @mandatory
	 * @max_length 50000
	 * @multiline
	 * @var string
	 */
	public $translation = '';

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->class_name
			? join(
				SP, [$this->class_name, $this->property_name, $this->language->code, $this->translation]
			)
			: '';
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * @return object
	 */
	protected function getObject()
	{
		if (!is_object($this->object) && !empty($this->id_object) && $this->class_name) {
			$this->object = Dao::read($this->id_object, $this->class_name);
			unset($this->id_object);
		}
		return $this->object;
	}

	//------------------------------------------------------------------------------------- setObject
	/**
	 * @param $value object
	 */
	protected function setObject($value)
	{
		$this->class_name = Builder::current()->sourceClassName(get_class($value));
		$this->object     = $value;
	}

	//-------------------------------------------------------------------------------------------- tr
	/**
	 * quick value search and translate
	 *
	 * @param $object        object
	 * @param $property_name string
	 * @param $language      string
	 * @return string
	 */
	public static function tr($object, $property_name, $language = null)
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
