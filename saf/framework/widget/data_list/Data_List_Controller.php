<?php
namespace SAF\Framework\Widget\Data_List;

use SAF\Framework\Builder;
use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Parameter;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Controller\Target;
use SAF\Framework\Dao\Func;
use SAF\Framework\Dao\Func\Group_Concat;
use SAF\Framework\Dao\Option\Count;
use SAF\Framework\Dao\Option\Group_By;
use SAF\Framework\Dao\Option\Limit;
use SAF\Framework\Dao;
use SAF\Framework\Dao\Option\Reverse;
use SAF\Framework\History;
use SAF\Framework\Locale;
use SAF\Framework\Locale\Loc;
use SAF\Framework\Printer\Model;
use SAF\Framework\Reflection\Annotation\Property\Store_Annotation;
use SAF\Framework\Reflection\Annotation\Property\User_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Method_Annotation;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Reflection\Reflection_Property_Value;
use SAF\Framework\Setting\Buttons;
use SAF\Framework\Setting\Custom_Settings;
use SAF\Framework\Setting\Custom_Settings_Controller;
use SAF\Framework\Tools\Color;
use SAF\Framework\Tools\List_Data;
use SAF\Framework\Tools\Names;
use SAF\Framework\Tools\Namespaces;
use SAF\Framework\View;
use SAF\Framework\Widget\Button;
use SAF\Framework\Widget\Button\Has_Selection_Buttons;
use SAF\Framework\Widget\Data_List_Setting;
use SAF\Framework\Widget\Data_List_Setting\Data_List_Settings;
use SAF\Framework\Widget\Output\Output_Controller;

/**
 * The default list controller is called if no list controller has beed defined for a business object class
 */
class Data_List_Controller extends Output_Controller implements Has_Selection_Buttons
{

	//---------------------------------------------------------------------------------- $class_names
	/**
	 * @var string The set class name (can be virtual if only the element class name exists)
	 */
	private $class_names;

	//----------------------------------------------------------------- applyParametersToListSettings
	/**
	 * Apply parameters to list settings
	 *
	 * @param $list_settings Data_List_Settings
	 * @param $parameters    array
	 * @param $form          array
	 * @return Data_List_Settings set if parameters did change
	 */
	public function applyParametersToListSettings(
		Data_List_Settings &$list_settings, $parameters, $form = null
	) {
		if (isset($form)) {
			$parameters = array_merge($parameters, $form);
		}
		$did_change = true;
		if (isset($parameters['add_property'])) {
			$list_settings->addProperty(
				$parameters['add_property'],
				isset($parameters['before']) ? 'before' : 'after',
				isset($parameters['before'])
					? $parameters['before']
					: (isset($parameters['after']) ? $parameters['after'] : '')
			);
		}
		elseif (isset($parameters['less'])) {
			if ($parameters['less'] == 20) {
				$list_settings->maximum_displayed_lines_count = 20;
			}
			else {
				$list_settings->maximum_displayed_lines_count = max(
					20, $list_settings->maximum_displayed_lines_count - $parameters['less']
				);
			}
		}
		elseif (isset($parameters['more'])) {
			$list_settings->maximum_displayed_lines_count = round(min(
						1000, $list_settings->maximum_displayed_lines_count + $parameters['more']
					) / 100) * 100;
		}
		elseif (isset($parameters['move'])) {
			if ($parameters['move'] == 'down') {
				$list_settings->start_display_line_number += $list_settings->maximum_displayed_lines_count;
			}
			elseif ($parameters['move'] == 'up') {
				$list_settings->start_display_line_number -= $list_settings->maximum_displayed_lines_count;
			}
			elseif (is_numeric($parameters['move'])) {
				$list_settings->start_display_line_number = $parameters['move'];
			}
		}
		elseif (isset($parameters['remove_property'])) {
			$list_settings->removeProperty($parameters['remove_property']);
		}
		elseif (isset($parameters['property_path'])) {
			if (isset($parameters['property_group_by'])) {
				$list_settings->propertyGroupBy(
					$parameters['property_path'], $parameters['property_group_by']
				);
			}
			if (isset($parameters['property_title'])) {
				$list_settings->propertyTitle($parameters['property_path'], $parameters['property_title']);
			}
		}
		elseif (isset($parameters['reverse'])) {
			$list_settings->reverse($parameters['reverse']);
		}
		elseif (isset($parameters['search'])) {
			$list_settings->search(self::descapeForm($parameters['search']));
		}
		elseif (isset($parameters['sort'])) {
			$list_settings->sort($parameters['sort']);
		}
		elseif (isset($parameters['title'])) {
			$list_settings->title = $parameters['title'];
		}
		else {
			$did_change = false;
		}
		if ($list_settings->start_display_line_number < 1) {
			$list_settings->start_display_line_number = 1;
			$did_change = true;
		}
		if (Custom_Settings_Controller::applyParametersToCustomSettings($list_settings, $parameters)) {
			$did_change = true;
		}
		if (!$list_settings->name) {
			$list_settings->name = $list_settings->title;
		}
		if (!$list_settings->title) {
			$list_settings->title = $list_settings->name;
		}
		if ($did_change) {
			$list_settings->save();
		}
		return $did_change ? $list_settings : null;
	}

