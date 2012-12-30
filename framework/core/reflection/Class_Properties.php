<?php
namespace SAF\Framework;

trait Class_Properties
{

	//----------------------------------------------------------------------------------- $properties
	/**
	 * List properties for each class
	 *
	 * @var multitype:multitype:string
	 */
	private $properties;

	//---------------------------------------------------------------------------- getClassProperties
	/**
	 * Gets properties names list from configuration, or from class definition is configuration is not set
	 *
	 * @param string $class_name
	 * @return multitype:string
	 */
	public function getClassProperties($class_name)
	{
		$main_class_name = $class_name;
		if (
			!isset($this->properties[$class_name])
			&& !isset($this->properties[Namespaces::shortClassName($class_name)])
		) {
			$parents = array_merge(class_parents($class_name), class_uses($class_name));
			while ($parents) {
				foreach ($parents as $class_name) {
					if (
						isset($this->properties[$class_name])
						|| isset($this->properties[Namespaces::shortClassName($class_name)])
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
		$properties = isset($this->properties[$class_name])
			? $this->properties[$class_name]
			: (
				isset($this->properties[Namespaces::shortClassName($class_name)])
				? $this->properties[Namespaces::shortClassName($class_name)]
				: array_keys(Reflection_Class::getInstanceOf($main_class_name)->getAllProperties())
			);
		return $properties;
	}

	//--------------------------------------------------------------------------- initClassProperties
	protected function initClassProperties($parameters)
	{
		$this->properties = $parameters["properties"];
	}

	//--------------------------------------------------------------------------- removeClassProperty
	/**
	 * Remove a property from the list for a given class
	 *
	 * Configuration file is not written, this is changed for current session configuration only
	 * If no list properties existed for class name but existed for a parent / unnamed class, the change is made for the class name only
	 */
	public function removeClassProperty($class_name, $property_name)
	{
		$properties = $this->getClassProperties($class_name);
		$key = array_search($property_name, $properties);
		if ($key !== false) {
			unset($properties[$key]);
			$properties = array_values($properties);
			$this->setClassProperties($class_name, $properties);
		}
	}

	//---------------------------------------------------------------------------- setClassProperties
	/**
	 * Sets properties list for a given class
	 *
	 * Configuration file is not written, this is changed for current session configuration only
	 */
	private function setClassProperties($class_name, $properties)
	{
		$this->properties[$class_name] = $properties;
		// TODO what is this ? no hard link please !
		$properties_class = Namespaces::shortClassName(get_class($this));
		$config = &$_SESSION["SAF\\Framework\\Configuration"]->$properties_class;
		$config["properties"][$class_name] = $properties;
	}

}
