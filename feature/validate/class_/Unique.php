<?php
namespace ITRocks\Framework\Feature\Validate\Class_;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Feature\Validate\Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Tools\Mutex;

class Unique extends Class_\Unique
{
	use Annotation;

	//--------------------------------------------------------------------------------- reportMessage
	public function reportMessage() : string
	{
		return 'This value already exist';
	}

	//-------------------------------------------------------------------------------------- validate
	public function validate(object $object) : ?bool
	{
		if (!$this->values) {
			return true;
		}
		$search = [];
		foreach ($this->values as $property_name) {
			$search[$property_name] = $object->$property_name;
		}
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
