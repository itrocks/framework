<?php
namespace ITRocks\Framework\Feature\Add;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Edit;
use ITRocks\Framework\Feature\Output_Setting;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Property\Mandatory_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Tools\Color;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;

/**
 * The default new controller is the same as an edit controller, that accepts no object
 */
class Controller extends Edit\Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $object     object|string object or class name
	 * @param $parameters array parameters
	 * @param $settings   Setting\Custom\Set|Output_Setting\Set|null
	 * @return Button[]
	 */
	public function getGeneralButtons($object, array $parameters, Setting\Custom\Set $settings = null)
		: array
	{
		$buttons = parent::getGeneralButtons($object, $parameters, $settings);

		$close_link   = View::link(Names::classToSet(get_class($object)));
		[$close_link] = $this->prepareThen($object, $parameters, $close_link);

		return array_merge($buttons, [
			Feature::F_CLOSE => new Button(
				'Close', $close_link, Feature::F_CLOSE, [new Color('close'), Target::MAIN]
			),
		]);
	}

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * The add controller getViewParameters does this work :
	 * - initializes an empty object and sets its properties default values
	 * If you initialized it before calling getViewParameters, all properties values are overwritten
	 * - initializes values while taking them from your parameters to add (call to initializeValues)
	 * - calculates the edit controller getViewParameters
	 *
	 * @noinspection PhpDocMissingThrowsInspection ReflectionException
	 * @param $parameters Parameters The parameters sent to the add controller
	 * @param $form       array      The form sent by the caller (if POST call)
	 * @param $class_name string     The name of the class of the added object
	 * @return array The parameters for the view
	 * @see Edit_Controller::getViewParameters
	 * @see initializeValues
	 */
	protected function getViewParameters(Parameters $parameters, array $form, $class_name) : array
	{
		$object = $parameters->getMainObject($class_name);
		/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
		$properties = (new Reflection_Class($class_name))->getProperties();
		$this->initializeSubObjects($object, $properties);
		$objects    = $parameters->getObjects();
		if ((count($objects) > 1) || $form) {
			$this->initializeValues($object, array_merge($objects, $form), $properties);
		}
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$parameters['title'] = Loc::tr('New', $class_name) . SP . $parameters['title'];
		return $parameters;
	}

	//-------------------------------------------------------------------------- initializeSubObjects
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object     object
	 * @param $properties Reflection_Property[]
	 */
	protected function initializeSubObjects(object $object, array $properties)
	{
		foreach ($properties as $property) {
			/** @noinspection PhpUnhandledExceptionInspection $property->getValue($object) */
			if (
				$property->getAnnotation('component')->value
				&& Mandatory_Annotation::of($property)->value
				&& ($type = $property->getType())->isSingleClass()
				&& !$property->getValue($object)
			) {
				/** @noinspection PhpUnhandledExceptionInspection @var */
				$property->setValue($object, Builder::create($type->getElementTypeAsString()));
			}
		}
	}

	//------------------------------------------------------------------------------ initializeValues
	/**
	 * Initialize some properties values into the added object, using $objects sent as parameters
	 * to the add controller.
	 *
	 * Your controller may override it to make its own initialization in addition or in replacement of
	 * those ones.
	 *
	 * @param $object     object The added object (new / empty)
	 * @param $objects    array  The values that where sent as parameters to the add controller
	 * @param $properties Reflection_Property[] The properties of $object (all are accessible here)
	 */
	protected function initializeValues(object $object, array $objects, array $properties)
	{
		foreach (array_slice($objects, 1) as $property_name => $value) {
			// the previous object was the name of a property : the value is the matching object
			if (isset($last_property_name)) {
				$property_name = $last_property_name;
				unset($last_property_name);
			}
			// the value is an object, and the property name a Full\Class\Name :
			// initialize the first matching property (beware : this is art)
			elseif (is_object($value) && is_string($property_name) && strpos($property_name, BS)) {
				$property_name = $this->matchingProperty($property_name, $properties);
			}
			// the property is a single string : it may be the name of the property for the next object
			elseif (is_numeric($property_name) && isset($properties[$value])) {
				$last_property_name = $value;
			}
			// initializes the value for the property
			if ($property_name && isset($properties[$property_name])) {
				$type = $properties[$property_name]->getType();
				if (is_array($value) && $type->isClass() && $type->isMultiple()) {
					$class_name = $type->getElementTypeAsString();
					foreach ($value as $key => $val) {
						if (!is_object($val)) {
							$value[$key] = Dao::read($val, $class_name);
						}
					}
				}
				$object->$property_name = $value;
			}
		}
	}

	//------------------------------------------------------------------------------ matchingProperty
	/**
	 * Look into $properties for the first property whose class type matches $class_name
	 *
	 * @param $class_name string The name of the class for search
	 * @param $properties Reflection_Property[] The list of properties to search into
	 * @return ?string The name of the matching property, null if not found
	 */
	protected function matchingProperty(string $class_name, array $properties) : ?string
	{
		foreach ($properties as $property) {
			$type = $property->getType();
			if ($type->isClass() && is_a($class_name, $type->asString(), true)) {
				return $property->name;
			}
		}
		return null;
	}

}
