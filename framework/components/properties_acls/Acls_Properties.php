<?php
namespace SAF\Framework;

/**
 * This is the common class for storage of a properties list as acls
 */
abstract class Acls_Properties
{

	//--------------------------------------------------------------------------- $context_class_name
	/**
	 * @var string The context class name
	 */
	public $context_class_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $context_class_name string The context class name
	 */
	public function __construct($context_class_name = null)
	{
		if ($context_class_name != null) {
			$this->context_class_name = $context_class_name;
		}
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds property to acls list, after another existing propery
	 *
	 * @param $context_feature_name  string
	 * @param $property_name         string
	 * @param $where                 string 'before' or 'after'
	 * @param $where_property_name   string
	 */
	public function add($context_feature_name, $property_name, $where, $where_property_name = null)
	{
		if ($property_name == $where_property_name) return;
		$prefix = $this->getAclPrefix($context_feature_name);
		/** @var $properties integer[] key is the property path */
		$properties = $this->getPropertiesNames($context_feature_name);
		if (!isset($properties)) {
			$properties = $this->getDefaultProperties();
		}
		// unset property_name from properties as it may have moved
		if (($key = array_search($property_name, $properties)) !== false) {
			unset($properties[$key]);
		}
		// insert property_name into properties and recalc position for each of them
		$result = [];
		$count = 1;
		if (($where == 'after') && empty($where_property_name)) {
			$result[$count++] = $property_name;
		}
		foreach ($properties as $key) {
			if (($where == 'before') && ($key == $where_property_name)) {
				$result[$count++] = $property_name;
			}
			$result[$count++] = $key;
			if (($where == 'after') && ($key == $where_property_name)) {
				$result[$count++] = $property_name;
			}
		}
		if (($where == 'before') && empty($where_property_name)) {
			$result[$count] = $property_name;
		}
		// save properties list into user's group and loaded access rights
		$group = Acls_User::current()->group;
		foreach ($result as $position => $property_name) {
			Acls::set($prefix . $property_name, $position);
		}
		Dao::write($group);
	}

	//---------------------------------------------------------------------------------- getAclPrefix
	/**
	 * @param $context_feature_name string
	 * @return string
	 */
	public function getAclPrefix($context_feature_name)
	{
		return $this->context_class_name . DOT . $context_feature_name . '.properties.';
	}

	//-------------------------------------------------------------------------- getDefaultProperties
	/**
	 * @return string[]
	 */
	public function getDefaultProperties()
	{
		return array_keys((new Reflection_Class($this->context_class_name))->getAllProperties());
	}

	//---------------------------------------------------------------------------- getPropertiesNames
	/**
	 * Get properties names list from acls
	 *
	 * @param $context_feature_name string
	 * @return string[]|null
	 */
	public function getPropertiesNames($context_feature_name)
	{
		$list = Acls::get($this->context_class_name . DOT . $context_feature_name . '.properties');
		if (isset($list)) {
			$list = treeToArray($list);
			asort($list);
			return array_keys($list);
		}
		return null;
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove property from acls list
	 *
	 * @param $context_feature_name  string
	 * @param $property_name         string
	 */
	public function remove($context_feature_name, $property_name)
	{
		$prefix = $this->getAclPrefix($context_feature_name);
		$properties = $this->getPropertiesNames($context_feature_name);
		if (!isset($properties)) {
			// no acls properties : remove from default properties list (create acls properties)
			foreach ($this->getDefaultProperties() as $position => $property) {
				if ($property != $property_name) {
					Acls::set($prefix . $property, $position + 1);
				}
			}
			Dao::write(Acls_User::current()->group);
		}
		else {
			// acls properties : simple remove
			Acls::remove($prefix . $property_name, null, true);
		}
	}

}
