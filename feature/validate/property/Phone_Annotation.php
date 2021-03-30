<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Phone\Phone_Format;
use ITRocks\Framework\Phone\Phone_Number_Exception;
use ITRocks\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

class Phone_Annotation extends Boolean_Annotation implements Property_Context_Annotation
{
	use Annotation;

	//-------------------------------------------------------------------------------- $error_message
	/**
	 * @var string
	 */
	private $error_message;

	//--------------------------------------------------------------------------------- $phone_format
	/**
	 * @var Phone_Format|null
	 */
	public $phone_format;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct($value, Reflection_Property $property)
	{
		parent::__construct($value);
		if ($this->value) {
			$this->phone_format = Phone_Format::get();
			$this->property     = $property;
		}
	}

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * @return string
	 */
	public function reportMessage(): string
	{
		return $this->error_message ?? 'This phone number is not correct';
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * @param $object object
	 * @return boolean
	 */
	public function validate($object): bool
	{
		if (!$this->value) {
			return true;
		}
		try {
			return $this->phone_format->isValid(
				$object->{$this->property->name},
				$this->phone_format->getCountryCode($object)
			);
		}
		catch (Phone_Number_Exception $exception) {
			$this->error_message = $exception->getErrorType();
		}
		return false;
	}

}