	//------------------------------------------------------------------------- applySearchParameters
	/**
	 * @param $list_settings Data_List_Settings
	 * @return array search-compatible search array
	 */
	protected function applySearchParameters(Data_List_Settings $list_settings)
	{
		$class = $list_settings->getClass();
		/** @var $search_parameters_parser Search_Parameters_Parser */
		$search_parameters_parser = Builder::create(
			Search_Parameters_Parser::class, [$class->name, $list_settings->search]
		);
		$result = $search_parameters_parser->parse();

		foreach ($class->getAnnotations('on_data_list') as $execute) {
			/** @var $execute Method_Annotation */
			if ($execute->call($class->name, [&$result]) === false) {
				break;
			}
		}

		return $result;
	}

	//----------------------------------------------------------------------------------- descapeForm
	/**
	 * @param $form string[]
	 * @return string[]
	 */
	protected function descapeForm($form)
	{
		$result = [];
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
		$property_name = str_replace(['.id_', '>id_', '>'], DOT, $property_name);
		if (substr($property_name, 0, 3) == 'id_') {
			$property_name = substr($property_name, 3);
		}
		return $property_name;
	}

	//--------------------------------------------------------------------------------- getClassNames
	/**
	 * Returns the class names
	 *
	 * @return string
	 */
	public function getClassNames()
	{
		return $this->class_names;
	}

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $class_name string The context object or class name
	 * @param $parameters array Parameters prepared to the view. 'selection_buttons' to be added
	 * @param $settings   Custom_Settings|Data_List_Settings
	 * @return Button[]
	 */
	public function getGeneralButtons($class_name, $parameters, Custom_Settings $settings = null)
	{
		return [
			Feature::F_ADD => new Button(
				'Add',
				View::link($class_name, Feature::F_ADD),
				Feature::F_ADD,
				[Target::MAIN, new Color(Color::GREEN)]
			),
			Feature::F_IMPORT => new Button(
				'Import',
				View::link($class_name, Feature::F_IMPORT),
				Feature::F_IMPORT,
				[Target::MAIN, new Color(Color::GREEN)]
			)
		];
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @param $list_settings Data_List_Settings
	 * @return Property[]
	 */
	protected function getProperties(Data_List_Settings $list_settings)
	{
		$class_name = $list_settings->getClassName();
		/** @var $properties Property[] */
		$properties = [];
		// properties / search
		foreach ($list_settings->properties as $property) {
			/** @var $property Property */
			$property = Builder::createClone($property, Property::class);
			$property->search = new Reflection_Property($class_name, $property->path);
			$properties[$property->path] = $property;
		}
		foreach ($list_settings->search as $property_path => $search_value) {
			if (isset($properties[$property_path])) {
				$properties[$property_path]->search = $this->searchProperty(
					$properties[$property_path]->search, $search_value
				);
			}
		}
		// sort / reverse
		$sort_position = 0;
		foreach ($list_settings->sort->columns as $column) {
			$property_path = ($column instanceof Reverse) ? $column->column : $column;
			if (isset($properties[$property_path])) {
				$properties[$property_path]->sort = ++$sort_position;
				if ($list_settings->sort->isReverse($property_path)) {
					$properties[$property_path]->reverse = true;
				}
			}
		}
		return $properties;
	}

	//------------------------------------------------------------------------------ getSearchSummary
	/**
	 * @param $list_settings Data_List_Settings
	 * @return string
	 */
	public function getSearchSummary(Data_List_Settings $list_settings)
	{
		if ($list_settings->search) {
			if (Locale::current()) {
				$t = '|';
				$i = '¦';
			}
			else {
				$t = $i = '';
			}
			$class_display = Names::classToDisplay(
				$list_settings->getClass()->getAnnotation('set')->value
			);
			$summary = $t . $i. ucfirst($class_display) . $i . ' filtered by' . $t;
			$first = true;
			foreach ($list_settings->search as $property_path => $value) {
				if ($first) $first = false; else $summary .= ',';
				$summary .= SP . $t . $property_path . $t . ' = ' . DQ . $value . DQ;
			}
			return $summary;
		}
		return null;
	}

	//--------------------------------------------------------------------------- getSelectionButtons
	/**
	 * @param $class_name    string class name
	 * @param $parameters    string[] parameters
	 * @param $list_settings Custom_Settings|Data_List_Settings
	 * @return Button[]
	 */
	public function getSelectionButtons(
		$class_name, $parameters, Custom_Settings $list_settings = null
	) {
		return [
			Feature::F_EXPORT => new Button(
				'Export',
				View::link(
					Names::classToSet($class_name), Feature::F_EXPORT, null, [Parameter::AS_WIDGET => true]
				),
				Feature::F_EXPORT,
				[View::TARGET => Target::TOP]
			),
			Feature::F_PRINT => new Button(
				'Print',
				View::link($class_name, Feature::F_PRINT),
				Feature::F_PRINT, [
				Button::SUB_BUTTONS => [
					new Button(
						'Models',
						View::link(
							Names::classToSet(Model::class),
							Feature::F_LIST,
							Namespaces::shortClassName($class_name)
						),
						Feature::F_LIST,
						Target::MAIN
					)
				]
			])
		];
	}

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $class_name string
	 * @return mixed[]
	 */
	protected function getViewParameters(Parameters $parameters, $form, $class_name)
	{
		$this->class_names = $class_name;
		$class_name = $parameters->getMainObject()->element_class_name;
		$parameters = $parameters->getObjects();
		$list_settings = Data_List_Settings::current($class_name);
		$list_settings->cleanup();
		$this->applyParametersToListSettings($list_settings, $parameters, $form);
		$customized_list_settings = $list_settings->getCustomSettings();
		$count = new Count();
		$data = $this->readData($class_name, $list_settings, $count);
		$displayed_lines_count = min($data->length(), $list_settings->maximum_displayed_lines_count);
		$less_twenty = $displayed_lines_count > 20;
		$more_hundred = ($displayed_lines_count < 1000) && ($displayed_lines_count < $count->count);
		$more_thousand = ($displayed_lines_count < 1000) && ($displayed_lines_count < $count->count);
		$parameters = array_merge(
			[$class_name => $data],
			$parameters,
			[
				'customized_lists'      => $customized_list_settings,
				'default_title'         => ucfirst(Names::classToDisplay($this->class_names)),
				'display_start'         => $list_settings->start_display_line_number,
				'displayed_lines_count' => $displayed_lines_count,
				'less_twenty'           => $less_twenty,
				'more_hundred'          => $more_hundred,
				'more_thousand'         => $more_thousand,
				'properties'            => $this->getProperties($list_settings),
				'rows_count'            => $count->count,
				'search_summary'        => $this->getSearchSummary($list_settings),
				'settings'              => $list_settings,
				'title'                 => $list_settings->title()
			]
		);
		// buttons
		/** @var $buttons Buttons */
		$buttons = Builder::create(Buttons::class);
		$parameters['custom_buttons'] = $buttons->getButtons(
			'custom list', Names::classToSet($class_name)
		);
		$parameters[self::GENERAL_BUTTONS] = $this->getGeneralButtons(
			$class_name, $parameters, $list_settings
		);
		$parameters[self::SELECTION_BUTTONS] = $this->getSelectionButtons(
			$class_name, $parameters, $list_settings
		);
		if (!isset($customized_list_settings[$list_settings->name])) {
			unset($parameters[self::GENERAL_BUTTONS][Feature::F_DELETE]);
		}
		return $parameters;
	}

	//--------------------------------------------------------------------------------------- groupBy
	/**
	 * @param $properties Data_List_Setting\Property[]
	 * @return Group_By|null
	 */
	private function groupBy($properties)
	{
		$group_by = null;
		foreach ($properties as $property) {
			if ($property->group_by) {
				if (!isset($group_by)) {
					$group_by = new Group_By();
				}
				$group_by->properties[] = $property->path;
			}
		}
		return $group_by;
	}

	//----------------------------------------------------------------------------------- groupConcat
	/**
	 * @param $properties_path string[]
	 * @param Group_By         $group_by
	 */
	private function groupConcat(&$properties_path, Group_By $group_by)
	{
		foreach ($properties_path as $key => $property_path) {
			if (!in_array($property_path, $group_by->properties)) {
				$group_concat = new Group_Concat();
				$group_concat->separator = ', ';
				$properties_path[$key] = $group_concat;
			}
		}
	}

	//------------------------------------------------------------------------------- objectsToString
	/**
	 * In Dao::select() result : replace objects with their matching __toString() result value
	 *
	 * @param $data List_Data
	 */
	private function objectsToString(List_Data $data)
	{
		$class_properties = [];
		$class_name = $data->getClass()->getName();
		foreach ($data->getProperties() as $property_name) {
			$property = new Reflection_Property($class_name, $property_name);
			if ($property->getType()->isClass()) {
				$class_properties[$property_name] = $property_name;
			}
		}
		if ($class_properties) {
			foreach ($data->getRows() as $row) {
				foreach ($class_properties as $property_name) {
					$row->setValue($property_name, strval($row->getValue($property_name)));
				}
			}
		}
	}

	//-------------------------------------------------------------------------------------- readData
	/**
	 * @param $class_name    string
	 * @param $list_settings Data_List_Settings
	 * @param $count         Count
	 * @return List_Data
	 */
	public function readData($class_name, Data_List_Settings $list_settings, Count $count = null)
	{
		$search = $this->applySearchParameters($list_settings);
		$options = [$list_settings->sort];
		if ($count) {
			$options[] = $count;
		}
		if ($list_settings->maximum_displayed_lines_count) {
			$limit = new Limit(
				$list_settings->start_display_line_number,
				$list_settings->maximum_displayed_lines_count
			);
			$options[] = $limit;
		}
		$properties = array_keys($list_settings->properties);
		list($properties_path, $search) = $this->removeInvisibleProperties(
			$class_name, $properties, $search
		);
		// TODO : an automation to make the group by only when it is useful
		if ($group_by = $this->groupBy($list_settings->properties)) {
			$options[] = $group_by;
			$this->groupConcat($properties_path, $group_by);
		}
		$data = Dao::select($class_name, $properties_path, $search, $options);
		$this->objectsToString($data);
		if (isset($limit) && isset($count)) {
			if (($data->length() < $limit->count) && ($limit->from > 1)) {
				$limit->from = max(1, $count->count - $limit->count + 1);
				$list_settings->start_display_line_number = $limit->from;
				$list_settings->save();
				$data = Dao::select($class_name, $properties_path, $search, $options);
			}
		}
		// TODO LOW the following patch line is to avoid others calculation to use invisible properties
		foreach ($list_settings->properties as $property_path => $property) {
			if (!isset($properties_path[$property_path])) {
				unset($list_settings->properties[$property_path]);
			}
		}
		return $data;
	}

	//--------------------------------------------------------------------- removeInvisibleProperties
	/**
	 * @param $class_name      string
	 * @param $properties_path string[] properties path that can include invisible properties
	 * @param $search          array search where to add Has_History criterions
	 * @return string[] properties path without the invisible properties
	 */
	protected function removeInvisibleProperties($class_name, $properties_path, $search)
	{
		// remove properties directly used as columns
		foreach ($properties_path as $key => $property_path) {
			$property = new Reflection_Property($class_name, $property_path);
			$annotation = $property->getListAnnotation(User_Annotation::ANNOTATION);
			if ($annotation->has(User_Annotation::INVISIBLE)) {
				unset($properties_path[$key]);
			}
			$history_class_name = $property->getFinalClassName();
			if (isA($history_class_name, History::class)) {
				$ignore_invisible_properties[$history_class_name] = lLastParse($property_path, DOT);
			}
		}
		// remove properties read from an History table
		// TODO this should be a specific when we use history. If the app does not use history, this
		// should not execute. Create an AOP advice.
		if (isset($ignore_invisible_properties)) {
			foreach ($ignore_invisible_properties as $history_class_name => $history_path) {
				$property_names = Dao::select(
					$history_class_name, ['property_name'], [], [Dao::groupBy('property_name')]
				)->getRows();
				foreach ($property_names as $property_name) {
					$property_name = $property_name->getValue('property_name');
					$property      = new Reflection_Property($class_name, $property_name);
					$annotation    = $property->getListAnnotation(User_Annotation::ANNOTATION);
					if ($annotation->has(User_Annotation::INVISIBLE)) {
						$all_but[] = $property_name;
					}
				}
				if (isset($all_but)) {
					$history_search[$history_path . DOT . 'property_name'] = Func::notIn($all_but);
					unset($all_but);
				}
			}
			if (isset($history_search)) {
				if ($search) {
					$search = Func::andOp(array_merge([$search], $history_search));
				}
				elseif (count($history_search) > 1) {
					$search = Func::andOp($history_search);
				}
				else {
					$search = $history_search;
				}
			}
		}
		return [array_combine($properties_path, $properties_path), $search];
	}

	//-------------------------------------------------------------------------------- searchProperty
	/**
	 * @param $property Reflection_Property
	 * @param $value    string
	 * @return Reflection_Property_Value
	 */
	private function searchProperty(Reflection_Property $property, $value)
	{
		if (strlen($value) && !is_null($value)) {
			if (
				$property->getType()->isClass()
				&& !$property->getAnnotation(Store_Annotation::ANNOTATION)->value
			) {
				$value = Dao::read($value, $property->getType()->asString());
			}
			$property = new Reflection_Property_Value($property->class, $property->name, $value, true);
			$property->value(Loc::propertyToISO($property));
		}
		return $property;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default 'list-typed' view controller
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = $this->getViewParameters($parameters, $form, $class_name);
		return View::run($parameters, $form, $files, $class_name, Feature::F_LIST);
	}

}
