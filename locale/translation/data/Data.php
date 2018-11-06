<?php
namespace ITRocks\Framework\Locale\Translation;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Language;

/**
 * Application data translation
 *
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
	 * @param $object object
	 */
	protected function setObject($object)
	{
		$this->class_name = Builder::current()->sourceClassName(get_class($object));
		$this->object     = $object;
	}

}
