<?php
namespace ITRocks\Framework\Setting;

use ITRocks\Framework\Builder;

/**
 * Common property setting set with $properties
 */
trait Has_Properties
{

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var Property[] key is the path of the property
	 */
	public array $properties = [];

	//----------------------------------------------------------------------------------- addProperty
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $add_property_path   string
	 * @param $where               string @values static::const,
	 * @param $where_property_path string reference property path for $where
	 * @return Property
	 */
	public function commonAddProperty(
		string $add_property_path, string $where, string $where_property_path
	) : Property
	{
		/** @noinspection PhpUnhandledExceptionInspection ::class */
		$add_property = $this->properties[$add_property_path]
			?? Builder::create($this->getPropertyClassName(), [$this->getClassName(), $add_property_path]);
		$properties = [];
		if (($where === self::BEFORE) && empty($where_property_path)) {
			$properties[$add_property_path] = $add_property;
		}
		foreach ($this->properties as $property_path => $property) {
			if (($where === self::BEFORE) && ($property_path === $where_property_path)) {
				$properties[$add_property_path] = $add_property;
			}
			if ($property_path !== $add_property_path) {
				$properties[$property_path] = $property;
			}
			if (($where === self::AFTER) && ($property_path === $where_property_path)) {
				$properties[$add_property_path] = $add_property;
			}
		}
		if (($where === self::AFTER) && empty($where_property_path)) {
			$properties[$add_property_path] = $add_property;
		}

		$this->properties = $properties;
		return $add_property;
	}

	//-------------------------------------------------------------------------- commonInitProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $filter_properties string[] property path
	 * @return Property[]
	 */
	protected function commonInitProperties(array $filter_properties = null) : array
	{
		if ($this->properties) {
			return $this->properties;
		}
		$class_name = $this->getClassName();
		if ($filter_properties) {
			foreach ($filter_properties as $property_path) {
				/** @see Property */
				$property_class = $this->getPropertyClassName();
				/** @noinspection PhpUnhandledExceptionInspection class */
				$this->properties[$property_path] = Builder::create(
					$property_class, [$class_name, $property_path]
				);
			}
			return $this->properties;
		}
		return [];
	}

	//-------------------------------------------------------------------------- getPropertyClassName
	/**
	 * @return string
	 */
	public function getPropertyClassName() : string
	{
		return lLastParse(get_class($this), BS) . '\Property';
	}

	//--------------------------------------------------------------------------- propertiesParameter
	/**
	 * Returns a list of a given parameter taken from properties
	 *
	 * @example $properties_display = $settings->propertiesParameter('display');
	 * @param $parameter string
	 * @return array key is the property path, value is the parameter value
	 */
	public function propertiesParameter(string $parameter) : array
	{
		$result = [];
		foreach ($this->properties as $property_path => $property) {
			$result[$property_path] = $property->$parameter;
		}
		return $result;
	}

	//--------------------------------------------------------------------------------- propertyTitle
	/**
	 * Sets the title of the property
	 *
	 * @param $property_path string
	 * @param $title         string if empty, the title is removed to get back to default
	 */
	public function propertyTitle(string $property_path, string $title = '')
	{
		$this->initProperties();
		if (isset($this->properties[$property_path])) {
			$this->properties[$property_path]->display = $title;
		}
	}

	//-------------------------------------------------------------------------------- removeProperty
	/**
	 * @param $property_path string
	 */
	public function removeProperty(string $property_path)
	{
		$this->initProperties();
		if (isset($this->properties[$property_path])) {
			unset($this->properties[$property_path]);
		}
	}

}
