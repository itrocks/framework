<?php
namespace ITRocks\Framework\Feature\Validate;

use ITRocks\Framework\AOP\Joinpoint\Before_Method;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Data_Link\Object_To_Write_Array;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Dao\Option\Exclude;
use ITRocks\Framework\Dao\Option\Link_Class_Only;
use ITRocks\Framework\Dao\Option\Only;
use ITRocks\Framework\Feature\Save;
use ITRocks\Framework\Feature\Validate\Annotation\Warning_Annotation;
use ITRocks\Framework\Feature\Validate\Property;
use ITRocks\Framework\Feature\Validate\Property\Mandatory_Annotation;
use ITRocks\Framework\Feature\Validate\Property\Var_Annotation;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Mapper\Null_Object;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Parser;
use ITRocks\Framework\Reflection\Annotation\Property\Integrated_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Call_Stack;
use ITRocks\Framework\Tools\Date_Time_Error;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;
use ITRocks\Framework\View\View_Exception;

/**
 * The object validator links validation processes to objects
 */
class Validator implements Registerable
{
	use Has_Get;

	//--------------------------------------------------------------------------------------- $report
	/**
	 * The report is made of validate annotations that have been validated or not
	 *
	 * If you launch multiple validations with exception capture, don't forget to reset the report,
	 * or it will be accumulated for your further validations !
	 *
	 * @var Reflection\Annotation[]|Annotation[]
	 */
	public $report = [];

	//---------------------------------------------------------------------------------------- $valid
	/**
	 * true if the last validated object was valid, else false
	 *
	 * @read_only
	 * @var boolean
	 */
	public $valid;

	//--------------------------------------------------------------------------------- $validator_on
	/**
	 * @var boolean
	 */
	public $validator_on = false;

	//----------------------------------------------------------------------------------- $warning_on
	/**
	 * @var boolean
	 */
	public $warning_on = false;

	//------------------------------------------------------------------------ afterMainControllerRun
	public function afterMainControllerRun()
	{
		$this->validator_on = false;
	}

	//------------------------------------------------------------------------ afterSaveControllerRun
	public function afterSaveControllerRun()
	{
		$this->warning_on = false;
	}

	//---------------------------------------------------------------------- afterSaveControllerWrite
	/**
	 * @param $write_objects array
	 * @throws View_Exception
	 */
	public function afterSaveControllerWrite($write_objects)
	{
		if ($this->warningEnabled() && $this->getWarnings()) {
			throw new View_Exception($this->notValidated(reset($write_objects)->object));
		}
	}

	//----------------------------------------------------------------------- beforeMainControllerRun
	public function beforeMainControllerRun()
	{
		$this->validator_on = true;
	}

	//--------------------------------------------------------------------- beforePropertyStoreString
	/**
	 * This is called before an object is changed to its string value before writing it using the data
	 * link. Objects must be validated before doing this
	 *
	 * @noinspection PhpDocMissingThrowsInspection ReflectionException
	 * @param $joinpoint Before_Method
	 * @throws View_Exception
	 */
	public function beforePropertyStoreString(Before_Method $joinpoint)
	{
		$object = $joinpoint->parameters['value'];
		if (is_object($object) && $this->validator_on) {
			/** @noinspection PhpUnhandledExceptionInspection object, valid property */
			$property = new Reflection\Reflection_Property($joinpoint->object, 'options');
			$property->setAccessible(true);
			/** @noinspection PhpUnhandledExceptionInspection property is of object and accessible */
			$options = $property->getValue($joinpoint->object);
			if (!Null_Object::isNull($object)) {
				$this->beforeWrite($object, $options, null);
			}
		}
	}

	//----------------------------------------------------------------------- beforeSaveControllerRun
	/**
	 * @param $parameters Parameters
	 */
	public function beforeSaveControllerRun(Parameters $parameters)
	{
		if (!$parameters->getRawParameter('confirm')) {
			$this->warning_on = true;
		}
	}

	//----------------------------------------------------------------------------------- beforeWrite
	/**
	 * The validator hook is called before each Data_Link::write() call to validate the object
	 * before writing it.
	 *
	 * @param  $object                  object
	 * @param  $options                 Option[]
	 * @param  $before_write_annotation string
	 * @throws View_Exception
	 */
	public function beforeWrite($object, array $options, $before_write_annotation)
	{
		if (($before_write_annotation === 'before_writes') || ($object instanceof Exception)) {
			return;
		}
		if ($this->validator_on) {
			$exclude = [];
			$only    = [];
			foreach ($options as $option) {
				if ($option instanceof Exclude) {
					$exclude = array_merge($exclude, $option->properties);
				}
				elseif ($option instanceof Only) {
					$only = array_merge($only, $option->properties);
				}
				elseif ($option instanceof Skip) {
					$skip = true;
				}
				elseif ($option instanceof Link_Class_Only) {
					if (!$only) {
						$only = array_keys(Link_Class_Only::propertiesOf($object));
					}
				}
			}
			if (!isset($skip) && !Result::isValid($this->validate($object, $only, $exclude), true)) {
				throw new View_Exception($this->notValidated($object, $only, $exclude));
			}
		}
	}

