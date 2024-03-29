<?php
namespace ITRocks\Framework\Feature\Validate;

use ITRocks\Framework\AOP\Joinpoint\Before_Method;
use ITRocks\Framework\Builder;
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
use ITRocks\Framework\Feature\Validate\Property\Validate_Annotation;
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
use ITRocks\Framework\Reflection\Attribute;
use ITRocks\Framework\Reflection\Attribute\Class_\Unique;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Property\Composite;
use ITRocks\Framework\Reflection\Attribute\Property\Decimals;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Call_Stack;
use ITRocks\Framework\Tools\Date_Time_Error;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;

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
	 * @var Reflection\Annotation[]|Annotation[]|Property\Annotation[]
	 */
	public array $report = [];

	//---------------------------------------------------------------------------------------- $valid
	/**
	 * true if the last validated object was valid, else message string
	 *
	 * @read_only
	 * @var string|true|null
	 */
	public bool|string|null $valid;

	//--------------------------------------------------------------------------------- $validator_on
	public bool $validator_on = false;

	//----------------------------------------------------------------------------------- $warning_on
	public bool $warning_on = false;

	//------------------------------------------------------------------------ afterMainControllerRun
	public function afterMainControllerRun() : void
	{
		$this->validator_on = false;
	}

	//------------------------------------------------------------------------ afterSaveControllerRun
	public function afterSaveControllerRun() : void
	{
		$this->warning_on = false;
	}

	//---------------------------------------------------------------------- afterSaveControllerWrite
	/** @throws Exception */
	public function afterSaveControllerWrite(array $write_objects) : void
	{
		if ($this->warningEnabled() && $this->getWarnings()) {
			throw new Exception($this->notValidated(reset($write_objects)->object));
		}
	}

	//----------------------------------------------------------------------- beforeMainControllerRun
	public function beforeMainControllerRun() : void
	{
		$this->validator_on = true;
	}

	//--------------------------------------------------------------------- beforePropertyStoreString
	/**
	 * This is called before an object is changed to its string value before writing it using the data
	 * link. Objects must be validated before doing this
	 *
	 * @noinspection PhpDocMissingThrowsInspection ReflectionException
	 * @throws Exception
	 */
	public function beforePropertyStoreString(Before_Method $joinpoint) : void
	{
		$object = $joinpoint->parameters['value'];
		if (is_object($object) && $this->validator_on) {
			/** @noinspection PhpUnhandledExceptionInspection object, valid property */
			$property = new Reflection\Reflection_Property($joinpoint->object, 'options');
			/** @noinspection PhpUnhandledExceptionInspection property is of object and accessible */
			$options = $property->getValue($joinpoint->object);
			if (!Null_Object::isNull($object)) {
				$this->beforeWrite($object, $options, null);
			}
		}
	}

	//----------------------------------------------------------------------- beforeSaveControllerRun
	public function beforeSaveControllerRun(Parameters $parameters) : void
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
	 * @param  $before_write_annotation ?string
	 * @throws Exception
	 */
	public function beforeWrite(object $object, array $options, ?string $before_write_annotation)
		: void
	{
		if (
			!$this->validator_on
			|| ($before_write_annotation === 'before_writes')
			|| ($object instanceof Except)
		) {
			return;
		}
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
			throw new Exception($this->notValidated($object, $only, $exclude));
		}
	}

	//------------------------------------------------------------------------------- createSubObject
	/**
	 * Create a new empty component sub-object
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Component
	 */
	private function createSubObject(object $object, Reflection_Property $property) : object
	{
		/** @noinspection PhpUnhandledExceptionInspection var annotation values must be valid */
		$link_class = new Reflection\Reflection_Class($property->getType()->getElementTypeAsString());
		/** @var $sub_object Component */
		/** @noinspection PhpUnhandledExceptionInspection must be valid */
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
	public function disable() : bool
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
	public function enable() : bool
	{
		$validator_on       = $this->validator_on;
		$this->validator_on = true;
		return $validator_on;
	}

	//-------------------------------------------------------------------------------- getConfirmLink
	/** @noinspection PhpUnused not_validated.html */
	public function getConfirmLink() : string
	{
		$uri = lParse(SL . rParse($_SERVER['REQUEST_URI'], SL, 2), '?');
		return $uri . (str_contains($uri, '?') ? '&' : '?') . 'confirm=1';
	}

	//------------------------------------------------------------------------------------- getErrors
	/** @return Annotation[] */
	public function getErrors() : array
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
	 * @noinspection PhpUnused not_validated.html
	 * @return Annotation[]
	 */
	public function getMessages() : array
	{
		return array_merge($this->getWarnings(), $this->getErrors());
	}

	//----------------------------------------------------------------------------- getPostProperties
	/**
	 * Return all properties passed in POST originator form
	 *
	 * @noinspection PhpUnused not_validated.html
	 * @return string[]
	 */
	public function getPostProperties() : array
	{
		return $this->getPropertiesToString($_POST);
	}

	//------------------------------------------------------------------------- getPropertiesToString
	/**
	 * TODO NORMAL There is probably already a function for that somewhere. If not, should be !
	 *
	 * @return string[]
	 */
	private function getPropertiesToString(array $elements = [], string $base_path = '') : array
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
	/** @return Annotation[] */
	public function getWarnings() : array
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
	private function notValidated(object $object, array $only = [], array $exclude = []) : string
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
	public function register(Register $register) : void
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
			'regex'      => Property\Regex_Annotation::class,
			'signed'     => Property\Signed_Annotation::class,
			'validate'   => Property\Validate_Annotation::class,
			'var'        => Property\Var_Annotation::class,
			'warning'    => Property\Warning_Annotation::class,
		]);
		$builder = Builder::current();
		$builder->setReplacement(Decimals::class,  Property\Decimals::class);
		$builder->setReplacement(Mandatory::class, Property\Mandatory::class);
		$builder->setReplacement(Unique::class,    Class_\Unique::class);
		$builder->setReplacement(Values::class,    Property\Values::class);
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object             object
	 * @param $only_properties    string[] property names if we want to check those properties only
	 * @param $exclude_properties string[] property names if we don't want to check those properties
	 * @param $component          boolean
	 * @return string|true|null @values Result::const
	 */
	public function validate(
		object $object, array $only_properties = [], array $exclude_properties = [],
		bool $component = false
	) : bool|string|null
	{
		/** @noinspection PhpUnhandledExceptionInspection object */
		$class = new Link_Class($object);
		/** @var $properties Reflection_Property[] */
		$properties = Replaces_Annotations::removeReplacedProperties(
			Reflection\Annotation\Class_\Link_Annotation::of($class)->value
				? $class->getLinkProperties()
				: $class->getProperties()
		);

		$this->valid = Result::andResult(
			$this->validateProperties(
				$object, $properties, $only_properties, $exclude_properties, $component
			),
			$this->validateObject($object, $class)
		);

		return $this->valid;
	}

	//---------------------------------------------------------------------------- validateAnnotation
	/**
	 * @noinspection PhpDocSignatureInspection $annotation
	 * @param $object     object
	 * @param $annotation Reflection\Annotation|Annotation|Common
	 * @return string|true|null @values Result::const
	 */
	protected function validateAnnotation(object $object, object $annotation) : bool|string|null
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
			if (($annotation->valid === Result::ERROR) && ($annotation instanceof Property\Mandatory)) {
				foreach ($this->report as $key => $report_line) {
					if ($report_line->property->is($annotation->property)) {
						unset($this->report[$key]);
					}
				}
			}
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
	 * @return string|true|null @values Result::const
	 */
	protected function validateAnnotations(object $object, array $annotations) : bool|string|null
	{
		$result = true;
		foreach ($annotations as $annotation) {
			if (is_array($annotation)) {
				$result = Result::andResult($result, $this->validateAnnotations($object, $annotation));
			}
			elseif (isA($annotation, Annotation::class)) {
				$result = Result::andResult($result, $this->validateAnnotation($object, $annotation));
			}
		}
		return $result;
	}

	//---------------------------------------------------------------------------- validateAttributes
	/**
	 * Returns true if the object follows attribute rules
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object     object
	 * @param $attributes Reflection\Reflection_Attribute[]
	 * @return string|true|null @values Result::const
	 */
	protected function validateAttributes(object $object, array $attributes) : bool|string|null
	{
		$result = true;
		foreach ($attributes as $attribute) {
			if (is_array($attribute)) {
				$result = Result::andResult($result, $this->validateAttributes($object, $attribute));
			}
			elseif (isA($attribute->getName(), Annotation::class)) {
				/** @noinspection PhpUnhandledExceptionInspection Must be valid */
				$attribute = $attribute->newInstance();
				/** @var $attribute Annotation */
				$result = Result::andResult($result, $this->validateAnnotation($object, $attribute));
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
	 * @param $property           Reflection_Property
	 * @return string|true|null @values Result::const
	 */
	private function validateComponent(
		object $object,
		array $only_properties,
		array $exclude_properties,
		Reflection_Property $property
	) : bool|string|null
	{
		$result = true;
		$type   = $property->getType();
		if ($type->isClass()) {
			/** @var $sub_objects object|object[] */
			$sub_objects = $object->{$property->name};
			if (!$sub_objects && Mandatory::of($property)->value) {
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
						$result, $this->validate($sub_object, $only_properties, $exclude_properties, true)
					);
				}
				// update properties path of report annotations to be relative to parent property
				$property_class_name = $type->getElementTypeAsString();
				$class_name          = get_class($object);
				foreach ($this->report as $key => $annotation) {
					/** @var $annotation_property Reflection\Reflection_Property */
					if (
						isA($annotation, Property\Annotation::class)
						&& isA($annotation_property = $annotation->property, Reflection\Reflection_Property::class)
						&& $annotation_property->root_class === $property_class_name
					) {
						$annotation = clone $annotation;
						/** @noinspection PhpUnhandledExceptionInspection $class_name comes from an object */
						$annotation->property = new Reflection\Reflection_Property(
							$class_name, $property->path . DOT . $annotation_property->path
						);
						$this->report[$key] = $annotation;
					}
				}
				// merge saved report with this
				$this->report = array_merge($report, $this->report);
			}
		}
		return $result;
	}

	//-------------------------------------------------------------------------------- validateObject
	/** @return string|true|null @values Result::const */
	protected function validateObject(object $object, Reflection_Class $class) : bool|string|null
	{
		return Result::andResult(
			$this->validateAnnotations($object, $class->getAnnotations()),
			$this->validateAttributes($object, $class->getAttributes())
		);
	}

	//---------------------------------------------------------------------------- validateProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object             object
	 * @param $properties         Reflection_Property[]
	 * @param $only_properties    string[]
	 * @param $exclude_properties string[]
	 * @param $component          boolean
	 * @return string|true|null @values Result::const
	 */
	protected function validateProperties(
		object $object, array $properties, array $only_properties, array $exclude_properties,
		bool $component = false
	) : bool|string|null
	{
		$result             = true;
		$exclude_properties = array_flip($exclude_properties);
		$only_properties    = array_flip($only_properties);
		foreach ($properties as $property) {
			/** @noinspection PhpUnhandledExceptionInspection $property->getValue must be valid */
			if (!(
				(
					!$property->isStatic()
					&& (!$only_properties || isset($only_properties[$property->name]))
					&& !isset($exclude_properties[$property->name])
					&& !$property->getAnnotation('calculated')->value
					&& (!$component || !Composite::of($property)?->value)
					&& !$property->getAnnotation('link_composite')->value
				)
				|| (
					$property->getAnnotation('force_validate')->value
					&& !(new Call_Stack)->calledMethodArguments(
						[static::class, 'validate'], ['object' => $property->getValue($object)]
					)
				)
			)) {
				continue;
			}
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
			if (!$var_is_valid) {
				continue;
			}
			// if value is not set and is a link (component or not), then we validate only mandatory
			if (!isset($object->{$property->name}) && Link_Annotation::of($property)->value) {
				$result = Result::andResult($result, $this->validateAnnotations(
					$object, [Mandatory::of($property), Validate_Annotation::allOf($property)]
				));
				continue;
			}
			// otherwise we validate all annotations, and recurse if is component
			$result = Result::andResult($result, $this->validateAnnotations(
				$object, $property->getAnnotations()
			));
			$result = Result::andResult($result, $this->validateAttributes(
				$object, $property->getAttributes()
			));
			if (
				Attribute\Property\Component::of($property)?->value
				|| Link_Annotation::of($property)->isCollection()
				|| (Integrated_Annotation::of($property)->value && Mandatory::of($property)->value)
			) {
				$result = Result::andResult($result, $this->validateComponent(
					$object, $only_properties, $exclude_properties, $property
				));
			}
		}
		return $result;
	}

	//-------------------------------------------------------------------------------- warningEnabled
	/** Return true if warning check are enabled */
	public function warningEnabled() : bool
	{
		return $this->validator_on && $this->warning_on;
	}

}
