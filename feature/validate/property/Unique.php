<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Tools\Mutex;

/**
 * The unique annotation validator
 */
class Unique extends Boolean_Annotation implements Property_Context_Annotation
{
	use Annotation;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    bool|null|string
	 * @param $property Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct(bool|null|string $value, Reflection_Property $property)
	{
		parent::__construct($value);
		$this->property = $property;
	}

	//--------------------------------------------------------------------------------- reportMessage
	public function reportMessage() : string
	{
		return 'This value already exist';
	}

	//-------------------------------------------------------------------------------------- validate
	public function validate(object $object) : bool
	{
		if (!$this->value) {
			return true;
		}
		$property_name = $this->property->name;
		if (!strlen($object->$property_name)) {
			return true;
		}
		$search = [$property_name => $object->$property_name];
		if (Dao::getObjectIdentifier($object)) {
			$search[] = Func::notEqual($object);
		}

		// ensure that the mutual exclusion runs until the end of the script execution
		global $persistent_mutex;
		if (!$persistent_mutex) {
			$persistent_mutex = [];
		}
		$mutex_key = strUri(get_class($object)) . '.#Unique';
		if (!isset($persistent_mutex[$mutex_key])) {
			$mutex = (new Mutex($mutex_key));
			$mutex->lock();
			$persistent_mutex[$mutex_key] = $mutex;
		}
		return !Dao::searchOne($search, get_class($object));
	}

}