	//------------------------------------------------------------------------------- createSubObject
	/**
	 * Create a new empty component sub-object
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object   object
	 * @param $property Reflection_Property
	 * @return object|Component
	 */
	private function createSubObject($object, Reflection_Property $property)
	{
		/** @noinspection PhpUnhandledExceptionInspection var annotation values must be valid */
		$link_class = new Reflection\Reflection_Class($property->getType()->getElementTypeAsString());
		/** @var $sub_object Component */
		$sub_object = $link_class->newInstance();
		// we attach composite object, but we do not set the sub_object in its parent property
		// we simply want to validate the new object, not to save it !
		$sub_object->setComposite($object);
		return $sub_object;
	}

	//--------------------------------------------------------------------------------------- disable
	/**
	 * Disable validator and return previous status
	 *
	 * @return boolean true if was enabled, false if was disabled
	 */
	public function disable()
	{
		$validator_on       = $this->validator_on;
		$this->validator_on = false;
		return $validator_on;
	}

	//---------------------------------------------------------------------------------------- enable
	/**
	 * Enable validator and return previous status
	 *
	 * @return boolean true if was enabled, false if was disabled
	 */
	public function enable()
	{
		$validator_on       = $this->validator_on;
		$this->validator_on = true;
		return $validator_on;
	}

	//-------------------------------------------------------------------------------- getConfirmLink
	/**
	 * @return string
	 */
	public function getConfirmLink()
	{
		$uri = lParse(SL . rParse($_SERVER['REQUEST_URI'], SL, 2), '?', 1, true);
		return $uri . ((strpos($uri, '?') !== false) ? '&' : '?') . 'confirm=1';
	}

	//------------------------------------------------------------------------------------- getErrors
	/**
	 * @return Annotation[]
	 */
	public function getErrors()
	{
		$errors = [];
		foreach ($this->report as $annotation) {
			if ($annotation->valid === Result::ERROR) {
				$errors[] = $annotation;
			}
		}
		return $errors;
	}

	//----------------------------------------------------------------------------------- getMessages
	/**
	 * Return all annotations - and their messages - for the view
	 *
	 * @return Annotation[]
	 */
	public function getMessages()
	{
		return array_merge($this->getWarnings(), $this->getErrors());
	}

	//----------------------------------------------------------------------------- getPostProperties
	/**
	 * Return all properties passed in POST originator form
	 *
	 * @return string[]
	 */
	public function getPostProperties()
	{
		return $this->getPropertiesToString($_POST);
	}

	//------------------------------------------------------------------------- getPropertiesToString
	/**
	 * TODO NORMAL There is probably already a function for that somewhere. If not, should be !
	 *
	 * @param $elements  array
	 * @param $base_path string
	 * @return string[]
	 */
	private function getPropertiesToString(array $elements = [], $base_path = '')
	{
		$properties = [];
		foreach ($elements as $key => $element) {
			$path = $base_path
				? ($base_path . '[' . $key . ']')
				: $key;
			if (is_array($element)) {
				$properties = array_merge($properties, $this->getPropertiesToString($element, $path));
			}
			else {
				$properties[$path] = $element;
			}
		}
		return $properties;
	}

	//----------------------------------------------------------------------------------- getWarnings
	/**
	 * @return Annotation[]
	 */
	public function getWarnings()
	{
		$warning = [];
		foreach ($this->report as $annotation) {
			if ($annotation->valid === Result::WARNING) {
				$warning[] = $annotation;
			}
		}
		return $warning;
	}

