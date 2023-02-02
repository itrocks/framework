<?php
namespace ITRocks\Framework\Plugin;

use ITRocks\Framework\AOP\Weaver\IWeaver;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Reflection\Attribute\Property\Setter;

/**
 * Plugin register structure
 */
class Register
{

	//------------------------------------------------------------------------------------------ $aop
	/**
	 * @var IWeaver
	 */
	public IWeaver $aop;

	//-------------------------------------------------------------------------------- $configuration
	/**
	 * @impacts get
	 */
	#[Getter('getConfiguration'), Setter('setConfiguration')]
	public array|string $configuration;

	//------------------------------------------------------------------------------------------ $get
	/**
	 * @var boolean
	 */
	private bool $get;

	//---------------------------------------------------------------------------------------- $level
	/**
	 * @values core, highest, higher, high, normal, low, lower, lowest
	 * @var string
	 */
	public string $level;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array|string|null
	 * @param $aop           IWeaver|null
	 */
	public function __construct(mixed $configuration = null, IWeaver $aop = null)
	{
		if (isset($aop))           $this->aop           = $aop;
		if (isset($configuration)) $this->configuration = $configuration;
	}

	//------------------------------------------------------------------------------ getConfiguration
	/**
	 * @return array|string
	 */
	protected function getConfiguration() : array|string
	{
		if ($this->get) {
			return $this->configuration;
		}
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
	public function setAnnotation(string $context, string $annotation_name, string $annotation_class)
		: void
	{
		Additional_Annotations::setAnnotation($context, $annotation_name, $annotation_class);
	}

	//-------------------------------------------------------------------------------- setAnnotations
	/**
	 * Defines multiple annotations classes
	 * A very bit faster than multiple calls to setAnnotation()
	 *
	 * @param $context             string Parser::T_CLASS, Parser::T_METHOD, Parser::T_PROPERTY
	 * @param $annotations_classes string[] key is the annotation name, value is the annotation class
	 */
	public function setAnnotations(string $context, array $annotations_classes) : void
	{
		Additional_Annotations::setAnnotations($context, $annotations_classes);
	}

	//------------------------------------------------------------------------------ setConfiguration
	/**
	 * @param $configuration array|string
	 */
	protected function setConfiguration(array|string $configuration) : void
	{
		$this->configuration = $configuration;
		$this->get           = false;
	}

}
