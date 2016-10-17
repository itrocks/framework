<?php
namespace SAF\Framework\Plugin;

use SAF\Framework\AOP\Weaver\IWeaver;
use SAF\Framework\Reflection\Annotation\Parser;
use SAF\Framework\Tools\Names;

/**
 * Plugin register structure
 */
class Register
{

	//------------------------------------------------------------------------------------------ $aop
	/**
	 * @var IWeaver
	 */
	public $aop;

	//-------------------------------------------------------------------------------- $configuration
	/**
	 * @getter getConfiguration
	 * @setter setConfiguration
	 * @var array|string
	 */
	public $configuration;

	//------------------------------------------------------------------------------------------ $get
	/**
	 * @var boolean
	 */
	private $get;

	//---------------------------------------------------------------------------------------- $level
	/**
	 * @values core, highest, higher, high, normal, low, lower, lowest
	 * @var string
	 */
	public $level;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array|string
	 * @param $aop           IWeaver
	 */
	public function __construct($configuration = null, IWeaver $aop = null)
	{
		if (isset($aop))           $this->aop           = $aop;
		if (isset($configuration)) $this->configuration = $configuration;
	}

	//------------------------------------------------------------------------------ getConfiguration
	/** @noinspection PhpUnusedPrivateMethodInspection @getter */
	/**
	 * @return array|string
	 */
	private function getConfiguration()
	{
		if (!$this->get) {
			if (!is_array($this->configuration)) {
				$this->configuration = isset($this->configuration) ? [$this->configuration => true] : [];
			}
			foreach ($this->configuration as $key => $value) {
				if (is_numeric($key) && is_string($value)) {
					unset($this->configuration[$key]);
					$this->configuration[$value] = true;
				}
			}
			$this->get = true;
		}
		return $this->configuration;
	}

	//--------------------------------------------------------------------------------- setAnnotation
	/**
	 * Defines an annotation class, linked to an annotation
	 *
	 * @param $context          string Parser::T_CLASS, Parser::T_METHOD, Parser::T_PROPERTY
	 * @param $annotation_name  string
	 * @param $annotation_class string
	 */
	public function setAnnotation($context, $annotation_name, $annotation_class)
	{
		// instantiates Parser, in order to call its __destruct() method at the script end
		if (!isset($GLOBALS['parser'])) {
			$GLOBALS['parser'] = new Parser();
		}
		// add annotation
		$namespace = 'SAF\Framework\Reflection\Annotation' . BS . $context;
		$class_name = Names::propertyToClass($annotation_name) . '_Annotation';
		Parser::$additional_annotations[$namespace . BS . $class_name] = $annotation_class;
	}

	//-------------------------------------------------------------------------------- setAnnotations
	/**
	 * Defines multiple annotations classes
	 * A very little bit faster than multiple calls to setAnnotation()
	 *
	 * @param $context             string Parser::T_CLASS, Parser::T_METHOD, Parser::T_VARIABLE
	 * @param $annotations_classes string[] key is the annotation name, value is the annotation class
	 */
	public function setAnnotations($context, $annotations_classes)
	{
		// instantiates Parser, in order to call its __destruct() method at the script end
		if (!isset($GLOBALS['parser'])) {
			$GLOBALS['parser'] = new Parser();
		}
		// add annotation
		$namespace = 'SAF\Framework\Reflection\Annotation' . BS . $context;
		foreach ($annotations_classes as $annotation_name => $annotation_class) {
			$class_name = Names::propertyToClass($annotation_name) . '_Annotation';
			Parser::$additional_annotations[$namespace . BS . $class_name] = $annotation_class;
		}
	}

	//------------------------------------------------------------------------------ setConfiguration
	/** @noinspection PhpUnusedPrivateMethodInspection @setter */
	/**
	 * @param $configuration array|string
	 */
	private function setConfiguration($configuration)
	{
		$this->configuration = $configuration;
		$this->get = false;
	}

}