	//---------------------------------------------------------------------------------- notValidated
	/**
	 * @param $object  object
	 * @param $only    string[] only property names
	 * @param $exclude string[] excluded property names
	 * @return string
	 */
	private function notValidated($object, array $only = [], array $exclude = [])
	{
		$parameters = [
			$this,
			'exclude'            => $exclude,
			'object'             => $object,
			'only'               => $only,
			Parameter::AS_WIDGET => true,
			Template::TEMPLATE   => 'not_validated'
		];
		return View::run($parameters, [], [], get_class($object), 'validate');
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->afterMethod(
			[Data_Link\Write::class, 'beforeWrite'], [$this, 'beforeWrite']
		);
		$register->aop->afterMethod(
			[Main::class, 'runController'], [$this, 'afterMainControllerRun']
		);
		$register->aop->beforeMethod(
			[Main::class, 'runController'], [$this, 'beforeMainControllerRun']
		);
		$register->aop->beforeMethod(
			[Object_To_Write_Array::class, 'propertyStoreString'], [$this, 'beforePropertyStoreString']
		);
		$register->aop->afterMethod(
			[Save\Controller::class, 'run'], [$this, 'afterSaveControllerRun']
		);
		$register->aop->beforeMethod(
			[Save\Controller::class, 'run'], [$this, 'beforeSaveControllerRun']
		);
		$register->aop->afterMethod(
			[Save\Controller::class, 'write'], [$this, 'afterSaveControllerWrite']
		);
		$register->setAnnotations(Parser::T_CLASS, [
			'validate'   => Class_\Validate_Annotation::class,
			'warning'    => Class_\Warning_Annotation::class,
		]);
		$register->setAnnotations(Parser::T_PROPERTY, [
			'characters' => Property\Characters_Annotation::class,
			'length'     => Property\Length_Annotation::class,
			'mandatory'  => Property\Mandatory_Annotation::class,
			'max_length' => Property\Max_Length_Annotation::class,
			'max_value'  => Property\Max_Value_Annotation::class,
			'min_length' => Property\Min_Length_Annotation::class,
			'min_value'  => Property\Min_Value_Annotation::class,
			'precision'  => Property\Precision_Annotation::class,
			'regex'      => Property\Regex_Annotation::class,
			'signed'     => Property\Signed_Annotation::class,
			'validate'   => Property\Validate_Annotation::class,
			'values'     => Property\Values_Annotation::class,
			'var'        => Property\Var_Annotation::class,
			'warning'    => Property\Warning_Annotation::class,
		]);
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object             object
	 * @param $only_properties    string[] property names if we want to check those properties only
	 * @param $exclude_properties string[] property names if we don't want to check those properties
	 * @return string|null|true @values Result::const
	 */
	public function validate($object, array $only_properties = [], array $exclude_properties = [])
	{
		/** @noinspection PhpUnhandledExceptionInspection object */
		$class      = new Link_Class($object);
		$properties = Replaces_Annotations::removeReplacedProperties(
			Reflection\Annotation\Class_\Link_Annotation::of($class)->value
				? $class->getLinkProperties()
				: $class->accessProperties()
		);

		$this->valid = Result::andResult(
			$this->validateProperties($object, $properties, $only_properties, $exclude_properties),
			$this->validateObject($object, $class)
		);

		return $this->valid;
	}

	//---------------------------------------------------------------------------- validateAnnotation
	/**
	 * @param $object     object
	 * @param $annotation Reflection\Annotation|Annotation
	 * @return string|null|true @values Result::const
	 */
	protected function validateAnnotation($object, Reflection\Annotation $annotation)
	{
		$annotation->object = $object;
		$annotation->valid  = $annotation->validate($object);
		if ($annotation->valid === true) {
			$annotation->valid = Result::INFORMATION;
		}
		elseif ($annotation->valid === false) {
			$annotation->valid = ($annotation instanceof Warning_Annotation)
				? Result::WARNING
				: Result::ERROR;
		}
		if (!in_array($annotation->valid, [Result::NONE, true], true)) {
			$this->report[] = $annotation;
		}
		return $annotation->valid;
	}

	//--------------------------------------------------------------------------- validateAnnotations
	/**
	 * Returns true if the object follows validation rules
	 *
	 * @param $object      object
	 * @param $annotations Reflection\Annotation[]|Annotation[]
	 * @return string|null|true @values Result::const
	 */
	protected function validateAnnotations($object, array $annotations)
	{
		$result = true;
		foreach ($annotations as $annotation_name => $annotation) {
			if (is_array($annotation)) {
				$result = Result::andResult($result, $this->validateAnnotations($object, $annotation));
			}
			elseif (isA($annotation, Annotation::class)) {
				$result = Result::andResult($result, $this->validateAnnotation($object, $annotation));
			}
		}
		return $result;
	}

	//----------------------------------------------------------------------------- validateComponent
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object             object
	 * @param $only_properties    string[]
	 * @param $exclude_properties string[]
	 * @param $property           Reflection\Reflection_Property
	 * @return string|null|true @values Result::const
	 */
	private function validateComponent(
		$object,
		array $only_properties,
		array $exclude_properties,
		Reflection\Reflection_Property $property
	) {
		$result = true;
		$type   = $property->getType();
		if ($type->isClass()) {
			/** @var $sub_objects object|object[] */
			$sub_objects = $object->{$property->name};
			if (!$sub_objects && Mandatory_Annotation::of($property)->value) {
				$sub_objects = $this->createSubObject($object, $property);
			}
			if ($sub_objects && !is_array($sub_objects)) {
				$sub_objects = [$sub_objects];
			}
			if ($sub_objects) {
				// save current report
				$report       = $this->report;
				$this->report = [];
				// validate sub_objects
				$exclude_properties = (new Option\Only($exclude_properties))
					->subObjectOption($property->name, true)->properties;
				$only_properties = (new Option\Only($only_properties))
					->subObjectOption($property->name, true)->properties;
				foreach ($sub_objects as $sub_object) {
					$result = Result::andResult(
						$result, $this->validate($sub_object, $only_properties, $exclude_properties)
					);
				}
				// update properties path of report annotations to be relative to parent property
				$property_class_name = $type->getElementTypeAsString();
				$class_name          = get_class($object);
				foreach ($this->report as $annotation) {
					if (
						isA($annotation, Property\Annotation::class)
						&& $annotation->property
						&& isA($annotation->property, Reflection\Reflection_Property::class)
						&& $annotation->property->root_class == $property_class_name
					) {
						/** @noinspection PhpUnhandledExceptionInspection $class_name comes from an object */
						$annotation->property = new Reflection\Reflection_Property(
							$class_name, $property->path . DOT . $annotation->property->path
						);
					}
				}
				// merge saved report with this
				$this->report = array_merge($report, $this->report);
			}
		}
		return $result;
	}

	//-------------------------------------------------------------------------------- validateObject
	/**
	 * @param $object object
	 * @param $class  Reflection_Class
	 * @return string|null|true @values Result::const
	 */
	protected function validateObject($object, Reflection_Class $class)
	{
		return $this->validateAnnotations($object, $class->getAnnotations());
	}

	//---------------------------------------------------------------------------- validateProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object             object
	 * @param $properties         Reflection_Property[]
	 * @param $only_properties    string[]
	 * @param $exclude_properties string[]
	 * @return string|null|true @values Result::const
	 */
	protected function validateProperties(
		$object, array $properties, array $only_properties, array $exclude_properties
	) {
		$result             = true;
		$exclude_properties = array_flip($exclude_properties);
		$only_properties    = array_flip($only_properties);
		foreach ($properties as $property) {
			/** @noinspection PhpUnhandledExceptionInspection $property from $object and accessible */
			if (
				(
					!$property->isStatic()
					&& (!$only_properties || isset($only_properties[$property->name]))
					&& !isset($exclude_properties[$property->name])
					&& !$property->getAnnotation('calculated')->value
					&& !$property->getAnnotation('composite')->value
					&& !$property->getAnnotation('link_composite')->value
				)
				|| (
					$property->getAnnotation('force_validate')->value
					&& !(new Call_Stack)->calledMethodArguments(
						[static::class, 'validate'], ['object' => $property->getValue($object)]
					)
				)
			) {
				$type = $property->getType();
				// we could do this control for all, but this may run getters and useless data reads
				// this control was added for date-time format control, and nothing else
				$var_is_valid = true;
				/** @noinspection PhpUnhandledExceptionInspection $property from $object and accessible */
				if ($type->isDateTime() && ($property->getValue($object) instanceof Date_Time_Error)) {
					$var_annotation = new Var_Annotation($type->asString(), $property);
					$var_annotation->reportMessage(Loc::tr('bad format'));
					$var_annotation->valid = Result::ERROR;
					$this->report[]        = $var_annotation;
					$var_is_valid          = false;
					$result                = Result::andResult($result, Result::ERROR);
				}
				if ($var_is_valid) {
					// if value is not set and is a link (component or not), then we validate only mandatory
					if (
						!isset($object->{$property->name})
						&& Reflection\Annotation\Class_\Link_Annotation::of($property)->value
					) {
						$result = Result::andResult($result, $this->validateAnnotations(
							$object, [Mandatory_Annotation::of($property)]
						));
					}
					// otherwise we validate all annotations, and recurse if is component
					else {
						$result = Result::andResult($result, $this->validateAnnotations(
							$object, $property->getAnnotations()
						));
						if (
							$property->getAnnotation('component')->value
							|| Link_Annotation::of($property)->isCollection()
							|| (
								Integrated_Annotation::of($property)->value
								&& Mandatory_Annotation::of($property)->value
							)
						) {
							$result = Result::andResult($result, $this->validateComponent(
								$object, $only_properties, $exclude_properties, $property
							));
						}
					}
				}
			}
		}
		return $result;
	}

	//-------------------------------------------------------------------------------- warningEnabled
	/**
	 * Return true if warning check are enabled
	 *
	 * @return boolean
	 */
	public function warningEnabled()
	{
		return $this->validator_on && $this->warning_on;
	}

}
