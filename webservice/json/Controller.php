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
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Search_Array_Builder;
use ITRocks\Framework\Tools\String_Class;

/**
 * A default json controller to output any object or objects collection into json format
 */
class Controller implements Default_Feature_Controller
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Reflection_Class
	 */
	public $class;

	//-------------------------------------------------------------------------- applyFiltersToSearch
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $search  array|object
	 * @param $filters array[]|string[] list of filters to apply (most of times string[])
	 */
	protected function applyFiltersToSearch(&$search, array $filters)
	{
		if (!(is_object($search) && $search->isAnd())) {
			$search = Func::andOp($search ? [$search] : []);
		}
		foreach ($filters as $filter_name => $filter_value) {
			if (is_string($filter_value) && strlen($filter_value) && ($filter_value[0] == '!')) {
				$filter_value = Func::notEqual(substr($filter_value, 1));
			}
			elseif (substr($filter_name, -1) === '<') {
				$filter_name  = substr($filter_name, 0, -1);
				$filter_value = Func::lessOrEqual($filter_value);
			}
			elseif (substr($filter_name, -1) === '>') {
				$filter_name  = substr($filter_name, 0, -1);
				$filter_value = Func::greaterOrEqual($filter_value);
			}
			elseif (substr($filter_name, -1) === '!') {
				$filter_name  = substr($filter_name, 0, -1);
				$filter_value = Func::notEqual($filter_value);
			}
			/** @noinspection PhpUnhandledExceptionInspection filters must be valid */
			$property = new Reflection_Property($this->class->name, $filter_name);
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
	 * @param $objects    object[]|object
	 * @param $class_name string
	 * @return string
	 */
	protected function buildJson($objects, $class_name)
	{
		$is_abstract = (new Type($class_name))->isAbstractClass();
		if (is_array($objects)) {
			$entries = [];
			foreach ($objects as $source_object) {
				$identifier = Dao::getObjectIdentifier($source_object);
				$value      = strval($source_object);
				if ($is_abstract) {
					$class_name = Builder::current()->sourceClassName(get_class($source_object));
					$entries[] = new Autocomplete_Entry_With_Class_Name($identifier, $value, $class_name);
				}
				else {
					$entries[] = new Autocomplete_Entry($identifier, $value);
				}
			}
		}
		else {
			$identifier = Dao::getObjectIdentifier($objects);
			$value      = strval($objects);
			if ($is_abstract) {
				$class_name = Builder::current()->sourceClassName(get_class($objects));
				$entries    = new Autocomplete_Entry_With_Class_Name($identifier, $value, $class_name);
			}
			else {
				$entries = new Autocomplete_Entry($identifier, $value);
			}
		}
		return json_encode($entries);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Run the default json controller
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return string
	 * @throws Exception
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		$class_name = Builder::className(Names::setToClass($class_name));
		$parameters = $parameters->getObjects();
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
	 * @param $what       object|array source object for filter, only set properties will be used for
	 *                    search
	 * @param $class_name string must be set if is $what is a filter array instead of a filter object
	 * @param $options    Option[] some options for advanced search
	 * @return object[] a collection of read objects
	 */
	protected function search($what, $class_name, array $options)
	{
		if (!Sort::in($options)) {
			$options[] = Dao::sort();
		}
		if (
			($what instanceof Logical)
			&& ($what->operator == Logical::OR_OPERATOR)
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
	protected function searchObjects($class_name, $parameters)
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
	 */
	protected function searchObjectsForAutoCompleteCombo($class_name, array $parameters)
	{
		/** @noinspection PhpUnhandledExceptionInspection verified class name */
		$this->class   = new Reflection_Class($class_name);
		$first_search  = null;
		$search        = null;
		$second_search = null;
		if (!empty($parameters['term'])) {
			$search_array_builder = new Search_Array_Builder();
			$search = $search_array_builder->buildMultiple(
				$this->class, $parameters['term'], '', '%'
			);
			$second_search = $search_array_builder->buildMultiple(
				$this->class, str_replace(' ', '%', $parameters['term']), '', '%'
			);
			$search_array_builder->and = '¤no-and-separator¤';
			$first_search = $search_array_builder->buildMultiple(
				$this->class, $parameters['term'], '', '%'
			);
		}
		if (!empty($parameters['filters'])) {
			$this->applyFiltersToSearch($first_search,  $parameters['filters']);
			$this->applyFiltersToSearch($search,        $parameters['filters']);
			$this->applyFiltersToSearch($second_search, $parameters['filters']);
		}
		$search_options = [];
		if (
			$filters = Filter_Annotation::apply($class_name, $search_options, Filter_Annotation::FOR_USE)
		) {
			$first_search  = $first_search  ? Func::andOp([$filters, $first_search])  : $filters;
			$search        = $search        ? Func::andOp([$filters, $search])        : $filters;
			$second_search = $second_search ? Func::andOp([$filters, $second_search]) : $filters;
		}

		// first object only
		if (!empty($parameters['first'])) {
			$search_options[] = Dao::limit(1);
			$objects = $this->search($first_search, $class_name, $search_options)
				?: $this->search($second_search, $class_name, $search_options)
				?: $this->search($search, $class_name, $search_options);
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
			if (is_array($search)) {
				foreach ($search as $property_name => $value) {
					/** @noinspection PhpUnhandledExceptionInspection property of the class */
					if (
						!strlen($value)
						&& (new Reflection_Property($class_name, $property_name))->getType()->isClass()
					) {
						$search[$property_name] = Func::isNull();
					}
				}
			}
			$objects = $this->search($first_search, $class_name, $search_options)
				?: $this->search($second_search, $class_name, $search_options)
				?: $this->search($search, $class_name, $search_options);
			return $this->buildJson($objects, $class_name);
		}
	}

}
