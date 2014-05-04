<?php
namespace SAF\Framework\Property;

use SAF\Framework\Controller\Parameters;
use SAF\Framework\Locale\Loc;
use SAF\Framework\Property;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Tools\Names;
use SAF\Framework\View;

/**
 * Property search controller
 */
class Search_Controller extends Select_Controller
{

	//------------------------------------------------------------------------------------ $max_depth
	/**
	 * @var integer
	 */
	private $max_depth = 5;

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		$parameters->set('container', 'inside_tree');
		$search = $parameters->getRawParameter('search');
		if (empty($search)) {
			return parent::run($parameters, $form, $files);
		}
		$search = strtolower(str_replace(
			[DOT, '*', '?'], [BS . DOT, '.*', '.?'], strSimplify($search, '.*? ' . BS)
		));

		$class_name = Names::setToClass($parameters->shift());
		$properties = $this->searchProperties($class_name, $search);

		$top_property = new Property();
		$top_property->class = $class_name;
		$objects = $parameters->getObjects();
		array_unshift($objects, $top_property);
		$objects['class_name'] = $class_name;
		$objects['properties'] = $properties;
		$objects['display_full_path'] = true;

		return View::run($objects, $form, $files, Property::class, 'select');
	}

	//------------------------------------------------------------------------------ searchProperties
	/**
	 * @param $class_name     string
	 * @param $search         string
	 * @param $parent_classes string[]
	 * @param $prefix         string
	 * @return Reflection_Property[]
	 */
	protected function searchProperties($class_name, $search, $parent_classes = [], $prefix = '')
	{
		$parent_classes[$class_name] = true;
		$class = new Reflection_Class($class_name);
		$all_properties = $this->getProperties($class);
		$first_properties = [];
		$properties       = [];
		$more_properties  = [];
		foreach ($all_properties as $property) {

			$property_path = $prefix . $property->name;
			if (($property->name == $search) || ($property_path == $search)) {
				$first_properties[$property_path] = $property;
			}
			else {
				preg_match(
					'|^' . $search . '|', strtolower(strSimplify(Loc::tr($property->name))),
					$matches
				);
				preg_match(
					'|^' . $search . '|', strtolower(strSimplify(Loc::tr($prefix . $property_path), DOT)),
					$matches2
				);
				if ($matches || $matches2) {
					$properties[$property_path] = $property;
				}
				else {
					preg_match(
						'|' . $search . '|', strtolower(strSimplify(Loc::tr($property_path), DOT)), $matches
					);
					if ($matches) {
						$more_properties[$property_path] = $property;
					}
				}
			}

			if (count($parent_classes) < $this->max_depth) {
				$type = $property->getType();
				if ($type->isClass() && !isset($parent_classes[$type->getElementTypeAsString()])) {
					$sub_properties = $this->searchProperties(
						$type->getElementTypeAsString(), $search, $parent_classes, $property->name . DOT
					);
					foreach ($sub_properties as $sub_property) {
						$more_properties[] = new Reflection_Property(
							$class_name, $property->name . DOT . $sub_property->path
						);
					}
				}
			}

		}
		$properties = array_merge($first_properties, $properties, $more_properties);
		return $properties;
	}

}
