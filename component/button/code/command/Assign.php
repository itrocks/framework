<?php
namespace ITRocks\Framework\Component\Button\Code\Command;

use ITRocks\Framework\Component\Button\Code\Command;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Tools\Names;

/**
 * Assign command : assigns the property-name or constant value on right to the property-name on
 * left
 *
 * @example status = 'waiting for repair'
 * @example status = new_status
 */
class Assign implements Command
{

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @var string
	 */
	private string $property_name;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var string
	 */
	private string $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property_name string
	 * @param $value         string
	 */
	public function __construct(string $property_name, string $value)
	{
		$this->property_name = $property_name;
		$this->value         = $value;
	}

	//--------------------------------------------------------------------------------------- execute
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @return mixed
	 */
	public function execute(object $object) : mixed
	{
		if ($object) {
			// right operand is a string constant
			if (
				(str_starts_with($this->value, Q) && str_ends_with($this->value, Q))
				|| (str_starts_with($this->value, DQ) && str_ends_with($this->value, DQ))
			) {
				$value = substr($this->value, 1, -1);
			}
			// right operand is a numeric constant
			elseif (is_numeric($this->value)) {
				$value = $this->value;
			}
			// right operand is a property path : get its value
			else {
				/** @noinspection PhpUnhandledExceptionInspection property must be valid */
				$property_value = new Reflection_Property_Value(
					$object, Names::displayToProperty(Loc::rtr($this->value)), $object
				);
				$value = $property_value->value();
			}
			// left operand is a property path
			/** @noinspection PhpUnhandledExceptionInspection property must be valid */
			$property_value = new Reflection_Property_Value(
				$object, Names::displayToProperty(Loc::rtr($this->property_name)), $object
			);
			// translate value
			if (Values::of($property_value)?->values) {
				$value = Names::displayToProperty(Loc::rtr($value));
			}
			// set value
			$property_value->setValue($object, $value);
			return $value;
		}
		return false;
	}

}
