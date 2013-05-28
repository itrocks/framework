<?php
namespace SAF\Framework;

/**
 * A standard property add controller to store it into acls
 */
class Acls_Property_Add_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Controller_Parameters
	 * - first : the Acls_Properties class name
	 * - key 0 : context class name (ie a business object)
	 * - key 1 : context feature name (ie "output", "list")
	 * - keys 2... : the identifiers of the added elements (ie property names)
	 * - key "after" : the name of the property to add the property after (optional)
	 * - key "before" : the name of the property to add the property after (optional)
	 * @param $form array
	 * @param $files array
	 * @return null
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		/** @var $acls_properties Acls_Properties */
		$acls_properties = Builder::create($parameters->shiftUnnamed());
		$class_name = Namespaces::fullClassName($parameters->shiftUnnamed());
		if (!class_exists($class_name) || class_instanceof($class_name, 'SAF\Framework\Set')) {
			$class_name = Namespaces::fullClassName(Set::elementClassNameOf($class_name));
		}
		$acls_properties->context_class_name = $class_name;
		$context_feature_name = $parameters->shiftUnnamed();
		$objects = $parameters->getObjects();
		foreach ($parameters->getUnnamedParameters() as $property_name) {
			if (isset($objects["after"])) {
				$acls_properties->add($context_feature_name, $property_name, "after", $objects["after"]);
			}
			elseif (isset($objects["before"])) {
				$acls_properties->add($context_feature_name, $property_name, "before", $objects["before"]);
			}
			else {
				$acls_properties->add($context_feature_name, $property_name, "before");
			}
		}
	}

}
