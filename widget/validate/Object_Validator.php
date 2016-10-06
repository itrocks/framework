<?php
namespace SAF\Framework\Widget\Validate;

use SAF\Framework\Controller\Main;
use SAF\Framework\Controller\Parameter;
use SAF\Framework\Dao\Data_Link;
use SAF\Framework\Dao\Option;
use SAF\Framework\Dao\Option\Exclude;
use SAF\Framework\Dao\Option\Only;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Reflection\Annotation\Class_\Link_Annotation;
use SAF\Framework\Reflection\Annotation\Parser;
use SAF\Framework\Reflection\Annotation\Template;
use SAF\Framework\Reflection\Annotation\Template\Validator;
use SAF\Framework\Reflection\Link_Class;
use SAF\Framework\View;
use SAF\Framework\View\View_Exception;
use SAF\Framework\Widget\Validate\Property;
use SAF\Framework\Widget\Validate\Property\Property_Validate_Annotation;

/**
 * The object validator links validation processes to objects
 */
class Object_Validator implements Registerable
{

	//--------------------------------------------------------------------------------- $validator_on
	public $validator_on = false;

	//--------------------------------------------------------------------------------------- $report
	/**
	 * The validation report contains a detailed list of validate annotations and values
	 *
	 * @read_only
	 * @var Validator[]|Property\Property_Validate_Annotation[]
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

	//------------------------------------------------------------------------ afterMainControllerRun
	public function afterMainControllerRun()
	{
		$this->validator_on = false;
	}

	//----------------------------------------------------------------------- beforeMainControllerRun
	public function beforeMainControllerRun()
	{
		$this->validator_on = true;
	}

	//----------------------------------------------------------------------------------- beforeWrite
	/**
	 * The validator hook is called before each Data_Link::write() call to validate the object
	 * before writing it.
	 *
	 * @param  $object  object
	 * @param  $options Option[]
	 * @throws View_Exception
	 */
	public function beforeWrite($object, &$options)
	{
		if ($this->validator_on) {
			$exclude = [];
			$only    = [];
			foreach ($options as $option) {
				if ($option instanceof Exclude) {
					$exclude = array_merge($exclude, $option->properties);
				}
				if ($option instanceof Only) {
					$only = array_merge($only, $option->properties);
				}
				if ($option instanceof Skip) {
					$skip = true;
				}
			}
			if (!isset($skip) && !$this->validate($object, $only, $exclude)) {
				throw new View_Exception($this->notValidated($object, $only, $exclude));
			}
		}
	}

	//------------------------------------------------------------------------------------- getErrors
	/**
	 * @return Property\Property_Validate_Annotation[]
	 */
	public function getErrors()
	{
		$errors = [];
		foreach ($this->report as $annotation) {
			if ($annotation->valid === Validate::ERROR) {
				$errors[] = $annotation;
			}
		}
		return $errors;
	}

	//---------------------------------------------------------------------------------- notValidated
	/**
	 * @param $object  object
	 * @param $only    string[] only property names
	 * @param $exclude string[] excluded property names
	 * @return string
	 */
	private function notValidated($object, $only = [], $exclude = [])
	{
		$parameters = [
			$this,
			'exclude'                    => $exclude,
			'object'                     => $object,
			'only'                       => $only,
			Parameter::AS_WIDGET         => true,
			View\Html\Template::TEMPLATE => 'not_validated'
		];
		return View::run($parameters, [], [], get_class($object), 'validate');
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->afterMethod(
			[Data_Link::class, 'beforeWrite'], [$this, 'beforeWrite']
		);
		$register->aop->afterMethod(
			[Main::class, 'runController'], [$this, 'afterMainControllerRun']
		);
		$register->aop->beforeMethod(
			[Main::class, 'runController'], [$this, 'beforeMainControllerRun']
		);
		$register->setAnnotations(Parser::T_CLASS, [
			'validate'   => Template\Validate_Annotation::class
		]);
		$register->setAnnotations(Parser::T_PROPERTY, [
			'length'     => Property\Length_Annotation::class,
			'mandatory'  => Property\Mandatory_Annotation::class,
			'max_length' => Property\Max_Length_Annotation::class,
			'max_value'  => Property\Max_Value_Annotation::class,
			'min_length' => Property\Min_Length_Annotation::class,
			'min_value'  => Property\Min_Value_Annotation::class,
			'precision'  => Property\Precision_Annotation::class,
			'signed'     => Property\Signed_Annotation::class,
			'validate'   => Template\Validate_Annotation::class
		]);
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * @param $object             object
	 * @param $only_properties    string[] property names if we want to check those properties only
	 * @param $exclude_properties string[] property names if we don't want to check those properties
	 * @return boolean
	 */
	public function validate($object, $only_properties = [], $exclude_properties = [])
	{
		$this->report = [];
		$this->valid  = true;
		$exclude_properties = array_flip($exclude_properties);
		$only_properties    = array_flip($only_properties);
		$class = new Link_Class($object);
		$properties = $class->getAnnotation(Link_Annotation::ANNOTATION)->value
			? $class->getLinkProperties()
			: $class->accessProperties();

		// properties value validation
		foreach ($properties as $property) {
			if (
				(!$only_properties || isset($only_properties[$property->name]))
				&& !isset($exclude_properties[$property->name])
			) {
				$property_validator = new Property_Validator($property);
				$validated_property = $property_validator->validate($object);
				if (is_null($validated_property)) {
					return $this->valid = null;
				}
				else {
					$this->report = array_merge($this->report, $property_validator->report);
					$this->valid = $this->valid && $validated_property;
				}
			}
		}

		// object validation
		foreach ($class->getAnnotations() as $annotation) {
			if ($annotation instanceof Template\Validator) {
				$validated_annotation = $annotation->validate($object);
				if (isA($annotation, Property_Validate_Annotation::class)) {
					/** @var $annotation Template\Property_Validator|Property_Validate_Annotation */
					if ($annotation->valid === true)  $annotation->valid = Validate::INFORMATION;
					if ($annotation->valid === false) $annotation->valid = Validate::ERROR;
				}
				if (is_null($validated_annotation)) {
					return $this->valid = null;
				}
				else {
					if (!$validated_annotation) {
						$this->report[] = $annotation;
					}
					$this->valid = $this->valid && $validated_annotation;
				}
			}
		}

		return $this->valid;
	}

}
