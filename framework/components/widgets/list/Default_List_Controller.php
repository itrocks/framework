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
				"Add", View::link($class_name, "new"),
				"add", Color::of("green")
			),
			"import" => new Button(
				"Import", View::link($class_name, "import"),
				"import", "#main", Color::of("green")
			),
			"save" => new Button(
				"Save", View::link($class_name, "list"),
				"custom_save", array(Color::of("green"), "#main", ".submit")
			),
			"delete" => new Button(
				"Delete", View::link($class_name, "list", null, array("delete_name" => true)),
				"custom_delete", array(Color::of("red"), "#main", ".submit")
			)
		);
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
		// read data
		$count = new Dao_Count_Option();
		$parameters = array_merge(
			array(
				$class_name => Dao::select(
					$class_name,
					$list_settings->properties_path,
					$list_settings->search,
					array($list_settings->sort, Dao::limit(20), $count)
				),
				"customized_lists" => $customized_list_settings,
				"reversed"         => $this->getReverseClasses($list_settings),
				"rows_count"       => $count->count,
				"search"           => $this->getSearchValues($list_settings),
				"search_summary"   => $this->getSearchSummary($list_settings),
				"settings"         => $list_settings,
				"short_titles"     => $this->getShortTitles($list_settings),
				"sort_options"     => $this->getSortLinks($list_settings),
				"sorted"           => $this->getSortClasses($list_settings),
				"title"            => $list_settings->title(),
				"titles"           => $this->getTitles($list_settings)
			),
			$parameters
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
