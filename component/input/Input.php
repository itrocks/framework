<?php
namespace ITRocks\Framework\Component;

/**
 * An input object is used into HMI forms to enable the user to type in data
 */
class Input
{

	//---------------------------------------------------------------------------------- $is_multiple
	/**
	 * If the input is multi-rows
	 *
	 * @var boolean
	 */
	public $is_multiple;

	//---------------------------------------------------------------------------------------- $label
	/**
	 * The label of the input
	 *
	 * @var string
	 */
	public $label;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * Input name
	 *
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * The type of the input
	 *
	 * @var string
	 */
	public $type;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * The default value of the input
	 *
	 * @var string
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name        string Name of the input
	 * @param $label       string Label of the input
	 * @param $type        string Type of the input
	 * @param $is_multiple boolean If the input is multi-rows
	 * @param $value       string
	 */
	public function __construct(
		$name = null, $label = null, $type = null, $is_multiple = false, $value = null
	) {
		if (isset($is_multiple)) $this->is_multiple = $is_multiple;
		if (isset($label))       $this->label       = $label;
		if (isset($name))        $this->name        = $name;
		if (isset($type))        $this->type        = $type;
		if (isset($value))       $this->value       = $value;
	}

	//--------------------------------------------------------------------------------- newCollection
	/**
	 * Builds a new collection of inputs
	 *
	 * @param $inputs_arrays array[] each array is a set of arguments for Input's constructor
	 * @return Input[]
	 */
	public static function newCollection(array $inputs_arrays)
	{
		$inputs = [];
		foreach ($inputs_arrays as $array) {
			switch (count($array)) {
				case 1: $inputs[] = new Input($array[0]); break;
				case 2: $inputs[] = new Input($array[0], $array[1]); break;
				case 3: $inputs[] = new Input($array[0], $array[1], $array[2]); break;
				case 4: $inputs[] = new Input($array[0], $array[1], $array[2], $array[3]); break;
				case 5: $inputs[] = new Input($array[0], $array[1], $array[2], $array[3], $array[4]); break;
			}
		}
		return $inputs;
	}

}
