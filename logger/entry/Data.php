<?php
namespace ITRocks\Framework\Logger\Entry;

use ITRocks\Framework\Logger\Entry;
use ITRocks\Framework\Mapper\Component;

/**
 * Logger entry data
 *
 * @business
 * @set Logs_Data
 */
class Data
{
	use Component;

	//------------------------------------------------------------------------------------ $arguments
	/**
	 * @max_length 65000
	 * @var string
	 */
	public $arguments;

	//---------------------------------------------------------------------------------------- $entry
	/**
	 * @composite
	 * @link Object
	 * @var Entry
	 */
	public $entry;

	//---------------------------------------------------------------------------------------- $files
	/**
	 * @max_length 65000
	 * @var string
	 */
	public $files;

	//----------------------------------------------------------------------------------------- $form
	/**
	 * @max_length 65000
	 * @var string
	 */
	public $form;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $arguments array
	 * @param $form      array
	 * @param $files     array[]
	 */
	public function __construct(array $arguments = null, array $form = null, array $files = null)
	{
		if (isset($arguments) && !isset($this->arguments)) {
			if (isset($arguments['as_widget'])) {
				unset($arguments['as_widget']);
			}
			$this->arguments = $this->serialize($arguments);
		}
		if (isset($files) && !isset($this->files)) {
			$this->files = $this->serialize($files);
		}
		if (isset($form) && !isset($this->form)) {
			if (isset($form['password'])) {
				$form['password'] = '***';
			}
			$this->form = $this->serialize($form);
		}
	}

	//------------------------------------------------------------------------------------- serialize
	/**
	 * @param $value array
	 * @return string
	 */
	private function serialize(array $value)
	{
		$value = json_encode($value);
		return ($value === '[]') ? '' : $value;
	}

}
