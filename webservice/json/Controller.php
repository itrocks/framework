<?php
namespace ITRocks\Framework\Webservice\Json;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Comparison;
use ITRocks\Framework\Dao\Func\Logical;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Dao\Option\Limit;
use ITRocks\Framework\Dao\Option\Sort;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Map;
use ITRocks\Framework\Reflection\Annotation\Class_\Filter_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Values_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Search_Array_Builder;
use ITRocks\Framework\Tools\String_Class;
use ReflectionException;

/**
 * A default json controller to output any object or objects collection into json format
 */
class Controller implements Default_Feature_Controller
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Reflection_Class
	 */
	public Reflection_Class $class;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * Cache for $property_names properties
	 *
	 * @var Reflection_Property[]
	 */
	public array $properties;

	//------------------------------------------------------------------------------- $property_names
	/**
	 * @var string[]
	 */
	public array $property_names = [];

	//-------------------------------------------------------------------------- applyFiltersToSearch
	/**
	 * @param $search  array|object|null
	 * @param $filters array[]|string[] list of filters to apply (most times string[])
	 * @throws ReflectionException
	 */
	protected function applyFiltersToSearch(array|object|null &$search, array $filters) : void
	{
		if (!(is_object($search) && $search->isAnd())) {
			/** @noinspection PhpConditionAlreadyCheckedInspection Inspector bug : may be [] */
			$search = Func::andOp($search ? [$search] : []);
		}
		foreach ($filters as $filter_name => $filter_value) {
			if (is_string($filter_value) && strlen($filter_value) && ($filter_value[0] === '!')) {
				$filter_value = ($filter_value === 'null')
					? Func::isNotNull()
					: Func::notEqual(substr($filter_value, 1));
			}
			elseif (str_ends_with($filter_name, '<')) {
				$filter_name  = substr($filter_name, 0, -1);
				$filter_value = Func::lessOrEqual($filter_value);
			}
			elseif (str_ends_with($filter_name, '>')) {
				$filter_name  = substr($filter_name, 0, -1);
				$filter_value = Func::greaterOrEqual($filter_value);
			}
			elseif (str_ends_with($filter_name, '!')) {
				$filter_name  = substr($filter_name, 0, -1);
				$filter_value = $this->isMultipleValues($filter_name)
					? Func::notInSet($filter_value)
					: Func::notEqual($filter_value);
			}
			elseif ($this->isMultipleValues($filter_name)) {
				$filter_value = Func::inSet($filter_value);
			}
			elseif ($filter_value === 'null') {
				$filter_value = Func::isNull();
			}
			$property = $this->class->getProperty($filter_name);
			if ($property->getType()->isDateTime()) {
				if ($filter_value instanceof Comparison) {
					$filter_value->than_value = Loc::dateToIso($filter_value->than_value);
				}
				else {
					$filter_value = Loc::dateToIso($filter_value);
				}
			}
			$search->arguments[$filter_name] = $filter_value;
		}
		if (count($search->arguments) === 1) {
			reset($search->arguments);
			$search = [key($search->arguments) => current($search->arguments)];
		}
		elseif (!$search->arguments) {
			$search = null;
		}
	}

	//------------------------------------------------------------------------------------- buildJson
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $objects    object[]|object
	 * @param $class_name string
	 * @return string
	 */
	protected function buildJson(array|object $objects, string $class_name) : string
	{
		if ($this->property_names && $objects) {
			/** @noinspection PhpDeprecatedStdLibCallInspection is_array */
			$first_object = is_array($objects) ? reset($objects) : $objects;
			foreach ($this->property_names as $property_name) {
				if (property_exists($first_object, $property_name)) {
					/** @noinspection PhpUnhandledExceptionInspection property_exists */
					$this->properties[$property_name] = new Reflection_Property($first_object, $property_name);
				}
			}
		}
		$is_abstract = (new Type($class_name))->isAbstractClass();
		if (is_array($objects)) {
			$entries = [];
			foreach ($objects as $source_object) {
				$entries[] = $this->buildJsonEntry($source_object, $is_abstract);
			}
		}
		else {
			$entries = $this->buildJsonEntry($objects, $is_abstract);
		}
		return json_encode($entries);
	}

	//-------------------------------------------------------------------------------- buildJsonEntry
	/**
	 * @param $object      object
	 * @param $is_abstract boolean
	 * @return Autocomplete_Entry
	 */
	protected function buildJsonEntry(object $object, bool $is_abstract) : Autocomplete_Entry
	{
		$identifier = Dao::getObjectIdentifier($object);
		$value      = strval($object);
		if ($is_abstract) {
			$class_name = Builder::current()->sourceClassName(get_class($object));
			$entry      = new Autocomplete_Entry_With_Class_Name($identifier, $value, $class_name);
		}
		else {
			$entry = new Autocomplete_Entry($identifier, $value);
		}
		foreach ($this->property_names as $property_name) {
			$property = $this->properties[$property_name] ?? false;
			$entry->$property_name = $property
				? Loc::propertyToLocale($property, $object->$property_name)
				: $object->$property_name;
		}
		return $entry;
	}

	//------------------------------------------------------------------------------ isMultipleValues
	/**
	 * @param $property_path string
	 * @return boolean
	 * @throws ReflectionException
	 */
	protected function isMultipleValues(string $property_path) : bool
	{
		$property = $this->class->getProperty($property_path);
		return Values_Annotation::of($property)->value && $property->getType()->isMultipleString();
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Run the default json controller
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return ?string
	 * @throws Exception
	 * @throws ReflectionException
	 */
	public function run(Parameters $parameters, array $form, array $files, string $class_name)
		: ?string
	{
		$class_name = Builder::className(Names::setToClass($class_name));
		$parameters = $parameters->getObjects();
		if (isset($parameters['property_names'])) {
			$this->property_names = $parameters['property_names'];
			unset($parameters['property_names']);
		}
		// read all objects corresponding to class name
		if (!$parameters) {
			return json_encode(Dao::readAll($class_name, Dao::sort()));
		}
		// read object
		$first_parameter = reset($parameters);
		if (is_object($first_parameter)) {
			return json_encode($first_parameter);
		}
		// single object for autocomplete pull-down list value
		if (isset($parameters['id']) && $parameters['id']) {
			$source_object = Dao::read($parameters['id'], $class_name);
			return $this->buildJson($source_object, $class_name);
		}
		// advanced search returns a json collection
		elseif (isset($parameters['search']) && $parameters['search']) {
			$objects = $this->searchObjects($class_name, $parameters);
			return json_encode($objects);
		}
		// search objects for autocomplete combo pull-down list
		elseif (isset($parameters['term'])) {
			return $this->searchObjectsForAutoCompleteCombo($class_name, $parameters);
		}
		return '';
	}

	//---------------------------------------------------------------------------------------- search
	/**
	 * Optimized search : if OR, it's best launching multiple fast searches than one slow one
	 *
	 * @param $what       object|array|null source object for filter, only set properties will be used
	 *                    for search
	 * @param $class_name string must be set if is $what is a filter array instead of a filter object
	 * @param $options    Option[] some options for advanced search
	 * @return object[] a collection of read objects
	 */
	protected function search(array|object|null $what, string $class_name, array $options) : array
	{
		if (!Sort::in($options)) {
			$options[] = Dao::sort();
		}
		if (
			($what instanceof Logical)
			&& ($what->operator === Logical::OR_OPERATOR)
			&& (count($what->arguments) > 1)
		) {
			$objects = new Map();
			foreach ($what->arguments as $argument_key => $argument) {
				$objects->add(Dao::search([$argument_key => $argument], $class_name, $options));
			}
			$objects = $objects->sort();
			foreach ($options as $option) {
				if (($option instanceof Limit) && (count($objects) > $option->count)) {
					$objects = array_slice($objects, $option->from, $option->count, true);
				}
			}
		}
		else {
			$objects = Dao::search($what, $class_name, $options);
		}
		return $objects;
	}

	//--------------------------------------------------------------------------------- searchObjects
	/**
	 * Method to search objects by parameters
	 *
	 * $parameters :
	 * - search : search criteria @example search[prop1]=foo,foo2&search[prop.prop2]=bar
	 * - limit : limit number of results
	 * - get_properties : return only specified property names values
	 *
	 * @param $class_name string
	 * @param $parameters array
	 * @return array|object[]
	 * @throws Exception
	 */
	protected function searchObjects(string $class_name, array $parameters) : array
	{
		$search               = [];
		$search_array_builder = new Search_Array_Builder();
		$search_options       = [];

		foreach ($parameters['search'] as $property_path => $value) {
			if (!($property_path && $value)) {
				throw new Exception('Invalid search parameter (value or property is empty)');
			}
			if (!Reflection_Property::exists($class_name, $property_path)) {
				throw new Exception("Search property $property_path does not exist");
			}
			$search = array_merge($search_array_builder->build($property_path, $value), $search);
		}
		if (isset($parameters['limit'])) {
			$search_options[] = Dao::limit($parameters['limit']);
		}
		if (isset($parameters['get_properties']) && $parameters['get_properties']) {
			$data    = Dao::select($class_name, $parameters['get_properties'], $search, $search_options);
			$objects = [];
			foreach ($data->getRows() as $row) {
				$objects[$row->id()] = $row->getValues();
			}
		}
		else {
			$objects = $this->search($search, $class_name, $search_options);
		}
		return $objects;
	}

	//------------------------------------------------------------- searchObjectsForAutoCompleteCombo
	/**
	 * @noinspection PhpDocMissingThrowsInspection verified class name
	 * @param $class_name string
	 * @param $parameters array
	 * @return string
	 * @throws ReflectionException
	 */
	protected function searchObjectsForAutoCompleteCombo(string $class_name, array $parameters)
		: string
	{
		/** @noinspection PhpUnhandledExceptionInspection verified class name */
		$this->class = new Reflection_Class($class_name);
		$search_1    = null;
		$search_2    = null;
		$search_3    = null;
		if (!empty($parameters['term'])) {
			$search_array_builder = new Search_Array_Builder();
			$search_2 = $search_array_builder->buildMultiple(
				$this->class, str_replace(' ', '%', $parameters['term']), '', '%'
			);
			$search_3 = $search_array_builder->buildMultiple(
				$this->class, $parameters['term'], '', '%'
			);
			$search_array_builder->and = '¤no-and-separator¤';
			$search_1 = $search_array_builder->buildMultiple(
				$this->class, $parameters['term'], '', '%'
			);
		}
		if (!empty($parameters['filters'])) {
			$this->applyFiltersToSearch($search_1, $parameters['filters']);
			$this->applyFiltersToSearch($search_2, $parameters['filters']);
			$this->applyFiltersToSearch($search_3, $parameters['filters']);
		}
		$search_options = [];
		if (
			$filters = Filter_Annotation::apply($class_name, $search_options, Filter_Annotation::FOR_USE)
		) {
			$search_1 = $search_1 ? Func::andOp([$filters, $search_1]) : $filters;
			$search_2 = $search_2 ? Func::andOp([$filters, $search_2]) : $filters;
			$search_3 = $search_3 ? Func::andOp([$filters, $search_3]) : $filters;
		}

		// first object only
		if (!empty($parameters['first'])) {
			$search_options[] = Dao::limit(1);
			$objects = $this->search($search_1, $class_name, $search_options)
				?: $this->search($search_2, $class_name, $search_options)
				?: $this->search($search_3, $class_name, $search_options);
			/** @noinspection PhpUnhandledExceptionInspection verified class name */
			$source_object = $objects
				? reset($objects)
				: ($this->class->isAbstract() ? (new String_Class) : Builder::create($class_name));
			return $this->buildJson($source_object, $class_name);
		}
		// all results from search
		else {
			if (isset($parameters['limit'])) {
				$search_options[] = Dao::limit($parameters['limit']);
			}
			if (is_array($search_3)) {
				foreach ($search_3 as $property_name => $value) {
					/** @noinspection PhpUnhandledExceptionInspection property of the class */
					if (
						!strlen($value)
						&& (new Reflection_Property($class_name, $property_name))->getType()->isClass()
					) {
						$search_3[$property_name] = Func::isNull();
					}
				}
			}
			$objects = $this->search($search_1, $class_name, $search_options)
				?: $this->search($search_2, $class_name, $search_options)
				?: $this->search($search_3, $class_name, $search_options);
			return $this->buildJson($objects, $class_name);
		}
	}

}
