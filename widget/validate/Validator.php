<?php
namespace ITRocks\Framework\Widget\Validate;

use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Dao\Option\Exclude;
use ITRocks\Framework\Dao\Option\Link_Class_Only;
use ITRocks\Framework\Dao\Option\Only;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Class_\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Parser;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;
use ITRocks\Framework\View\View_Exception;
use ITRocks\Framework\Widget\Validate\Annotation\Warning_Annotation;
use ITRocks\Framework\Widget\Validate\Property;
use ITRocks\Framework\Widget\Write\Write_Controller;

/**
 * The object validator links validation processes to objects
 */
class Validator implements Registerable
{

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

	//----------------------------------------------------------------------- afterWriteControllerRun
	public function afterWriteControllerRun()
	{
		$this->warning_on = false;
	}

	//--------------------------------------------------------------------- afterWriteControllerWrite
	/**
	 * @param $write_objects array
	 * @throws View_Exception
	 */
	public function afterWriteControllerWrite($write_objects)
	{
		if ($this->warningEnabled() && $this->getWarnings()) {
			throw new View_Exception($this->notValidated(reset($write_objects)));
		}
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
	public function beforeWrite($object, array &$options)
	{
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
	 * Disable validator and return previous status
	 *
	 * @return boolean true if was enabled, false if was disabled
	 */
	public function enable()
	{
		$validator_on       = $this->validator_on;
		$this->validator_on = true;
		return $validator_on;
	}

	//---------------------------------------------------------------------- beforeWriteControllerRun
	/**
	 * @param $parameters Parameters
	 */
	public function beforeWriteControllerRun(Parameters $parameters)
	{
		if (!$parameters->getRawParameter('confirm')) {
			$this->warning_on = true;
		}
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
		$register->aop->afterMethod(
			[Write_Controller::class, 'run'], [$this, 'afterWriteControllerRun']
		);
		$register->aop->beforeMethod(
			[Write_Controller::class, 'run'], [$this, 'beforeWriteControllerRun']
		);
		$register->aop->afterMethod(
			[Write_Controller::class, 'write'], [$this, 'afterWriteControllerWrite']
		);
		$register->setAnnotations(Parser::T_CLASS, [
			'validate'   => Class_\Validate_Annotation::class,
			'warning'    => Class_\Warning_Annotation::class,
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
			'validate'   => Property\Validate_Annotation::class,
			// 'var'        => Property\Var_Annotation::class,
			'warning'    => Property\Warning_Annotation::class,
		]);
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * @param $object             object
	 * @param $only_properties    string[] property names if we want to check those properties only
	 * @param $exclude_properties string[] property names if we don't want to check those properties
	 * @return string|null|true @values Result::const
	 */
	public function validate($object, array $only_properties = [], array $exclude_properties = [])
	{
		$class      = new Link_Class($object);
		$properties = Replaces_Annotations::removeReplacedProperties(
			$class->getAnnotation(Link_Annotation::ANNOTATION)->value
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
	 * @param $annotation Annotation
	 * @return string|null|true @values Result::const
	 */
	protected function validateAnnotation($object, $annotation)
	{
		$annotation->object = $object;
		$annotation->valid = $annotation->validate($object);
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
	 * @param $annotations Annotation[]
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

	//-------------------------------------------------------------------------------- validateObject
	/**
	 * @param $object          object
	 * @param Reflection_Class $class
	 * @return string|null|true @values Result::const
	 */
	protected function validateObject($object, Reflection_Class $class)
	{
		return $this->validateAnnotations($object, $class->getAnnotations());
	}

	//---------------------------------------------------------------------------- validateProperties
	/**
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
			if (
				!$property->isStatic()
				&& (!$only_properties || isset($only_properties[$property->name]))
				&& !isset($exclude_properties[$property->name])
				&& (isset($object->{$property->name}) || !Link_Annotation::of($property)->value)
			) {
				$result = Result::andResult(
					$result, $this->validateAnnotations($object, $property->getAnnotations())
				);
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
