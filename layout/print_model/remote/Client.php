<?php
namespace ITRocks\Framework\Layout\Print_Model\Remote;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\List_;
use ITRocks\Framework\Http\Http;
use ITRocks\Framework\Layout\Print_Model;
use ITRocks\Framework\Layout\Print_Model\Status;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Json;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\View;

/**
 * Allow to access (in preview mode) and download remote print models
 *
 * This feature needs to be manually installed, as there are technical configuration settings
 *
 * @feature-off Download remote print models from a hub
 */
class Client implements Configurable, Registerable
{
	use Has_Get;

	//----------------------------------------------------------------------- DOWNLOAD_REMOTE_FEATURE
	const DOWNLOAD_REMOTE_FEATURE = 'remote_download';

	//------------------------------------------------------------------------------------ REMOTE_URL
	const REMOTE_URL = 'remote_url';

	//----------------------------------------------------------------------------------- $remote_url
	/**
	 * The base URL of the server application which hosts print models
	 *
	 * @var string
	 */
	public string $remote_url = 'https://hub.itrocks.org/hub';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration string|string[] The key is the local property name
	 */
	public function __construct($configuration = [])
	{
		if (!$configuration) {
			return;
		}
		if (!is_array($configuration)) {
			$configuration = [static::REMOTE_URL => $configuration];
		}
		foreach ($configuration as $property_name => $value) {
			$this->$property_name = $value;
		}
	}

	//------------------------------------------------------------------------------- addRemoteButton
	/**
	 * @param $class_name string
	 * @param $result     Button[]
	 */
	public function addRemoteButton(string $class_name, array &$result)
	{
		if (!is_a($class_name, Print_Model::class, true)) {
			return;
		}
		$buttons =& $result;
		$link    = View::link(
			Print_Model::class, Feature::F_LIST, arguments: ['callback' => Paths::getUrl(), 'X' => 'X']
		);
		$buttons[static::DOWNLOAD_REMOTE_FEATURE] = new Button(
			'Download other print models',
			$this->remote_url . $link,
			static::DOWNLOAD_REMOTE_FEATURE,
			[View::TARGET => Target::TOP]
		);
	}

	//-------------------------------------------------------------------------------------- download
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $form array
	 * @return Print_Model[]
	 */
	public function download(array $form) : array
	{
		$data    = http_build_query($form, arg_separator: '&');
		$context = stream_context_create(['http' => [
			'content' => $data,
			'header'  => 'Content-Type: application/x-www-form-urlencoded' . CRLF
				. 'Content-Length: ' . strlen($data),
			'method'  => Http::POST
		]]);
		$result = file_get_contents(
			$this->remote_url . View::link(Names::classToSet(Print_Model::class), Feature::F_EXPORT), false, $context
		);
		// TODO move this into Import, as it will be exactly the same
		/** @var $print_models Print_Model[] */
		/** @noinspection PhpUnhandledExceptionInspection Should work fine */
		$print_models = (new Json)->decodeObjects($result, Print_Model::class);
		if (json_last_error()) {
			trigger_error(json_last_error_msg(), E_USER_ERROR);
		}
		foreach ($print_models as $print_model) {
			foreach ($print_model->pages as $page) {
				if ($page->background->content ?? false) {
					$page->background->content = base64_decode($page->background->content);
				}
			}
			$print_model->status = Status::DOWNLOADED;
			Dao::write($print_model);
		}
		return $print_models;
	}

	//-------------------------------------------------------------------------------------- register
	public function register(Register $register)
	{
		$register->aop->afterMethod(
			[List_\Controller::class, 'getGeneralButtons'], [$this, 'addRemoteButton']
		);
	}

}
