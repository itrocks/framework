<?php
namespace SAF\Framework;

/**
 * All list controllers should heritate List_Controller, which gives some default implementations
 */
abstract class List_Controller extends Output_Controller
{

	//----------------------------------------------------------------- applyParametersToListSettings
	/**
	 * Apply parameters to list settings
	 *
	 * @param $list_settings List_Settings
	 * @param $parameters    array
	 * @param $search        array
	 */
	public function applyParametersToListSettings(
		List_Settings $list_settings, $parameters, $search = null
	) {
		if (isset($parameters["add_property"])) {
			$list_settings->addProperty(
				$parameters["add_property"],
				isset($parameters["before"]) ? "before" : "after",
				isset($parameters["before"])
					? $parameters["before"]
					: (isset($parameters["after"]) ? $parameters["after"] : "")
			);
		}
		if (isset($parameters["remove_property"])) {
			$list_settings->removeProperty($parameters["remove_property"]);
		}
		if (isset($parameters["property_path"])) {
			if (isset($parameters["property_title"])) {
				$list_settings->propertyTitle($parameters["property_path"], $parameters["property_title"]);
			}
		}
		if (isset($parameters["reverse"])) {
			$list_settings->reverse($parameters["reverse"]);
		}
		if (!empty($search)) {
			$list_settings->search(self::descapeForm($search));
		}
		if (isset($parameters["sort"])) {
			$list_settings->sort($parameters["sort"]);
		}
		if (isset($parameters["title"])) {
			$list_settings->title = $parameters["title"];
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
			$property_name = self::descapePropertyName($property_name);
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

	//--------------------------------------------------------------------- getCustomizedListSettings
	/**
	 * @param $list_settings List_Settings
	 * @return List_Settings[]
	 */
	public function getCustomizedListSettings(List_Settings $list_settings)
	{
		$list = array();
		$search["code"] = $list_settings->class_name . ".list.%";
		/** @var $setting Setting */
		foreach (Dao::search($search, 'SAF\Framework\Setting') as $setting) {
			$list[] = $setting->value;
		}
		uasort($list, function(List_Settings $s1, List_Settings $s2) {
			return $s1->name > $s2->name;
		});
		return $list;
	}

	//------------------------------------------------------------------------------- getListSettings
	/**
	 * @param $class_name string
	 * @return List_Settings
	 */
	public static function getListSettings($class_name)
	{
		/** @var $settings Settings */
		$settings = Session::current()->get('SAF\Framework\Settings', true);
		/** @var $setting Setting */
		$setting = $settings->get($class_name . ".list");
		if (!isset($setting)) {
			$list_settings = new List_Settings($class_name);
			$settings->add($class_name . ".list", $list_settings);
		}
		else {
			$list_settings = $setting->value;
		}
		return $list_settings;
	}

	//------------------------------------------------------------------------------- getSearchValues
	/**
	 * @param $list_settings List_Settings
	 * @return Reflection_Property_Value[] key is the property path
	 */
	public function getSearchValues(List_Settings $list_settings)
	{
		$search = array_combine($list_settings->properties_path, $list_settings->properties_path);
		foreach ($list_settings->search as $property_path => $search_value) {
			$property = new Reflection_Property_Value(
				$list_settings->class_name, $property_path, $search_value, true
			);
			if ($property->getType()->isClass()) {
				$property->value(Dao::read($search_value, $property->getType()->asString()));
			}
			else {
				$property->value($search_value);
			}
			$search[$property_path] = $property;
		}
		return $search;
	}

	//--------------------------------------------------------------------------- getSelectionButtons
	/**
	 * @param $class_name string
	 * @return Button[]
	 */
	protected function getSelectionButtons(
		/** @noinspection PhpUnusedParameterInspection needed for plugins or overriding */
		$class_name
	) {
		return array();
	}

	//----------------------------------------------------------------------------- getReverseClasses
	/**
	 * @param $list_settings List_Settings
	 * @return string[] key is column number and property path
	 */
	protected function getReverseClasses(List_Settings $list_settings)
	{
		$reverse_classes = array();
		foreach ($list_settings->sort->reverse as $property_path) {
			$reverse_classes[$property_path] = "reverse";
			$key = array_search($property_path, $list_settings->properties_path);
			if ($key !== false) {
				$reverse_classes[$key] = "reverse";
			}
		}
		return $reverse_classes;
	}

	//-------------------------------------------------------------------------------- getSortClasses
	/**
	 * @param $list_settings List_Settings
	 * @return string[] key is column number and property path, value is position of the sort property (0..n)
	 */
	protected function getSortClasses(List_Settings $list_settings)
	{
		$sort_classes = array();
		$sort_count = 0;
		foreach ($list_settings->sort->getColumns() as $property_path) {
			$sort_classes[$property_path] = ++$sort_count;
			$key = array_search($property_path, $list_settings->properties_path);
			if ($key !== false) {
				$sort_classes[$key] = $sort_count;
			}
		}
		return $sort_classes;
	}

	//---------------------------------------------------------------------------------- getSortLinks
	/**
	 * @param $list_settings List_Settings
	 * @return string[] key is column number and property path, value is "sort" or "reverse"
	 */
	protected function getSortLinks(List_Settings $list_settings)
	{
		$sort_links = array();
		foreach ($list_settings->properties_path as $property_path) {
			$sort_links[$property_path] = "sort";
			$key = array_search($property_path, $list_settings->properties_path);
			if ($key !== false) {
				$sort_links[$key] = "sort";
			}
		}
		$sort = $list_settings->sort->getColumns();
		if ($sort) {
			$sort_links[reset($sort)] = "reverse";
			$key = array_search(reset($sort), $list_settings->properties_path);
			if ($key !== false) {
				$sort_links[$key] = "reverse";
			}
		}
		return $sort_links;
	}

	//-------------------------------------------------------------------------------- getShortTitles
	/**
	 * @param $list_settings List_Settings
	 * @return string[] key is property path, value is short title
	 */
	protected function getShortTitles(List_Settings $list_settings)
	{
		$short_titles = array();
		foreach ($this->getTitles($list_settings) as $property_path => $title) {
			$short_titles[$property_path] = (new String($title))->twoLast();
			$key = array_search($property_path, $list_settings->properties_path);
			if ($key !== false) {
				$sort_titles[$key] = $short_titles[$property_path];
			}
		}
		return $short_titles;
	}

	//------------------------------------------------------------------------------------- getTitles
	/**
	 * @param $list_settings List_Settings
	 * @return string[] key is property path, value is title
	 */
	protected function getTitles(List_Settings $list_settings)
	{
		$titles = array();
		foreach ($list_settings->properties_path as $property_path) {
			$titles[$property_path] = isset($list_settings->properties_title[$property_path])
				? $list_settings->properties_title[$property_path]
				: $property_path;
			$key = array_search($property_path, $list_settings->properties_path);
			if ($key !== false) {
				$titles[$key] = $titles[$property_path];
			}
		}
		return $titles;
	}

}
