<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

/**
 * For annotations that have options
 */
trait Options_Annotation
{

	//-------------------------------------------------------------------------------------- $options
	/**
	 * @var array
	 */
	public $options = [];

	//------------------------------------------------------------------------------ constructOptions
	/**
	 * A standard construction of options is for an annotation definition string like this :
	 * 'annotation_main_value option1, option2, option3'
	 *
	 * This initialises the $options property and remove the options from the value string
	 *
	 * @param $value string
	 */
	protected function constructOptions(&$value)
	{
		if (strpos($value, SP)) {
			list($value, $options) = explode(SP, $value, 2);
			$this->parseOptions(explode(',', $options));
		}
	}

	//---------------------------------------------------------------------------------------- option
	/**
	 * Get option value
	 *
	 * @param $option  string the name of the option
	 * @param $default mixed value to return if the option is not set
	 * @return mixed the value of the option
	 */
	public function option($option, $default = null)
	{
		return $this->options[$option] ?? $default;
	}

	//---------------------------------------------------------------------------------- parseOptions
	/**
	 * @param $options string[]
	 */
	protected function parseOptions(array $options)
	{
		foreach ($options as $option) if (strlen($option = trim($option))) {
			if (strpos($option, '=')) {
				list($key, $val) = explode('=', $option);
			}
			else {
				$key = $options;
				$val = true;
			}
			switch ($val) {
				case 'true':
					$val = true;
					break;
				case 'false':
					$val = false;
					break;
			}
			$this->options[$key] = $val;
		}
	}

}
