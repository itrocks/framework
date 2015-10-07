<?php
namespace SAF\Framework;

/**
 * Every _History class should extend this
 *
 * You must @override object @var Class_Name into the final class
 * Or create another property with @replaces object
 */
abstract class History
{

	//--------------------------------------------------------------------------------------- $object
	/**
	 * You must @override object @var Class_Name into the final class
	 * Or create another property with @replaces object
	 *
	 * @link Object
	 * @var object
	 */
	public $object;

	//------------------------------------------------------------------------------------ $new_value
	/**
	 * @var string|mixed
	 */
	public $new_value;

	//------------------------------------------------------------------------------------ $old_value
	/**
	 * @var string|mixed
	 */
	public $old_value;

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @var string
	 */
	public $property_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $object        object
	 * @param $property_name string
	 * @param $old_value     mixed
	 * @param $new_value     mixed
	 */
	public function __construct(
		$object = null, $property_name = null, $old_value = null, $new_value = null
	) {
		if (isset($object) && isset($property_name)) {
			$this->object = $object;
			$this->property_name = $property_name;
			$this->old_value = (is_object($old_value) && Dao::getObjectIdentifier($old_value))
				? Dao::getObjectIdentifier($old_value)
				: strval($old_value);
			$this->new_value = (is_object($new_value) && Dao::getObjectIdentifier($old_value))
				? Dao::getObjectIdentifier($new_value)
				: strval($new_value);
		}
	}

}
