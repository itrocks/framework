<?php
namespace SAF\Framework;

class Default_List_Controller_Configuration
{
	use Current { current as private pCurrent; }

	//------------------------------------------------------------------------------ $list_properties
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
				: array()
			);
	}

}
