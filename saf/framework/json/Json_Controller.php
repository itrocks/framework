<?php
namespace SAF\Framework\Json;

use SAF\Framework\Builder;
use SAF\Framework\Controller\Default_Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Dao;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Tools\Names;
use SAF\Framework\Tools\Search_Array_Builder;

/**
 * A default json controller to output any object or objects collection into json format
 */
class Json_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Run the default json controller
	 *
	 * @param $parameters Parameters
	 * @param $form array
	 * @param $files array
	 * @param $class_name string
	 * @return string
	 */
	public function run(Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = $parameters->getObjects();
		// read all objects corresponding to class name
		if (!$parameters) {
			return json_encode(Dao::readAll(Names::setToClass($class_name, false), [Dao::sort()]));
		}
		// read object
		$first_parameter = reset($parameters);
		if (is_object($first_parameter)) {
			return json_encode($first_parameter);
		}
		// search objects for autocomplete combo pull-down list
		if (isset($parameters['term'])) {
			$element_class_name = Names::setToClass($class_name, false);
			$search = null;
			if (!empty($parameters['term'])) {
				$search = (new Search_Array_Builder)->buildMultiple(
					new Reflection_Class($element_class_name), $parameters['term'], '', '%'
				);
			}
			if (isset($parameters['filters']) && $parameters['filters']) {
				if (!(is_object($search) && $search->isAnd())) {
					$search = Dao\Func::andOp($search ? [$search] : []);
				}
				foreach ($parameters['filters'] as $filter_name => $filter_value) {
					$search->arguments[$filter_name] = ($filter_value[0] == '!')
						? Dao\Func::notEqual(substr($filter_value, 1))
						: $filter_value;
				}
				if (count($search->arguments) == 1) {
					reset($search->arguments);
					$search = [key($search->arguments) => current($search->arguments)];
				}
			}
			$objects = [];
			// first object only
			if (isset($parameters['first']) && $parameters['first']) {
				$objects = Dao::search($search, $element_class_name, [Dao::sort(), Dao::limit(1)]);
				$source_object = $objects ? reset($objects) : Builder::create($element_class_name);
				return json_encode(new Autocomplete_Entry(
					Dao::getObjectIdentifier($source_object), strval($source_object)
				));
			}
			// all results from search
			else {
				$search_options = [Dao::sort()];
				if (isset($parameters['limit'])) {
					$search_options[] = Dao::limit($parameters['limit']);
				}
				foreach (Dao::search($search, $element_class_name, $search_options) as $source_object) {
					$objects[] = new Autocomplete_Entry(
						Dao::getObjectIdentifier($source_object), strval($source_object)
					);
				}
				return json_encode($objects);
			}
		}
		// single object for autocomplete pull-down list value
		elseif (isset($parameters['id'])) {
			$element_class_name = Names::setToClass($class_name);
			$source_object = Dao::read($parameters['id'], $element_class_name);
			return json_encode(new Autocomplete_Entry(
				Dao::getObjectIdentifier($source_object), strval($source_object)
			));
		}
		return '';
	}

}
