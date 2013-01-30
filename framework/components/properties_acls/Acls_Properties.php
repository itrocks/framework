<?php
namespace SAF\Framework;

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

	//-------------------------------------------------------------------------- getDefaultProperties
	/**
	 * @return string[]
	 */
	public function getDefaultProperties()
	{
		return array_keys(Reflection_Class::getInstanceOf($this->context_class_name)
			->getAllProperties());
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
		$list = Acls::get($this->context_class_name . "." . $context_feature_name . ".properties");
		return isset($list) ? array_keys(treeToArray($list)) : null;
	}

	//-------------------------------------------------------------------------------------- addAfter
	/**
	 * Adds property to acls list, after another existing propery
	 *
	 * @param $context_feature_name string
	 * @param $property_name        string
	 * @param $after_property_name  string
	 */
	public function addAfter($context_feature_name, $property_name, $after_property_name = null)
	{
		$prefix = $this->getAclPrefix($context_feature_name);
		$properties = $this->getPropertiesNames($context_feature_name);
		if (!isset($properties)) {
			$properties = $this->getDefaultProperties();
		}
		if (!in_array($property_name, $properties)) {
			Acls::add($prefix . $property_name, true);
			Dao::write(Acls_User::current()->getGroup());
		}
	}

	//------------------------------------------------------------------------------------- addBefore
	/**
	 * Adds property to acls list, before another existing propery
	 *
	 * @param $context_feature_name string
	 * @param $property_name        string
	 * @param $before_property_name string
	 */
	public function addBefore($context_feature_name, $property_name, $before_property_name = null)
	{
		echo "add $context_feature_name::$this->context_class_name : $property_name before $before_property_name";
	}

	//---------------------------------------------------------------------------------- getAclPrefix
	/**
	 * @param $context_feature_name string
	 * @return string
	 */
	public function getAclPrefix($context_feature_name)
	{
		return $this->context_class_name . "." . $context_feature_name . ".properties.";
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
		if (isset($properties)) {
			Acls::remove($prefix . $property_name, null, true);
		}
		else {
			// if no acls properties add all default properties but not the removed property
			$properties = $this->getDefaultProperties();
			foreach ($properties as $property) {
				if ($property != $property_name) {
					Acls::add($prefix . $property);
				}
			}
			Dao::write(Acls_User::current()->getGroup());
		}
	}

}
