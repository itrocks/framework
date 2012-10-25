<?php
namespace SAF\Framework;

class Default_List_Controller_Configuration
{
	use Current;

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
		return parent::current($set_current);
	}

	//----------------------------------------------------------------------------- getListProperties
	public function getListProperties($class_name)
	{
		echo "getListProperties($class_name)<br>";
		if (!isset($this->getListProperties($class_name))) {
			$parents = array_merge(class_parents($class_name), class_uses($class_name));
			while ($parents) { 
				foreach ($parents as $class_name) {
					echo "- check $class_name<br>";
					if (isset($this->list_properties[$class_name])) {
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
		echo "- remember $class_name = " . print_r($this->list_properties[$class_name], true) . "<br>";
		return $this->list_properties[$class_name];
	}

}
