<?php
namespace SAF\Framework;

/**
 * The default list controller is called if no list controller has beed defined for a business object class
 */
class Default_List_Controller extends List_Controller
{

	//------------------------------------------------------------------------ addSessionSearchValues
	/**
	 * @param $class_name string
	 * @param $search     string[]
	 * @return string[]
	 */
	protected function addSessionSearchValues($class_name, $search)
	{
		foreach (
			Session::current()->get('SAF\Framework\Search_Values', true)->get($class_name)
			as $property_name => $value
		) {
			if (!isset($search[$property_name])) {
				$search[$property_name] = $value;
			}
		}
		return $search;
	}

	//----------------------------------------------------------------------------- addSessionReverse
	/**
	 * @param $class_name string the class name
	 * @param $reverse    string the property path to reverse sort order
	 */
	protected function addToSessionReverse($class_name, $reverse)
	{
		/** @var $sort_options Sort_Options */
		$sort_options = Session::current()->get('SAF\Framework\Sort_Options', true);
		if ($reverse) {
			$sort_options->reverse($class_name, $reverse);
		}
	}

	//-------------------------------------------------------------------------------- addSessionSort
	/**
	 * @param $class_name string the class name
	 * @param $sort       string the property path to add to sort list
	 */
	protected function addToSessionSort($class_name, $sort)
	{
		/** @var $sort_options Sort_Options */
		$sort_options = Session::current()->get('SAF\Framework\Sort_Options', true);
		if ($sort) {
			$sort_options->add($class_name, $sort);
		}
	}

	//----------------------------------------------------------------------------------- descapeForm
	/**
	 * @param $form string[]
	 * @return string[]
	 */
	protected function descapeForm($form)
	{
		$result = array();
		foreach ($form as $property_name => $value) {
			$property_name = $this->descapePropertyName($property_name);
			$result[$property_name] = $value;
		}
		return $result;
	}

	//--------------------------------------------------------------------------- descapePropertyName
	/**
	 * @param $property_name string
	 * @return string
	 */
	protected function descapePropertyName($property_name)
	{
		$property_name = str_replace(".id_", ".", str_replace(">", ".", $property_name));
		if (substr($property_name, 0, 3) == "id_") {
			$property_name = substr($property_name, 3);
		}
		return $property_name;
	}

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $class_name string object or class name
	 * @param $parameters string[] parameters
	 * @return Button[]
	 */
	protected function getGeneralButtons($class_name, $parameters)
	{
		return array(
			new Button("Add", View::link($class_name, "new"), "add", Color::of("green"))
		);
	}

	//----------------------------------------------------------------------------- getPropertiesList
	/**
	 * @param $class_name string
	 * @return string[] property names list
	 */
	protected function getPropertiesList($class_name)
	{
		return Reflection_Class::getInstanceOf($class_name)
			->getListAnnotation("representative")->values();
	}

	//------------------------------------------------------------------------------- getSearchValues
	/**
	 * Get search values from form's "search" array
	 *
	 * @param $class_name string element class name
	 * @param $form       string[] the values, key is the name/path of each property into the class
	 * @return Reflection_Property_Value[] Search values
	 */
	protected function getSearchValues($class_name, $form)
	{
		$form = $this->descapeForm($form);
		$this->saveSearchValuesToSession($class_name, $form);
		$search = array();
		foreach ($this->addSessionSearchValues($class_name, $form) as $property_name => $value) {
			if (strlen($value)) {
				$property = new Reflection_Property_Value($class_name, $property_name, $value, true);
				if ($property->getType()->isClass()) {
					$property->value(Dao::read($value, $property->getType()->asString()));
				}
				$search[$property_name] = $property;
			}
		}
		return $search;
	}

	//--------------------------------------------------------------------------- getSelectionButtons
	/**
	 * @param $class_name string
	 * @return Button[]
	 */
	protected function getSelectionButtons($class_name)
	{
		return array(
			new Button("Print", View::link($class_name, "print"), "print", array(
				"sub_buttons" => array(
					new Button(
						"Models",
						View::link(
							'SAF\Framework\Print_Models', "list", Namespaces::shortClassName($class_name)
						),
						"models",
						"#main"
					)
				)
			))
		);
	}

	//-------------------------------------------------------------------------------- getSessionSort
	/**
	 * @param $class_name string
	 * @return Dao_Sort_Option
	 */
	protected function getSessionSort($class_name)
	{
		/** @var $sort_options Sort_Options */
		$sort_options = Session::current()->get('SAF\Framework\Sort_Options', true);
		return $sort_options->get($class_name);
	}

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $class_name string
	 * @return mixed[]
	 */
	protected function getViewParameters(Controller_Parameters $parameters, $form, $class_name)
	{
		$parameters = $parameters->getObjects();
		$element_class_name = Namespaces::fullClassName(Set::elementClassNameOf($class_name));
		// properties
		$properties_list = $this->getPropertiesList($element_class_name);
		// sort option object
		if (isset($parameters["sort"])) {
			$this->addToSessionSort($element_class_name, $parameters["sort"]);
			unset($parameters["sort"]);
		}
		if (isset($parameters["reverse"])) {
			$this->addToSessionReverse($element_class_name, $parameters["reverse"]);
			unset($parameters["reverse"]);
		}
		$sort = $this->getSessionSort($element_class_name);
		// sorted classes
		$sorted = array();
		$sort_count = 0;
		foreach ($sort->getColumns() as $sort_column) {
			$sorted[$sort_column] = ++$sort_count;
		}
		// reversed classes
		$reversed = array();
		foreach ($sort->reverse as $reverse) {
			$reversed[$reverse] = "reverse";
		}
		// sort links
		$sort_options = array();
		foreach ($properties_list as $property_name) {
			$sort_options[$property_name] = "sort";
		}
		if (reset($sorted)) {
			$sort_options[key($sorted)] = "reverse";
		}
		// search
		$search_values = $this->getSearchValues($element_class_name, $form);
		$search = array_combine($properties_list, $properties_list);
		foreach ($search_values as $search_key => $search_value) {
			if (($search_value instanceof Reflection_Property_Value) && isset($search[$search_key])) {
				$search[$search_key] = $search_value;
			}
		}
		// read data
		$parameters = array_merge(
			array(
				$element_class_name => Dao::select(
					$element_class_name, $properties_list, $search_values, array($sort, Dao::limit(20))
				),
				"search"       => $search,
				"sorted"       => $sorted,
				"reversed"     => $reversed,
				"sort_options" => $sort_options
			),
			$parameters
		);
		// buttons
		$parameters["general_buttons"]   = $this->getGeneralButtons($element_class_name, $parameters);
		$parameters["selection_buttons"] = $this->getSelectionButtons($element_class_name);
		return $parameters;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default "list-typed" view controller
	 *
	 * @param $parameters Controller_Parameters
	 * @param $form array
	 * @param $files array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = $this->getViewParameters($parameters, $form, $class_name);
		return View::run($parameters, $form, $files, $class_name, "list");
	}

	//--------------------------------------------------------------------- saveSearchValuesToSession
	/**
	 * @param $class_name string
	 * @param $form       string[]
	 */
	protected function saveSearchValuesToSession($class_name, $form)
	{
		/** @var $search_values Search_Values */
		$search_values = Session::current()->get('SAF\Framework\Search_Values', true);
		foreach ($form as $property_name => $value) {
			if (strlen($value)) {
				$search_values->set($class_name, $property_name, $value);
			}
			else {
				$search_values->remove($class_name, $property_name);
			}
		}
	}

}
