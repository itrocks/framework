<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Types_Annotation;

/**
 * Use a specific HTML builder class to build output / edit / object for write for the property
 */
class Widget_Annotation extends Annotation
{
	use Types_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'widget';

	//-------------------------------------------------------------------------------------- $options
	/**
	 * @var array
	 */
	protected $options = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 */
	public function __construct($value)
	{
		if (strpos($value, SP)) {
			list($value, $options) = explode(SP, $value, 2);
			$this->parseOptions(explode(',', $options));
		}
		parent::__construct($value);
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
