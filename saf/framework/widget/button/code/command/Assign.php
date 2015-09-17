<?php
namespace SAF\Framework\Widget\Button\Code\Command;

use SAF\Framework\Controller\Uri;
use SAF\Framework\Locale\Loc;
use SAF\Framework\Reflection\Reflection_Property_Value;
use SAF\Framework\Tools\Names;
use SAF\Framework\Widget\Button\Code\Command;

/**
 * Assign command
 */
class Assign implements Command
{

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @var string
	 */
	private $property_name;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var string
	 */
	private $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property_name string
	 * @param $value         string
	 */
	public function __construct($property_name, $value)
	{
		$this->property_name = $property_name;
		$this->value = $value;
	}

	//--------------------------------------------------------------------------------------- execute
	/**
	 * @param $uri Uri
	 */
	public function execute(Uri $uri)
	{
		$object = $uri->parameters->getMainObject();
		if ($object) {
			// right operand is a string constant
			if ((substr($this->value, 0 ,1) === Q) && (substr($this->value, -1) === Q)) {
				$value = substr($this->value, 1, -1);
			}
			// right operand is a numeric constant
			elseif (is_numeric($this->value)) {
				$value = $this->value;
			}
			// right operand is a property path : get its value
			else {
				$property_value = new Reflection_Property_Value(
					get_class($object), Names::displayToProperty(Loc::rtr($this->value)), $object
				);
				$value = $property_value->value();
			}
			// left operand is a property path
			$property_value = new Reflection_Property_Value(
				get_class($object), Names::displayToProperty(Loc::rtr($this->property_name)), $object
			);
			// translate value
			$values = $property_value->getListAnnotation('values')->values();
			if ($values) {
				$value = Names::displayToProperty(Loc::rtr($value));
			}
			// set value
			$property_value->setValue($object, $value);
		}
	}

}
