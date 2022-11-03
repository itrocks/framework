<?php
namespace ITRocks\Framework\Property;

use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Property;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;
use ReflectionException;

/**
 * Property search controller
 */
class Search_Controller extends Select_Controller
{

	//------------------------------------------------------------------------------------ $max_depth
	/**
	 * @var integer
	 */
	protected int $max_depth = 5;

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 * @throws ReflectionException
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$parameters->set(Parameter::CONTAINER, 'inside_tree');
		$search = $parameters->getRawParameter('search');
		if (empty($search)) {
			return parent::run($parameters, $form, $files);
		}
		$search = strtolower(str_replace(
			[DOT, '*', '?'], [BS . DOT, '.*', '.?'], strSimplify($search, '.*? ' . BS)
		));

		$class_name = Names::setToClass($parameters->shift());
		if (!$this->root_class) {
			$this->root_class = new Reflection_Class($class_name);
		}
		$properties = $this->searchProperties($class_name, $search);

		$top_property        = new Property();
		$top_property->class = $class_name;
		$objects             = $parameters->getObjects();
		array_unshift($objects, $top_property);
		$objects['class_name'] = $class_name;
		$objects['properties'] = $properties;

		return View::run($objects, $form, $files, Property::class, 'select');
	}

	//------------------------------------------------------------------------------ searchProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name         string
	 * @param $search             string
	 * @param $exclude_properties string[]
	 * @param $prefix             string
	 * @param $depth              integer
	 * @return Reflection_Property[]
	 */
	protected function searchProperties(
		string $class_name, string $search, array $exclude_properties = [], string $prefix = '',
		int $depth = 0
	) {
		/** @noinspection PhpUnhandledExceptionInspection verified $class_name */
		$class            = new Reflection_Class($class_name);
		$all_properties   = $this->getProperties($class, null, true);
		$first_properties = [];
		$properties       = [];
		$more_properties  = [];
		foreach ($all_properties as $property) {
			if (!$property) {
				break;
			}
			if (!isset($exclude_properties[$property->name])) {

				$property_path = $prefix . $property->name;
				if (($property->name == $search) || ($property_path == $search)) {
					$first_properties[$property_path] = $property;
					$matches                          = true;
				}
				else {
					preg_match(
						'|^' . $search . '|',
						strtolower(strSimplify(Loc::tr($property->name), SP)),
						$matches
					);
					preg_match(
						'|^' . $search . '|',
						strtolower(strSimplify(Loc::tr($prefix . $property_path), DOT . SP)),
						$matches2
					);
					if ($matches || $matches2) {
						$properties[$property_path] = $property;
						$matches = true;
					}
					else {
						preg_match(
							'|' . $search . '|',
							strtolower(strSimplify(Loc::tr($property_path), DOT . SP)),
							$matches
						);
						if ($matches) {
							$more_properties[$property_path] = $property;
						}
					}
				}

				if (($depth < $this->max_depth) && !$matches) {
					$type = $property->getType();
					if ($type->isClass() && !$type->isDateTime()) {
						$property_class         = $type->getElementTypeAsString();
						$is_component           = isA($property_class, Component::class);
						$exclude_sub_properties = $is_component
							? call_user_func([$property_class, 'getCompositeProperties'], $class_name)
							: [];
						$sub_properties   = $this->searchProperties(
							$type->getElementTypeAsString(), $search,
							$exclude_sub_properties, $property->name . DOT, $depth + 1
						);
						foreach ($sub_properties as $sub_property) {
							if (!isset($exclude_properties[$sub_property->name])) {
								$property_path = $property->name . DOT . $sub_property->path;
								/** @noinspection PhpUnhandledExceptionInspection generated valid $property */
								$reflection_property = new Reflection_Property(
									$class_name, $property_path, 'path'
								);
								$reflection_property->link_path  = $property_path;
								$reflection_property->link_class = $sub_property->link_class;
								$more_properties[$property_path] = $reflection_property;
							}
						}
					}
				}

			}
		}
		return array_merge($first_properties, $properties, $more_properties);
	}

}
