<?php
namespace ITRocks\Framework\Logger\Entry;

use ITRocks\Framework\Feature\Validate;
use ITRocks\Framework\Logger\Entry;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Reflection\Attribute\Class_\Set;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;

/**
 * Logger entry data
 */
#[Store]
#[Set('Logs_Data')]
class Data implements Validate\Except
{
	use Component;

	//------------------------------------------------------------------------------------ $arguments
	/**
	 * @max_length 65000
	 * @var string
	 */
	public string $arguments = '';

	//---------------------------------------------------------------------------------------- $entry
	/**
	 * @composite
	 * @link Object
	 * @var Entry
	 */
	public Entry $entry;

	//---------------------------------------------------------------------------------------- $files
	/**
	 * @max_length 65000
	 * @var string
	 */
	public string $files = '';

	//----------------------------------------------------------------------------------------- $form
	/**
	 * @max_length 1000000
	 * @var string
	 */
	public string $form = '';

	//--------------------------------------------------------------------------- $request_identifier
	/**
	 * @var string
	 */
	public string $request_identifier = '';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $arguments          array|null
	 * @param $form               array|null
	 * @param $files              array[]|null
	 * @param $request_identifier string|null
	 */
	public function __construct(
		array $arguments = null, array $form = null, array $files = null,
		string $request_identifier = null
	) {
		if (isset($arguments) && ($this->arguments === '')) {
			if (isset($arguments['as_widget'])) {
				unset($arguments['as_widget']);
			}
			if (isset($arguments['user_password'])) {
				$arguments['user_password'] = 'XXXX';
			}
			$this->arguments = $this->serialize($arguments);
		}
		if (isset($files) && ($this->files === '')) {
			$this->files = $this->serialize($files);
		}
		if (isset($form) && ($this->form === '')) {
			if (isset($form['password'])) {
				$form['password'] = 'XXXX';
			}
			if (isset($form['password2'])) {
				$form['password2'] = 'XXXX';
			}
			if (isset($form['user_password'])) {
				$form['user_password'] = 'XXXX';
			}
			$this->form = $this->serialize($form);
		}
		if (isset($request_identifier)) {
			$this->request_identifier = $request_identifier;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string
	 */
	public function __toString() : string
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return jsonEncode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}

	//----------------------------------------------------------------------------------- rawFormData
	/**
	 * @param $data mixed
	 */
	public function rawFormData(mixed $data) : void
	{
		$this->form = $this->serialize(['RAW' => $data]);
	}

	//------------------------------------------------------------------------------------- serialize
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $value array
	 * @return string
	 */
	private function serialize(array $value) : string
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		$json = jsonEncode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		return ($json === '[]') ? '' : $json;
	}

}
