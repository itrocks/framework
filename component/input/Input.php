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
	public bool $is_multiple = false;

	//---------------------------------------------------------------------------------------- $label
	/**
	 * The label of the input
	 *
	 * @var string
	 */
	public string $label = '';

	//----------------------------------------------------------------------------------------- $name
	/**
	 * Input name
	 *
	 * @var string
	 */
	public string $name = '';

	//----------------------------------------------------------------------------------------- $type
	/**
	 * The type of the input
	 *
	 * @var string
	 */
	public string $type = '';

	//---------------------------------------------------------------------------------------- $value
	/**
	 * The default value of the input
	 *
	 * @var string
	 */
	public string $value = '';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name        string|null Name of the input
	 * @param $label       string|null Label of the input
	 * @param $type        string|null Type of the input
	 * @param $is_multiple boolean|null If the input is multi-rows
	 * @param $value       string|null
	 */
	public function __construct(
		string $name = null, string $label = null, string $type = null, bool $is_multiple = null,
		string $value = null
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
	 * @return static[]
	 */
	public static function newCollection(array $inputs_arrays) : array
	{
		$inputs = [];
		foreach ($inputs_arrays as $array) {
			$inputs[] = new static(...$array);
		}
		return $inputs;
	}

}
