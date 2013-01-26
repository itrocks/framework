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

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove property from acls list
	 *
	 * @param $context_feature_name  string
	 * @param $property_name         string
	 */
	public function remove($context_feature_name, $property_name)
	{
		$prefix = $this->context_class_name . "." . $context_feature_name . ".properties.";
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
