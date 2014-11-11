<?php
namespace SAF\Framework\View\Html\Builder;

use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\View\Html\Template;

/**
 * Abstract class for html property builder
 */
abstract class Property
{

	//------------------------------------------------------------------------------ DONT_BUILD_VALUE
	/**
	 * This unique and arbitrary constant is returned by buildValue() when is not defined into a
	 * child class
	 */
	const DONT_BUILD_VALUE = 'çeàfdsnzOFfjapzjfsdgrT2è§édsvp-f';

	//----------------------------------------------------------------------------------- $parameters
	/**
	 * Additional parameters for html template or as options.
	 * The most common is ['edit' => ' edit'].
	 *
	 * @var array
	 */
	public $parameters = [];

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	protected $property;

	//------------------------------------------------------------------------------------- $template
	/**
	 * @var Template
	 */
	protected $template;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var mixed
	 */
	protected $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property Reflection_Property
	 * @param $value    mixed
	 * @param $template Template
	 */
	public function __construct(Reflection_Property $property, $value, Template $template = null)
	{
		$this->property = $property;
		$this->value    = $value;
		$this->template = $template;
	}

	//------------------------------------------------------------------------------------- buildHtml
	/**
	 * @return string
	 */
	public abstract function buildHtml();

	//------------------------------------------------------------------------------------ buildValue
	/**
	 * @param $object        object
	 * @param $null_if_empty boolean
	 * @return mixed
	 */
	public function buildValue(
		/* @noinspection PhpUnusedParameterInspection */ $object, $null_if_empty
	) {
		return self::DONT_BUILD_VALUE;
	}

	//----------------------------------------------------------------------------------- setTemplate
	/**
	 * @param $template Template
	 */
	public function setTemplate(Template $template)
	{
		$this->template = $template;
	}

}
