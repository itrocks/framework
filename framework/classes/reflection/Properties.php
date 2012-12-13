<?php
namespace SAF\Framework;

class Properties
{

	//----------------------------------------------------------------------------------- $properties
	/**
	 * List properties for each class
	 *
	 * @var multitype:multitype:string
	 */
	private $properties;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($parameters)
	{
		$this->properties = $parameters["list_properties"];
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param Default_List_Controller_Configuration $set_current
	 * @return Default_List_Controller_Configuration
	 */
	public static function current(Default_List_Controller_Configuration $set_current = null)
	{
		return self::pCurrent($set_current);
	}

	//----------------------------------------------------------------------------- getListProperties
	/**
	 * Gets list properties list from configuration file
	 *
	 * @param string $class_name
	 * @return multitype:string
	 */
	public function getListProperties($class_name)
	{
		$main_class_name = $class_name;
		if (!isset($this->list_properties[$class_name])) {
			$parents = array_merge(class_parents($class_name), class_uses($class_name));
			while ($parents) { 
					foreach ($parents as $class_name) {
					if (
						isset($this->list_properties[$class_name])
						|| isset($this->list_properties[Namespaces::shortClassName($class_name)])
					) {
						break 2;
					}
				}
				$next_parents = array();
				foreach ($parents as $parent) {
					$next_parents = array_merge($next_parents, class_parents($parent), class_uses($parent));
				}
				$parents = $next_parents;
			}
		}
		$properties = isset($this->list_properties[$class_name])
			? $this->list_properties[$class_name]
			: (
				isset($this->list_properties[Namespaces::shortClassName($class_name)])
				? $this->list_properties[Namespaces::shortClassName($class_name)]
				: array_keys(Reflection_Class::getInstanceOf($main_class_name)->getAllProperties())
			);
		return $properties;
	}

	//---------------------------------------------------------------------------- removeListProperty
	/**
	 * Remove a property from list properties list
	 *
	 * Configuration file is not written, this is changed for current session configuration only
	 * If no list properties existed for class name but existed for a parent / unnamed class, the change is made for the class name only
	 */
	public function removeListProperty($class_name, $property_name)
	{
		$list_properties = $this->getListProperties($class_name);
		$key = array_search($property_name, $list_properties);
		if ($key !== false) {
			unset($list_properties[$key]);
			$list_properties = array_values($list_properties);
			$this->setListProperties($class_name, $list_properties);
		}
	}

	//----------------------------------------------------------------------------- setListProperties
	/**
	 * Sets configuration list properties list to a given one
	 *
	 * Configuration file is not written, this is changed for current session configuration only
	 */
	public function setListProperties($class_name, $list_properties)
	{
		$this->list_properties[$class_name] = $list_properties;
		// TODO what is this ? no hard link please !
		$_SESSION["SAF\\Framework\\Configuration"]
			->Default_List_Controller_Configuration["list_properties"][$class_name]
			= $list_properties;
	}

}
