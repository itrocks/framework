<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\PHP\Dependency\Tools;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Class_\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Sql\Builder\Select;
use ITRocks\Framework\Tools\Contextual_Mysqli;
use ITRocks\Framework\Tools\Namespaces;

/**
 * Builds View object with a structure matching the structure of a PHP abstract class / trait
 */
class View_Builder_Class
{
	use Property_Filter;

	//-------------------------------------------------------------------------- $exclude_class_names
	/**
	 * @var string[]
	 */
	public $exclude_class_names = [];

	//--------------------------------------------------------------------------------------- $mysqli
	/**
	 * @var Contextual_Mysqli
	 */
	private $mysqli;

	//----------------------------------------------------------------------------------------- build
	/**
	 * Builds View objects using a Php class definition
	 *
	 * A Php class becomes a View
	 * Non-static properties of the class will become Column objects
	 *
	 * @param $class_name string
	 * @param $mysqli     Contextual_Mysqli
	 * @return View[]
	 */
	public function build($class_name, Contextual_Mysqli $mysqli)
	{
		$this->excluded_properties  = [];
		$this->mysqli               = $mysqli;
		return $this->buildInternal($class_name);
	}

	//-------------------------------------------------------------------------------- buildClassView
	/**
	 * Builds a View object using a Php class definition
	 *
	 * This takes care of excluded properties, so buildLinkView() should be called
	 * before buildClassView().
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class Reflection_Class
	 * @return View
	 */
	private function buildClassView(Reflection_Class $class)
	{
		$view_name = Dao::current()->storeNameOf($class->name);
		$view      = new View($view_name);
		/** @var $properties Reflection_Property[] */
		$properties = $class->accessProperties();
		foreach ($properties as $property_name => $property) {
			if (in_array($property->name, $this->excluded_properties)) {
				unset($properties[$property_name]);
			}
		}
		/** @noinspection PhpUnhandledExceptionInspection */
		foreach (Tools::extendsUse($class->name) as $class_name) {
				/** @noinspection PhpUnhandledExceptionInspection valid class name */
			$sub_class = new Reflection_Class(Builder::className($class_name));
			if (
				!$sub_class->isAbstract()
				&& $this->mysqli->exists(Store_Name_Annotation::of($sub_class)->value)
			) {
				$source_class_name = Builder::current()->sourceClassName($sub_class->name);
				/** @var $sub_properties Reflection_Property[] */
				$sub_properties     = $sub_class->accessProperties();
				$sub_property_names = ['id', 'class' => Dao\Func::value($source_class_name)];
				foreach ($properties as $property_name => $property) {
					$sub_property = $sub_properties[$property_name];
					$sub_property_names[$property_name] = $this->filterProperty($sub_property)
						? $property_name
						: Dao\Func::value(null);
				}
				$select = new Select($sub_class->name, $sub_property_names);
				$view->select_queries[$source_class_name] = str_replace(LF, SP, $select->buildQuery());
			}
		}
		return $view;
	}

	//--------------------------------------------------------------------------------- buildInternal
	/**
	 * The internal build method builds View objects using a Php class definition
	 *
	 * It is the same than build(), but enables to add an additional field
	 * (link field for link classes)
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @return View[]
	 */
	private function buildInternal($class_name)
	{
		/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
		$class = new Reflection_Class($class_name);
		$link  = Class_\Link_Annotation::of($class)->value;
		$views = $link ? $this->buildLinkViews($link, $class_name) : [];
		if (!in_array($class_name, $this->exclude_class_names)) {
			$views[] = $this->buildClassView($class);
		}
		return $views;
	}

	//-------------------------------------------------------------------------------- buildLinkViews
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $link       string
	 * @param $class_name string
	 * @return View[]
	 */
	private function buildLinkViews($link, $class_name)
	{
		$view_builder_class                      = new View_Builder_Class();
		$view_builder_class->exclude_class_names = $this->exclude_class_names;

		$link_class_name = Namespaces::defaultFullClassName($link, $class_name);
		$views           = $view_builder_class->build($link_class_name, $this->mysqli);

		/** @noinspection PhpUnhandledExceptionInspection link class name is always valid */
		$this->excluded_properties = array_keys(
			(new Reflection_Class($link_class_name))->getProperties([T_EXTENDS, T_USE])
		);

		return $views;
	}

}