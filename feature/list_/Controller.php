<?php
namespace ITRocks\Framework\Feature\List_;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Component\Button\Has_Selection_Buttons;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Comparison;
use ITRocks\Framework\Dao\Func\Group_Concat;
use ITRocks\Framework\Dao\Func\Logical;
use ITRocks\Framework\Dao\Mysql\Link;
use ITRocks\Framework\Dao\Mysql\Mysql_Error_Exception;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Dao\Option\Count;
use ITRocks\Framework\Dao\Option\Group_By;
use ITRocks\Framework\Dao\Option\Limit;
use ITRocks\Framework\Dao\Option\Reverse;
use ITRocks\Framework\Dao\Option\Time_Limit;
use ITRocks\Framework\Error_Handler\Handled_Error;
use ITRocks\Framework\Error_Handler\Report_Call_Stack_Error_Handler;
use ITRocks\Framework\Feature\Export;
use ITRocks\Framework\Feature\History;
use ITRocks\Framework\Feature\List_\Search_Parameters_Parser\Words;
use ITRocks\Framework\Feature\List_Setting;
use ITRocks\Framework\Feature\List_Setting\Set;
use ITRocks\Framework\Feature\Output;
use ITRocks\Framework\Layout\Print_Model\Buttons_Generator;
use ITRocks\Framework\Locale;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper;
use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Class_\Filter_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\List_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Representative_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Values_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Var_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Attribute;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Session;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Tools;
use ITRocks\Framework\Tools\Call_Stack;
use ITRocks\Framework\Tools\Contextual_Callable;
use ITRocks\Framework\Tools\Default_List_Data;
use ITRocks\Framework\Tools\List_Data;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;
use ReflectionException;

/**
 * The default list controller is called if no list controller has been defined for a business
 * object class
 *
 * @built_in_feature Display your business objects and documents into standard customizable lists
 */
