<?php
namespace ITRocks\Framework\Widget;

/**
 * An input object is used into HMI forms to enable the user to type in data
 */
class Input
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * Input name
	 *
	 * @var string
	 */
	public $name;

	//---------------------------------------------------------------------------------------- $label
	/**
	 * The label of the input
	 *
	 * @var string
	 */
	public $label;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * The type of the input
	 *
	 * @var string
	 */
	public $type;

	//----------------------------------------------------------------------------------- $isMultiple
	/**
	 * If the input is multi-rows
	 *
	 * @var boolean
	 */
	public $isMultiple;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * The default value of the input
	 *
	 * @var string
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name string Name of the input
	 * @param $label string Label of the input
	 * @param $type string Type of the input
	 * @param $isMultiple boolean If the input is multi-rows
	 * @param $value string
	 */
	public function __construct($name = null, $label = null, $type = null, $isMultiple = false, $value = null)
	{
		if ($name != null) $this->name              = $name;
		if ($label    != null) $this->label         = $label;
		if ($type != null) $this->type              = $type;
		if (is_bool($isMultiple)) $this->isMultiple = $isMultiple;
		if ($value != null) $this->value            = $value;
	}

	//--------------------------------------------------------------------------------- newCollection
	/**
	 * Builds a new collection of inputs
	 *
	 * @param $inputs_arrays array[] each array is a set of arguments for Input's constructor
	 * @return Input[]
	 */
	public static function newCollection($inputs_arrays)
	{
		$inputs = [];
		foreach ($inputs_arrays as $array) {
			switch (count($array)) {
				case 5: $inputs[] = new Input($array[0], $array[1], $array[2], $array[3], $array[4]); break;
				case 4: $inputs[] = new Input($array[0], $array[1], $array[2], $array[3]); break;
				case 3: $inputs[] = new Input($array[0], $array[1], $array[2]); break;
				case 2: $inputs[] = new Input($array[0], $array[1]); break;
				case 1: $inputs[] = new Input($array[0]); break;
			}
		}
		return $inputs;
	}

}
