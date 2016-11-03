<?php
namespace ITRocks\Framework\Widget\Button\Code\Command;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Widget\Button\Code\Command;

/**
 * Equals command : returns true if the two constant or property-name operands have the same value
 *
 * @example status = "waiting for repair"
 * @example status = old status
 */
class Equals implements Command
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
	 * @param $object object
	 * @return mixed
	 */
	public function execute($object)
	{
		if ($object) {
			// right operand is a string constant
			if (
				((substr($this->value, 0 ,1) === Q) && (substr($this->value, -1) === Q))
				|| ((substr($this->value, 0 ,1) === DQ) && (substr($this->value, -1) === DQ))
			) {
				$value = substr($this->value, 1, -1);
			}
			// right operand is a numeric constant
			elseif (is_numeric($this->value) || is_bool($this->value)) {
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
			// compare values
			return $value == $property_value->getValue($object);
		}
		return false;
	}

}
