<?php
namespace SAF\Framework;

class Default_List_Controller_Configuration
{
	use Current { current as private pCurrent; }

	//------------------------------------------------------------------------------ $list_properties
	/**
	 * List properties for each class
	 *
	 * @var multitype:multitype:string
	 */
	private $list_properties;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($parameters)
	{
		$this->list_properties = $parameters["list_properties"];
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
		return isset($this->list_properties[$class_name])
			? $this->list_properties[$class_name]
			: (
				isset($this->list_properties[Namespaces::shortClassName($class_name)])
				? $this->list_properties[Namespaces::shortClassName($class_name)]
				: array_keys(Reflection_Class::getInstanceOf($class_name)->getallProperties())
			);
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
		$key = in_array($property_name, $list_properties);
		if ($key) {
			unset($list_properties[$key]);
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
	}

}
