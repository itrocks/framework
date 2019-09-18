<?php
namespace ITRocks\Framework\Logger\Entry;

use ITRocks\Framework\Feature\Validate;
use ITRocks\Framework\Logger\Entry;
use ITRocks\Framework\Mapper\Component;

/**
 * Logger entry data
 *
 * @business
 * @set Logs_Data
 */
class Data implements Validate\Exception
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
	 * @max_length 1000000
	 * @var string
	 */
	public $form;

	//--------------------------------------------------------------------------- $request_identifier
	/**
	 * @var string
	 */
	public $request_identifier;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $arguments          array
	 * @param $form               array
	 * @param $files              array[]
	 * @param $request_identifier string
	 */
	public function __construct(
		array $arguments = null, array $form = null, array $files = null, $request_identifier = null
	) {
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
			if (isset($form['password2'])) {
				$form['password2'] = '***';
			}
			$this->form = $this->serialize($form);
		}
		if (isset($request_identifier)) {
			$this->request_identifier = $request_identifier;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}

	//------------------------------------------------------------------------------------- serialize
	/**
	 * @param $value array
	 * @return string
	 */
	private function serialize(array $value)
	{
		$json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		return ($json === '[]') ? '' : $json;
	}

}