class Controller extends Output\Controller implements Has_Selection_Buttons
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = Feature::F_LIST;

	//---------------------------------------------------------------------------------- $class_names
	/**
	 * @var string The set class name (can be virtual if only the element class name exists)
	 */
	private string $class_names;

	//---------------------------------------------------------------- $default_displayed_lines_count
	/**
	 * The default displayed lines count
	 *
	 * @var integer
	 */
	public int $default_displayed_lines_count = 20;

	//-------------------------------------------------------------------- $displayed_lines_count_gap
	/**
	 * The number of added / removed lines when you click on more / less
	 *
	 * @var integer
	 */
	public int $displayed_lines_count_gap = 100;

	//--------------------------------------------------------------------------------------- $errors
	/**
	 * List of errors on fields' search expression
	 *
	 * @var Exception[]
	 */
	protected array $errors = [];

	//------------------------------------------------------------------------- $foot_property_values
	/**
	 * @var Reflection_Property_Value[]
	 */
	protected array $foot_property_values = [];

	//------------------------------------------------------------------------------ $load_more_lines
	/**
	 * @var boolean
	 */
	protected bool $load_more_lines = false;

	//---------------------------------------------------------------- $maximum_displayed_lines_count
	/**
	 * The maximum displayed lines count, when we click on 'more'
	 *
	 * @var integer
	 */
	public int $maximum_displayed_lines_count = 1000;

	//----------------------------------------------------------------------------------- $time_limit
	/**
	 * The execution time limit for data list read data query.
	 * You can change this value to change this limit.
	 * A value of 0 or null will mean 'no limit'.
	 *
	 * @var integer
	 */
	public int $time_limit = 30;

	//-------------------------------------------------------------------------- applyGettersToValues
	/**
	 * In Dao::select() result : replace values with their matching result of user_getter / getter
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $data List_Data
	 */
	protected function applyGettersToValues(List_Data $data) : void
	{
		$properties             = $data->getProperties();
		$properties_with_getter = [];
		foreach ($properties as $property_path => $property) {
			$link_annotation = ($property instanceof Reflection_Property)
				? Link_Annotation::of($property)
				: null;
			$user_getter = null;
			if (
				$link_annotation
				&& !str_contains($property->path, DOT)
				&& !$link_annotation->isCollection()
				&& !$link_annotation->isMap()
				&& (
					Getter::of($property)->callable
					|| ($user_getter = $property->getAnnotation('user_getter')->value)
				)
			) {
				$translate = $property->getAnnotation('translate')->value;
				$properties_with_getter[$property_path] = [$property, $user_getter, $translate];
			}
		}
		if ($properties_with_getter) {
			foreach ($data->getRows() as $row) {
				$object = $row->id();
				// Optimize memory usage : detach object from the List_Row
				if (!is_object($object)){
					$object = Mapper\Getter::getObject($object, $row->getClassName());
				}
				foreach (
					$properties_with_getter as $property_path => [$property, $user_getter, $translate]
				) {
					/** @noinspection PhpUnhandledExceptionInspection valid $object */
					/** @var $property Reflection_Property */
					$value = $user_getter
						? (new Contextual_Callable($user_getter, $object))->call($property)
						: $property->getValue($object);
					if (is_object($value)){
						$value = strval($value);
					}
					if ($translate === 'common') {
						$value = Loc::tr($value);
					}
					$row->setValue($property_path, $value);
				}
			}
		}
	}

	//----------------------------------------------------------------- applyParametersToListSettings
	/**
	 * Apply parameters to list settings
	 *
	 * @param $list_settings    Set
	 * @param $parameters       array
	 * @param $form             array|null
	 * @return ?Set set if parameters did change
	 */
	public function applyParametersToListSettings(
		Set &$list_settings, array $parameters, array $form = null
	) : ?Set
	{
		if (isset($form)) {
			$parameters = array_merge($parameters, $form);
		}
		$did_change = Setting\Custom\Controller::applyParametersToCustomSettings(
			$list_settings, $parameters
		);
		if (isset($parameters['add_property'])) {
			$list_settings->addProperty(
				$parameters['add_property'],
				isset($parameters[Set::BEFORE]) ? Set::BEFORE : Set::AFTER,
				$parameters[Set::BEFORE] ?? $parameters[Set::AFTER] ?? ''
			);
			$did_change = true;
		}
		if (isset($parameters['less'])) {
			if (intval($parameters['less']) === $this->default_displayed_lines_count) {
				$list_settings->maximum_displayed_lines_count = $this->default_displayed_lines_count;
			}
			else {
				$list_settings->maximum_displayed_lines_count = max(
					$this->default_displayed_lines_count,
					$list_settings->maximum_displayed_lines_count - $parameters['less']
				);
			}
			$did_change = true;
		}
		if (isset($parameters['more'])) {
			$list_settings->maximum_displayed_lines_count = round(min(
				$this->maximum_displayed_lines_count,
				$list_settings->maximum_displayed_lines_count + $parameters['more']
			) / $this->displayed_lines_count_gap) * $this->displayed_lines_count_gap;
			$did_change = true;
		}
		if (isset($parameters['move'])) {
			if ($parameters['move'] === 'down') {
				$list_settings->start_display_line_number += $list_settings->maximum_displayed_lines_count;
				$did_change = true;
			}
			elseif ($parameters['move'] === 'up') {
				$list_settings->start_display_line_number -= $list_settings->maximum_displayed_lines_count;
				$did_change = true;
			}
			elseif (is_numeric($parameters['move'])) {
				$list_settings->start_display_line_number = $parameters['move'];
				$did_change = !isset($parameters['last_time']);
			}
		}
		if (isset($parameters['remove_property'])) {
			$list_settings->removeProperty($parameters['remove_property']);
			$did_change = true;
		}
		if (isset($parameters['property_path'])) {
			if (isset($parameters['property_group_by'])) {
				$list_settings->propertyGroupBy(
					$parameters['property_path'], $parameters['property_group_by']
				);
			}
			if (isset($parameters['property_title'])) {
				$list_settings->propertyTitle($parameters['property_path'], $parameters['property_title']);
			}
			$did_change = true;
		}
		if (isset($parameters['reset_search'])) {
			$list_settings->resetSearch();
			$did_change = true;
		}
		if (isset($parameters['reverse'])) {
			$list_settings->reverse($parameters['reverse']);
			$did_change = true;
		}
		if (isset($parameters['search'])) {
			$list_settings->search(self::descapeForm($parameters['search']));
			$did_change = true;
		}
		if (isset($parameters[Property::SORT])) {
			$list_settings->sort($parameters[Property::SORT]);
			$did_change = true;
		}
		if (isset($parameters['title'])) {
			$list_settings->title = $parameters['title'];
			$did_change = true;
		}
		if ($list_settings->start_display_line_number < 1) {
			$list_settings->start_display_line_number = 1;
			$did_change = true;
		}
		if (!$list_settings->name) {
			$list_settings->name = $list_settings->title;
		}
		if (!$list_settings->title) {
			$list_settings->title = $list_settings->name;
		}
		// SM : I put the save outside this method because we should save only if search
		// expressions are all valid.
		// TODO Move back save() here once we have a generic validator (parser) not depending of SQL that we could fire here before save !
		//if ($did_change) {
		//	$list_settings->save();
		//}
		return $did_change ? $list_settings : null;
	}

	//------------------------------------------------------------------------- applySearchParameters
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $list_settings Set
	 * @return array search-compatible search array
	 */
	public function applySearchParameters(Set $list_settings) : array
	{
		$class  = $list_settings->getClass();
		$search = $this->searchObjectsToRepresentative($class->name, $list_settings->search);
		/** @noinspection PhpUnhandledExceptionInspection ::class */
		$search_parameters_parser = Builder::create(
			Search_Parameters_Parser::class, [$class->name, $search]
		);
		$search = $search_parameters_parser->parse();
		// check if we have errors in search expressions
		$this->errors = [];
		foreach ($search as $property_path => &$search_value) {
			if ($search_value instanceof Exception) {
				$this->errors[$property_path] = $search_value;
				unset($search[$property_path]);
				unset($list_settings->search[$property_path]);
			}
		}
		return $search;
	}

	//----------------------------------------------------------------------------------- descapeForm
	/**
	 * @param $form string[]
	 * @return string[]
	 */
	protected function descapeForm(array $form) : array
	{
		$result = [];
		foreach ($form as $property_name => $value) {
			$property_name          = self::descapePropertyName($property_name);
			$result[$property_name] = $value;
		}
		return $result;
	}

	//--------------------------------------------------------------------------- descapePropertyName
	/**
	 * @param $property_name string
	 * @return string
	 * @see Functions::escapeName()
	 */
	protected function descapePropertyName(string $property_name) : string
	{
		$property_name = str_replace(
			['.id_', '>id_', '>', Q, BQ], [DOT, DOT, DOT, '(', ')'], $property_name
		);
		if (str_starts_with($property_name, 'id_')) {
			$property_name = substr($property_name, 3);
		}
		return $property_name;
	}

	//---------------------------------------------------------------------------- forceSetMainObject
	/**
	 * Force $parameters main object to a set of $this->class_names
	 * Replace the already existing Main_Object ($this->mainObject() must be called before this)
	 *
	 * @param $parameters Parameters
	 * @return string
	 */
	protected function forceSetMainObject(Parameters $parameters) : string
	{
		$set = Tools\Set::instantiate($this->class_names);
		if (!is_a($object = $parameters->shiftObject(), Names::setToClass($this->class_names))) {
			$parameters->unshift($object);
		}
		$parameters->unshift($set);
		return $set->element_class_name;
	}

	//--------------------------------------------------------------------------------- getClassNames
	/**
	 * Returns the class names
	 *
	 * @return string
	 */
	public function getClassNames() : string
	{
		return $this->class_names;
	}

	//------------------------------------------------------------------------------------- getErrors
	/**
	 * @return Exception[]
	 */
	public function getErrors() : array
	{
		return $this->errors;
	}

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @noinspection PhpDocSignatureInspection $class_name, $settings
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection $class_name
	 * @param $class_name string The context object or class name
	 * @param $parameters array Parameters prepared to the view. 'selection_buttons' to be added
	 * @param $settings   Setting\Custom\Set&Set|null
	 * @return Button[]
	 */
	public function getGeneralButtons(
		object|string $class_name, array $parameters, Setting\Custom\Set $settings = null
	) : array
	{
		return [
			Feature::F_ADD => new Button(
				'Add',
				View::link($class_name, Feature::F_ADD),
				Feature::F_ADD,
				Target::MAIN
			),
			Feature::F_LIST_EDIT => new Button(
				'Edit',
				View::link($class_name, Feature::F_LIST_EDIT),
				Feature::F_LIST_EDIT,
				Target::TOP
			),
			Feature::F_IMPORT => new Button(
				'Import',
				View::link($class_name, Feature::F_IMPORT),
				Feature::F_IMPORT,
				Target::MAIN
			)
		];
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $list_settings Set
	 * @return Property[]
	 */
	protected function getProperties(Set $list_settings) : array
	{
		$class_name = $list_settings->getClassName();
		/** @var $properties Property[] */
		$properties = [];
		// properties / search
		foreach ($list_settings->properties as $property) {
			/** @noinspection PhpUnhandledExceptionInspection ::class */
			/** @var $property Property */
			$property = Builder::createClone($property, Property::class);
			/** @noinspection PhpUnhandledExceptionInspection valid $property->path and $class_name */
			$property->search = new Reflection_Property($class_name, $property->path);
			$property->type   = $property->search->getType();
			$this->prepareSearchPropertyComponent($property->search);
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
	 * @param $class_name    string class for the read object
	 * @param $list_settings Set
	 * @param $search        array search-compatible search array
	 * @return string
	 */
	public function getSearchSummary(
		string $class_name, Set $list_settings, array $search
	) : string
	{
		if (!$search) {
			return '';
		}
		if (!$list_settings->search) {
			return '';
		}
		if (Locale::current()) {
			$t = '|';
			$i = '¦';
		}
		else {
			$t = $i = '';
		}
		$class_display = Names::classToDisplay(
			Attribute\Class_\Set::of($list_settings->getClass())->value
		);
		$summary         = $t . $i. ucfirst($class_display) . $i . ' filtered by' . $t;
		$summary_builder = new Summary_Builder($class_name, $search);
		$summary        .= SP . $summary_builder;
		return $summary;
	}

	//--------------------------------------------------------------------------- getSelectionButtons
	/**
	 * @param $class_name string class name
	 * @param $parameters string[] parameters
	 * @param $settings   Set|null
	 * @return Button[]
	 */
	public function getSelectionButtons(
		string $class_name, array $parameters, List_Setting\Set $settings = null
	) : array
	{
		$layout_model_buttons = (new Buttons_Generator($class_name))->getButtons();

		$buttons = [
			Feature::F_EXPORT => new Button(
				'Export',
				View::link(
					Names::classToSet($class_name), Feature::F_EXPORT, null, [Parameter::AS_WIDGET => true]
				),
				Feature::F_EXPORT,
				[
					View::TARGET => Target::TOP,
					Button::SUB_BUTTONS => [
						new Button(
							'All columns',
							View::link(
								Names::classToSet($class_name), Feature::F_EXPORT, null,
								[Parameter::AS_WIDGET => true, Export\Controller::ALL_PROPERTIES => true]
							),
							Feature::F_EXPORT,
							[View::TARGET => Target::TOP]
						)
					]
				]
			),
			Feature::F_PRINT => new Button(
				'Print',
				View::link($class_name, Feature::F_PRINT),
				Feature::F_PRINT
			),
			Feature::F_DELETE => new Button(
				'Delete',
				View::link($class_name, Feature::F_DELETE),
				Feature::F_DELETE,
				Target::RESPONSES
			)
		];

		$this->selectPrintButton($buttons[Feature::F_PRINT], $layout_model_buttons);

		return $buttons;
	}

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $class_name string
	 * @return array
	 */
	protected function getViewParameters(Parameters $parameters, array $form, string $class_name)
		: array
	{
		$load_time             = time();
		$parameters            = $parameters->getObjects();
		$this->load_more_lines = isset($parameters['last_time']) && isset($parameters['move']);
		$list_settings = Set::current($class_name);
		$list_settings->cleanup();
		$did_change = $this->applyParametersToListSettings($list_settings, $parameters, $form);
		$customized_list_settings = $list_settings->getCustomSettings();
		$count                    = new Count();
		$options                  = [
			$count, Dao::doublePass(), $list_settings->sort, Dao::timeLimit($this->time_limit)
		];
		// SM : Moved from readData() and moved before cloning
		$search = $this->applySearchParameters($list_settings);

		// before to fire readData (that may change $list_settings if error found)
		// we need to get a copy in order to display summary with original given parameters
		$list_settings_before_read = clone $list_settings;
		// if load more lines and table updated : reload all from start
		if ($this->load_more_lines && (($sql = Dao::current()) instanceof Link)) {
			/** @var $sql Link */
			$last_update = $sql->getConnection()->lastUpdate($class_name);
			if ($last_update && (strtotime($last_update) >= $load_time)) {
				$list_settings->maximum_displayed_lines_count = $parameters['move']
					+ $list_settings->maximum_displayed_lines_count;
				$list_settings->start_display_line_number = 1;
				$parameters['updated'] = true;
			}
		}
		try {
			$data = $this->readData($class_name, $list_settings, $search, $options);
			// SM : Moved from applyParametersToListSettings()
			// TODO Move back once we have a generic validator (parser) not depending of SQL that we could
			// TODO fire before save
			if (!is_null($did_change) && !(isset($this->errors) && count($this->errors))) {
				$list_settings->save();
			}
		}
		/** @noinspection PhpRedundantCatchClauseInspection Yes it could */
		catch (Exception $exception) {
			$this->reportError($exception);
		}
		finally {
			if (!isset($data) || !$data) {
				// set empty list result
				$data  = new Default_List_Data($class_name, []);
			}
		}
		$displayed_lines_count = min($data->length(), $list_settings->maximum_displayed_lines_count);
		$less_twenty    = $displayed_lines_count > $this->default_displayed_lines_count;
		$lock_columns   = List_Annotation::of($list_settings->getClass())->has(List_Annotation::LOCK);
		$more           = ($displayed_lines_count < $count->count);
		$more_hundred   = ($displayed_lines_count < $this->maximum_displayed_lines_count)
			&& ($displayed_lines_count < $count->count);
		$more_thousand  = ($displayed_lines_count < $this->maximum_displayed_lines_count)
			&& ($displayed_lines_count < $count->count);
		$search_summary = $this->getSearchSummary($class_name, $list_settings_before_read, $search);
		$parameters     = array_merge(
			[$class_name => $data],
			$parameters,
			[
				'allow_select_all'              => true,
				'class'                         => Builder::current()->sourceClassName($class_name),
				'column_select'                 => $lock_columns ? '' : 'column_select',
				'customized_lists'              => $customized_list_settings,
				'default_title'                 => ucfirst(Names::classToDisplay($this->class_names)),
				'display_start'                 => $list_settings->start_display_line_number,
				'displayed_lines_count'         => $displayed_lines_count,
				'displayed_lines_count_gap'     => $this->displayed_lines_count_gap,
				'errors_summary'                => $this->errors,
				'foot_property_values'          => $this->foot_property_values,
				'less_twenty'                   => $less_twenty,
				'load_time'                     => $load_time,
				'lock_columns'                  => $lock_columns,
				'maximum_displayed_lines_count' => $this->maximum_displayed_lines_count,
				'module'                        => $this->getModule($class_name),
				'more'                          => $more,
				'more_hundred'                  => $more_hundred,
				'more_thousand'                 => $more_thousand,
				'parent'                        => $this->getParent($class_name),
				'properties'                    => $this->getProperties($list_settings_before_read),
				'rows_count'                    => $count->count,
				'search_summary'                => $search_summary,
				'selected'                      => 'selected',
				'set'                           => Names::classToSet($class_name),
				'settings'                      => $list_settings,
				'title'                         => $list_settings->name ?: $list_settings->title()
			]
		);
		// buttons
		/** @noinspection PhpUnhandledExceptionInspection ::class */
		$buttons = Builder::create(Setting\Buttons::class);
		$parameters['custom_buttons'] = $buttons->getButtons(
			'custom list', Names::classToSet($class_name)
		);
		// if an error occurred, we do not display custom save button
		if (count($this->errors)) {
			if (isset($parameters['custom_buttons'][Feature::F_SAVE])) {
				unset($parameters['custom_buttons'][Feature::F_SAVE]);
			}
		}
		$parameters[self::GENERAL_BUTTONS] = $this->getGeneralButtons(
			$class_name, $parameters, $list_settings
		);
		$parameters[self::SELECTION_BUTTONS] = $this->getSelectionButtons(
			$class_name, $parameters, $list_settings
		);
		$parameters[Template::TEMPLATE_FUNCTIONS] = Html_Template_Functions::class;
		if (!isset($customized_list_settings[$list_settings->name])) {
			unset($parameters[self::GENERAL_BUTTONS][Feature::F_DELETE]);
		}
		return $parameters;
	}

	//--------------------------------------------------------------------------------------- groupBy
	/**
	 * @param $properties List_Setting\Property[]
	 * @return ?Group_By
	 */
	public function groupBy(array $properties) : ?Group_By
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
	 * @param $group_by        Group_By
	 */
	public function groupConcat(array &$properties_path, Group_By $group_by) : void
	{
		foreach ($properties_path as $key => $property_path) {
			if (!in_array($property_path, $group_by->properties)) {
				$group_concat            = new Group_Concat();
				$group_concat->separator = ', ';
				$properties_path[$key]   = $group_concat;
			}
		}
	}

	//------------------------------------------------------------------------------- objectsToString
	/**
	 * In Dao::select() result : replace objects with their matching __toString() result value
	 *
	 * @param $data List_Data
	 */
	private function objectsToString(List_Data $data) : void
	{
		$class_properties = [];
		foreach ($data->getProperties() as $property_path => $property) {
			if (($property instanceof Reflection_Property) && $property->getType()->isClass()) {
				$class_properties[$property_path] = $property_path;
			}
		}
		if ($class_properties) {
			foreach ($data->getRows() as $row) {
				foreach ($class_properties as $property_path) {
					$value = $row->getValue($property_path);
					if (is_array($value)){
						$value = join(LF, $value);
					}
					$row->setValue($property_path, $value);
				}
			}
		}
	}

	//---------------------------------------------------------------- prepareSearchPropertyComponent
	/**
	 * Prepare search property component for free search expression typing :
	 *
	 * - all properties are dealt as if they are string
	 * - all string properties do not have pre-selected values
	 *
	 * @param $property Reflection_Property
	 */
	protected function prepareSearchPropertyComponent(Reflection_Property $property) : void
	{
		$type = $property->getType();
		if ($type->isString() || $type->isMultipleString()) {
			Values_Annotation::local($property)->value = [];
		}
		else {
			Link_Annotation::local($property)->value = null;
			Var_Annotation::local($property)->value  = Type::STRING;
		}
	}

	//-------------------------------------------------------------------------------------- readData
	/**
	 * @param $class_name    string
	 * @param $list_settings Set
	 * @param $search        array search-compatible search array
	 * @param $options       Option[]
	 * @return List_Data
	 */
	public function readData(
		string $class_name, Set $list_settings, array $search, array $options = []
	) : List_Data
	{
		// SM : Moved outside the method in order result to be used for search summary
		//$search = $this->applySearchParameters($list_settings);

		$class = $list_settings->getClass();
		/** @var $on_list_annotations Method_Annotation[] */
		$on_list_annotations = $class->getAnnotations('on_list');
		Method_Annotation::callAll($on_list_annotations, $class->name, [&$search]);

		$properties = array_keys($list_settings->properties);
		[$properties_path, $search] = $this->removeInvisibleProperties(
			$class_name, $properties, $search
		);

		$this->foot_property_values = $this->readFootPropertyValues($class_name, $properties, $search);

		if (!$options) {
			$options = [Dao::doublePass(), $list_settings->sort, Dao::timeLimit($this->time_limit)];
		}
		$count = Count::in($options);

		if ($list_settings->maximum_displayed_lines_count) {
			$max_lines = isset($_SERVER['HTTP_TARGET_HEIGHT'])
				? intval(ceil($_SERVER['HTTP_TARGET_HEIGHT'] / 33))
				: 20;
			if ($list_settings->maximum_displayed_lines_count !== $max_lines) {
				$list_settings->maximum_displayed_lines_count = $max_lines;
			}
			$limit = new Limit(
				$this->load_more_lines ? $list_settings->start_display_line_number : 1,
				$list_settings->maximum_displayed_lines_count
			);
			$options[] = $limit;
		}
		// TODO : an automation to make the group by only when it is useful
		if ($group_by = $this->groupBy($list_settings->properties)) {
			$options[] = $group_by;
			$this->groupConcat($properties_path, $group_by);
		}
		$data = $this->readDataSelectSearch($class_name, $properties_path, $search, $options);
		if (isset($limit) && isset($count)) {
			if (!$this->load_more_lines && ($data->length() < $limit->count) && ($limit->from > 1)) {
				$limit->from = max(1, $count->count - $limit->count + 1);
				$list_settings->start_display_line_number = $limit->from;
				$list_settings->save();
				$data = $this->readDataSelectSearch($class_name, $properties_path, $search, $options);
			}
		}
		if (isset($limit)) {
			// only for limited results : this method create objects for each row to apply getter
			$this->applyGettersToValues($data);
		}
		$this->objectsToString($data);
		// TODO LOW the following patch lines are to avoid others calculation to use invisible props
		foreach ($list_settings->properties as $property_path => $property) {
			if (!isset($properties_path[$property_path])) {
				unset($list_settings->properties[$property_path]);
			}
		}

		foreach ($data->getRows() as $row) {
			foreach ($row->getValues() as $property_name => $value) {
				$row->setValue($property_name, htmlSpecialCharsRecurse(strval($value)));
			}
		}

		return $data;
	}

	//-------------------------------------------------------------------------------- readDataSelect
	/**
	 * @param $class_name      string Class name for the read object
	 * @param $properties_path string[] the list of the columns names : only those properties
	 *                         will be read. There are 'column.sub_column' to get values from linked
	 *                         objects from the same data source
	 * @param $search          array|object source object for filter, set properties will be used for
	 *                         search. Can be an array associating properties names to matching
	 *                         search value too.
	 * @param $options         Option[] some options for advanced search
	 * @return List_Data A list of read records. Each record values (it may be objects) are
	 *         stored in the same order as columns.
	 */
	public function readDataSelect(
		string $class_name, array $properties_path, array|object $search, array $options
	) : List_Data
	{
		return Dao::select($class_name, $properties_path, $search, $options);
	}

	//-------------------------------------------------------------------------- readDataSelectSearch
	/**
	 * @param $class_name      string Class name for the read object
	 * @param $properties_path string[] the list of the columns names : only those properties
	 *                         will be read. There are 'column.sub_column' to get values from linked
	 *                         objects from the same data source
	 * @param $search          array Search array for filter, associating properties names to
	 *                         matching search value too.
	 * @param $options         Option[] some options for advanced search
	 * @return List_Data A list of read records. Each record values (it may be objects) are
	 *         stored in the same order as columns.
	 */
	public function readDataSelectSearch(
		string $class_name, array $properties_path, array $search, array $options
	) : List_Data
	{
		$options[] = Dao::translate();
		if ($filters = Filter_Annotation::apply($class_name, $options, Filter_Annotation::FOR_VIEW)) {
			$search = $search ? Func::andOp([$filters, $search]) : $filters;
		}
		return $this->readDataSelect($class_name, $properties_path, $search, $options);
	}

	//------------------------------------------------------------------------ readFootPropertyValues
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name      string
	 * @param $properties_path string[]
	 * @param $search          array search-compatible search array
	 * @return Reflection_Property_Value[]
	 */
	protected function readFootPropertyValues(
		string $class_name, array $properties_path, array $search
	) : array
	{
		/** @var $foot_property_values Reflection_Property_Value[] value is sum result */
		$foot_property_values = [];
		/** @var $select_by_path Group_By[][] [$parent_property_path => [$property_path => Func::sum()]] */
		$select_by_path = [];
		foreach ($properties_path as $property_path) {
			/** @noinspection PhpUnhandledExceptionInspection must be valid */
			$property_value  = new Reflection_Property_Value($class_name, $property_path);
			$list_annotation = Annotation\Property\List_Annotation::of($property_value);
			if ($list_annotation->has(Annotation\Property\List_Annotation::AVERAGE)) {
				$parent_property_path = lLastParse($property_path, DOT);
				if (!isset($select_by_path[$parent_property_path])) {
					$select_by_path[$parent_property_path] = [];
				}
				$select_by_path[$parent_property_path][$property_path] = Func::average();
			}
			elseif ($list_annotation->has(Annotation\Property\List_Annotation::SUM)) {
				$parent_property_path = lLastParse($property_path, DOT);
				if (!isset($select_by_path[$parent_property_path])) {
					$select_by_path[$parent_property_path] = [];
				}
				$select_by_path[$parent_property_path][$property_path] = Func::sum();
			}
			$foot_property_values[$property_path] = $property_value;
		}
		if (!$select_by_path) {
			return [];
		}
		foreach ($select_by_path as $select) {
			$foot = $this->readDataSelect(
				$class_name, $select, $search, [Dao::timeLimit($this->time_limit)]
			);
			foreach (reset($foot->elements)->values as $property_path => $value) {
				/** @noinspection PhpUnhandledExceptionInspection must be valid */
				$foot_property_values[$property_path]
					= new Reflection_Property_Value($class_name, $property_path, floatval($value), true);
			}
		}
		return $foot_property_values;
	}

	//----------------------------------------------------------------------------------- readObjects
	/**
	 * Return only all search objects
	 *
	 * @param $class_name    string
	 * @param $list_settings Set
	 * @param $search        array search-compatible search array
	 * @param $count         Count|null
	 * @return object[]
	 */
	public function readObjects(
		string $class_name, Set $list_settings, array $search, Count $count = null
	) : array
	{
		$class = $list_settings->getClass();
		/** @var $on_list_annotations Method_Annotation[] */
		$on_list_annotations = $class->getAnnotations('on_list');
		Method_Annotation::callAll($on_list_annotations, $class->name, [&$search]);
		$options = [$list_settings->sort, Dao::doublePass()];
		if ($count) {
			$options[] = $count;
		}
		return Dao::search($search, $class_name, $options);
	}

	//--------------------------------------------------------------------- removeInvisibleProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name      string
	 * @param $properties_path string[] properties path that can include invisible properties
	 * @param $search          array search where to add Has_History criteria
	 * @return array properties path without the invisible properties
	 */
	public function removeInvisibleProperties(
		string $class_name, array $properties_path, array $search
	) : array
	{
		// remove properties directly used as columns
		foreach ($properties_path as $key => $property_path) {
			/** @noinspection PhpUnhandledExceptionInspection property must exist at this step */
			$property = new Reflection_Property($class_name, $property_path);
			if (!$property->isPublic() || !$property->isVisible(false, false)) {
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
					$history_class_name, 'property_name', null, Dao::groupBy('property_name')
				)->getRows();
				foreach ($property_names as $property_name) {
					$property_name = $property_name->getValue('property_name');
					/** @noinspection PhpUnhandledExceptionInspection already verified */
					$property   = new Reflection_Property($class_name, $property_name);
					$annotation = $property->getListAnnotation(User_Annotation::ANNOTATION);
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
					$search = [Func::andOp(array_merge([$search], $history_search))];
				}
				else {
					$search = $history_search;
				}
			}
		}
		return [array_combine($properties_path, $properties_path), $search];
	}

	//----------------------------------------------------------------------------------- reportError
	/**
	 * Log the error in order software maintainer to be informed
	 *
	 * @param $exception Exception
	 */
	protected function reportError(Exception $exception) : void
	{
		if (
			($exception instanceof Mysql_Error_Exception)
			&& Time_Limit::isErrorCodeTimeout($exception->getCode())
		) {
			$message = Loc::tr('Maximum statement execution time exceeded') . ', '
				. Loc::tr('please enter more acute search criteria') . DOT;
		}
		else {
			$handled = new Handled_Error(
				$exception->getCode(),
				$exception->getMessage(),
				$exception->getFile(),
				$exception->getLine()
			);
			$handler = new Report_Call_Stack_Error_Handler(new Call_Stack($exception));
			$handler->displayError($handled);
			$handler->logError($handled);
			$message = Loc::tr('Something wrong happened') . '. ' . Loc::tr('nothing serious') . ' : '
				. $exception->getMessage();
		}
		$this->errors[] = new Exception('', $message);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default 'list-typed' view controller
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files, string $class_name)
		: ?string
	{
		if (Session::current()->get(Navigation::class)) {
			Session::current()->remove(Navigation::class);
		}
		$this->class_names = $class_name;
		$main_object       = $parameters->getMainObject();
		$class_name = (($main_object instanceof Tools\Set) && $main_object->element_class_name)
			? $main_object->element_class_name
			: $this->forceSetMainObject($parameters);
		Loc::enterContext($class_name);
		$parameters = $this->getViewParameters($parameters, $form, $class_name);
		$view = View::run($parameters, $form, $files, Names::setToClass($class_name), static::FEATURE);
		Loc::exitContext();
		return $view;
	}

	//----------------------------------------------------------------- searchObjectsToRepresentative
	/**
	 * Replace search criterion on objects into $search by their equivalent in a OR search into its
	 * representative parts
	 *
	 * @param $class_name string
	 * @param $search     string[] search criterion
	 * @param $recurse    boolean @private true if recursive call
	 * @return array search criterion, may include Func\Logical elements for representative searches
	 */
	public function searchObjectsToRepresentative(
		string $class_name, array $search, bool $recurse = false
	) : array
	{
		foreach ($search as $property_path => $search_value) {
			if (($search_value instanceof Comparison) && is_null($search_value->than_value)) {
				continue;
			}
			// ignore numeric keys : these are additions, and do not come from the list form
			// ignore id filters, which filter current object using direct identifiers (no need to search)
			if (is_numeric($property_path) || ($property_path === 'id')) {
				continue;
			}
			try {
				$property = new Reflection_Property($class_name, $property_path);
			}
			catch (ReflectionException) {
				continue;
			}
			$property_type = $property->getType();
			if (!$property_type->isClass() || Store::of($property)->isString()) {
				continue;
			}
			$class = $property_type->asReflectionClass();
			$representative_property_names = Representative_Annotation::of($property)->values()
				?: Class_\Representative_Annotation::of($class)->values();
			if (!$representative_property_names && $class->isAbstract()) {
				$representative_property_names[] = 'representative';
			}
			if (!$representative_property_names) {
				continue;
			}

			unset($search[$property_path]);
			$add_search = [];
			$values     = ($search_value instanceof Logical) ? $search_value->arguments : [$search_value];
			foreach ($values as $value) {
				$sub_search            = [];
				$sub_search_properties = [];
				foreach ($representative_property_names as $property_name) {
					$sub_property              = $property_path . DOT . $property_name;
					$sub_search[$sub_property] = $value;
					$sub_search_properties[]   = $sub_property;
				}
				$sub_search = $this->searchObjectsToRepresentative($class_name, $sub_search, true);
				if (count($sub_search) === 1) {
					$add_search = array_merge($add_search, $sub_search);
				}
				else {
					$means_empty     = Words::meansEmpty($value);
					$object_argument = $recurse ? [] : [Func::concat($sub_search_properties, true) => $value];
					if (
						$means_empty
						|| (
							($value instanceof Comparison)
							&& in_array($value->sign, [Comparison::NOT_EQUAL, Comparison::NOT_LIKE])
						)
					) {
						$add = Func::andOp($sub_search);
						if ($object_argument && !$means_empty) {
							$add = Func::orOp(array_merge([$add], $object_argument));
						}
					}
					else {
						$add = Func::orOp(array_merge($sub_search, $object_argument));
					}
					$add_search[] = $add;
				}
			}
			if ($search_value instanceof Logical) {
				$search_value->arguments = $add_search;
				$search[] = $search_value;
			}
			else {
				$search = array_merge($search, $add_search);
			}
		}

		return $search;
	}

	//-------------------------------------------------------------------------------- searchProperty
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $property Reflection_Property
	 * @param $value    string
	 * @return Reflection_Property|Reflection_Property_Value
	 */
	private function searchProperty(Reflection_Property $property, string $value)
		: Reflection_Property|Reflection_Property_Value
	{
		if ($value === '') {
			return $property;
		}
		if ($property->getType()->isClass() && !Store::of($property)->isString()) {
			$value = Dao::read($value, $property->getType()->asString());
		}
		/** @noinspection PhpUnhandledExceptionInspection valid $property */
		$property = new Reflection_Property_Value($property->root_class, $property->path, $value, true);
		$this->prepareSearchPropertyComponent($property);
		$property->value(Loc::propertyToIso($property, $value));
		return $property;
	}

	//----------------------------------------------------------------------------- selectPrintButton
	/**
	 * Select the print button into $print_buttons which will replace the default link of
	 * $print_button
	 *
	 * @param $print_button  Button
	 * @param $print_buttons Button[]
	 */
	protected function selectPrintButton(Button $print_button, array $print_buttons) : void
	{
		if (!$print_buttons) {
			return;
		}
		$first_button         = reset($print_buttons);
		$print_button->link   = $first_button->link;
		$print_button->target = $first_button->target;
	}

}
