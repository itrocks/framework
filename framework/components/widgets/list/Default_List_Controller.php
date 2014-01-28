<?php
namespace SAF\Framework;

/**
 * The default list controller is called if no list controller has beed defined for a business object class
 */
class Default_List_Controller extends List_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $class_name string object or class name
	 * @param $parameters string[] parameters
	 * @return Button[]
	 */
	protected function getGeneralButtons($class_name, $parameters)
	{
		return array(
			"add" => new Button(
				"Add", View::link($class_name, "new"), "add",
				array("#main", Color::of("green"))
			),
			"import" => new Button(
				"Import", View::link($class_name, "import"), "import",
				array("#main", Color::of("green"))
			),
			"save" => new Button(
				"Save", View::link($class_name, "list"), "custom_save",
				array("#main", Color::of("green"), ".submit", "title" => "save this view as a custom list")
			),
			"delete" => new Button(
				"Delete", View::link($class_name, "list", null, array("delete_name" => true)),
				"custom_delete",
				array("#main", Color::of("red"), ".submit", "title" => "delete this custom list")
			)
		);
	}

	//----------------------------------------------------------------------------- getPropertiesList
	/**
	 * @param $class_name string
	 * @return string[] property names list
	 * @todo patch for runkit-aop. to be removed when done
	 */
	protected function getPropertiesList($class_name)
	{
		return parent::getPropertiesList($class_name);
	}

	//------------------------------------------------------------------------------- getSearchValues
	/**
	 * @param $list_settings List_Settings
	 * @return Reflection_Property_Value[] key is the property path
	 * @todo remove this runkit-aop-compatibility patch as soon as it work
	 */
	public function getSearchValues(List_Settings $list_settings)
	{
		return parent::getSearchValues($list_settings);
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
		$list_settings = List_Settings::current($class_name);
		$this->applyParametersToListSettings($list_settings, $parameters, $form);
		$customized_list_settings = $list_settings->getCustomSettings();
		$count = new Dao_Count_Option();
		$limit = new Dao_Limit_Option(
			$list_settings->start_display_line_number,
			$list_settings->maximum_displayed_lines_count
		);
		$data = Dao::select(
			$class_name,
			$list_settings->properties_path,
			$list_settings->search,
			array($list_settings->sort, $limit, $count)
		);
		if (($data->length() < $limit->count) && ($limit->from > 1)) {
			$limit->from = max(1, $count->count - $limit->count + 1);
			$list_settings->start_display_line_number = $limit->from;
			$list_settings->save();
			$data = Dao::select(
				$class_name,
				$list_settings->properties_path,
				$list_settings->search,
				array($list_settings->sort, $limit, $count)
			);
		}
		$displayed_lines_count = min($data->length(), $list_settings->maximum_displayed_lines_count);
		$less_twenty = $displayed_lines_count > 20;
		$more_hundred = ($displayed_lines_count < 1000) && ($displayed_lines_count < $count->count);
		$more_thousand = ($displayed_lines_count < 1000) && ($displayed_lines_count < $count->count);
		$parameters = array_merge(
			array($class_name => $data),
			$parameters,
			array(
				"customized_lists"      => $customized_list_settings,
				"displayed_lines_count" => $displayed_lines_count,
				"less_twenty"           => $less_twenty,
				"more_hundred"          => $more_hundred,
				"more_thousand"         => $more_thousand,
				"reversed"              => $this->getReverseClasses($list_settings),
				"rows_count"            => $count->count,
				"search"                => $this->getSearchValues($list_settings),
				"search_summary"        => $this->getSearchSummary($list_settings),
				"settings"              => $list_settings,
				"short_titles"          => $this->getShortTitles($list_settings),
				"sort_options"          => $this->getSortLinks($list_settings),
				"sorted"                => $this->getSortClasses($list_settings),
				"display_start"         => $list_settings->start_display_line_number,
				"title"                 => $list_settings->title(),
				"titles"                => $this->getTitles($list_settings)
			)
		);
		// buttons
		$parameters["general_buttons"]   = $this->getGeneralButtons($class_name, $parameters);
		$parameters["selection_buttons"] = $this->getSelectionButtons($class_name);
		if (!isset($customized_list_settings[$list_settings->name])) {
			unset($parameters["general_buttons"]["delete"]);
		}
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

}
