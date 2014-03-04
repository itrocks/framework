<?php
namespace SAF\Framework;

/**
 * A reflection property value is a reflection property enriched with it's display label and a value
 */
class Reflection_Property_Value extends Reflection_Property
{

	//-------------------------------------------------------------------------------------- $display
	/**
	 * What will be displayed by the display() function
	 *
	 * Keep this null to calculate automatically, fill this only to force display
	 *
	 * @var string|null
	 */
	public $display = null;

	//---------------------------------------------------------------------------------- $final_value
	/**
	 * If set to true, $object contains the final value instead of the object containing
	 * the valued property
	 *
	 * @var boolean
	 */
	private $final_value;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * The object ($final_value = false) or the value ($final_value = true) of the property
	 *
	 * @var object
	 */
	private $object;

	//----------------------------------------------------------------------------------------- $user
	/**
	 * Set this to true if the property is for an user use (ie for display into a template)
	 *
	 * @var boolean
	 */
	private $user;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a reflection property with value
	 *
	 * @example
	 * $pv = new Reflection_Property_Value('Class_Name', 'property_name', $object);
	 *
	 * @param $class_name    string
	 * @param $property_name string
	 * @param $object        object|mixed the object containing the value, or the value itself
	 *        (in this case set $final_value tu true)
	 * @param $final_value   boolean set to true if $object is a final value instead of the object
	 *        containing the valued property
	 * @param $user          boolean set to true if the property value will be used into an user
	 *        display (ie an HTML template)
	 */
	public function __construct(
		$class_name, $property_name, $object = null, $final_value = false, $user = false
	) {
		parent::__construct($class_name, $property_name);
		$this->final_value = $final_value;
		$this->path = $property_name;
		if (!isset($this->object)) {
			$this->object = $object;
		}
		else {
echo 'DEAD CODE ? object is set for ' . $class_name . '::' . $property_name . '<br>';
		}
		$this->user = $user;
	}

	//----------------------------------------------------------------------------------------- __get
	/**
	 * Sets additional properties to matching Reflection_Property
	 * (common for all instances of this property)
	 *
	 * @param $key string
	 * @return mixed
	 */
	public function __get($key)
	{
		$property = new Reflection_Property($this->class, $this->name);
		$value = isset($property->$key) ? $property->$key : null;
echo 'Reflection_Property_Value::__get(' . $key . ') = ' . $value . ' MAY CRASH !<br>';
		return $value;
	}

	//----------------------------------------------------------------------------------------- __set
	/**
	 * Sets additional properties to matching Reflection_Property
	 * (common for all instances of this property)
	 *
	 * @param $key   string
	 * @param $value mixed
	 */
	public function __set($key, $value)
	{
echo 'Reflection_Property_Value::__set(' . $key . ') = ' . $value . ' MAY CRASH !<br>';
		$property = (new Reflection_Property($this->class, $this->name));
		$property->$key = $value;
	}

	//--------------------------------------------------------------------------------------- display
	/**
	 * @return string
	 */
	public function display()
	{
		return $this->display
			? $this->display
			: Names::propertyToDisplay($this->path ? $this->path : $this->name);
	}

	//---------------------------------------------------------------------------------------- format
	/**
	 * @return mixed
	 */
	public function format()
	{
		return (new Reflection_Property_View($this))->getFormattedValue($this->object);
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Gets the object containing the value (null if the value was set as a value)
	 *
	 * @return object|null
	 */
	public function getObject()
	{
		return $this->object;
	}

	//----------------------------------------------------------------------------------------- value
	/**
	 * @param $value object
	 * @return mixed
	 */
	public function value($value = null)
	{
		if ($value !== null) {
			if ($this->final_value) {
				$this->object = $value;
			}
			else {
				$this->setValue($this->object, $value);
			}
		}
		if ($this->user && !$this->final_value) {
			$user_getter = $this->getAnnotation('user_getter')->value;
			if ($user_getter) {
				$callable = new Contextual_Callable($user_getter, $this->object);
				return $callable->call();
			}
		}
		return $this->final_value ? $this->object : $this->getValue($this->object);
	}

}
