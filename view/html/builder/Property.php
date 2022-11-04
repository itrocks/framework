<?php
namespace ITRocks\Framework\View\Html\Builder;

use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Mapper\Built_Object;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\View\Html\Template;

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

	//-------------------------------------------------------------------------------- $built_objects
	/**
	 * Built objects to add to Object_Builder_Array's built_objects after calling buildValue()
	 *
	 * @var Built_Object[]
	 */
	public array $built_objects = [];

	//----------------------------------------------------------------------------------- $parameters
	/**
	 * Additional parameters for html template or as options.
	 * The most common is ['edit' => ' edit'].
	 *
	 * @var array
	 */
	public array $parameters = [Parameter::IS_INCLUDED => true];

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	public Reflection_Property $property;

	//------------------------------------------------------------------------------------- $template
	/**
	 * @var ?Template
	 */
	public ?Template $template;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var mixed
	 */
	public mixed $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property Reflection_Property
	 * @param $value    mixed
	 * @param $template Template|null
	 */
	public function __construct(
		Reflection_Property $property, mixed $value, Template $template = null
	) {
		$this->property = $property;
		$this->template = $template;
		$this->value    = $value;
	}

	//------------------------------------------------------------------------------------- buildHtml
	/**
	 * @return string
	 */
	public abstract function buildHtml() : string;

	//------------------------------------------------------------------------------------ buildValue
	/**
	 * @param $object        object
	 * @param $null_if_empty boolean
	 * @return string
	 */
	public function buildValue(object $object, bool $null_if_empty) : mixed
	{
		return self::DONT_BUILD_VALUE;
	}

	//----------------------------------------------------------------------------------- setTemplate
	/**
	 * @param $template Template
	 */
	public function setTemplate(Template $template) : void
	{
		$this->template = $template;
	}

}
