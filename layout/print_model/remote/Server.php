<?php
namespace ITRocks\Framework\Layout\Print_Model\Remote;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Feature\List_;
use ITRocks\Framework\Layout\Print_Model;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\View;

/**
 * Allow the application to host a remote print model server
 * Clients will be allowed to access (in preview mode) and download remote print models from here
 *
 * This feature needs to be manually installed, as there are technical configuration settings
 *
 * @feature-off Allow your application to become a remote print models hub
 * @feature_free_access /ITRocks/Framework/Layout/Print_Model/\d+
 * @feature_free_access /ITRocks/Framework/Layout/Print_Model/\d+/export
 * @feature_free_access /ITRocks/Framework/Layout/Print_Model/list
 * @feature_free_access /ITRocks/Framework/Layout/Print_Model/output
 * @feature_free_access /ITRocks/Framework/Layout/Print_Model/Page/\d+/background
 * @feature_free_access /ITRocks/Framework/Layout/Print_Models
 * @feature_free_access /ITRocks/Framework/Layout/Print_Models/export(\?.*)?
 */
class Server implements Registerable
{
	use Has_Get;

	//------------------------------------------------------------------------------------- $callback
	/**
	 * @example 'http://localhost/mylocallapp'
	 * @var ?string
	 */
	public ?string $callback = null;

	//--------------------------------------------------------------------------------- addBackButton
	/**
	 * @param $class_name string
	 * @param $result     Button[]
	 */
	public function addBackButton(string $class_name, array& $result)
	{
		if (!is_a($class_name, Print_Model::class, true) || !$this->callback) {
			return;
		}
		$buttons =& $result;

		$new_buttons = [Feature::F_BACK => new Button(
			'Back',
			$this->callback . View::link(Print_Model::class, Feature::F_LIST),
			Feature::F_BACK,
			Target::MAIN
		)];
		$buttons = array_merge($new_buttons, $buttons);
	}

	//----------------------------------------------------------------------------- addDownloadButton
	/**
	 * @param $class_name string
	 * @param $result     Button[]
	 */
	public function addDownloadButton(string $class_name, array& $result)
	{
		if (!is_a($class_name, Print_Model::class, true) || !$this->callback) {
			return;
		}
		$buttons =& $result;

		$buttons['download'] = new Button(
			'Download',
			$this->callback . View::link(Print_Model::class, 'download'),
			'download',
			Target::RESPONSES
		);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$aop = $register->aop;
		$aop->afterMethod(
			[List_\Controller::class, 'getGeneralButtons'], [$this, 'addBackButton']
		);
		$aop->afterMethod(
			[List_\Controller::class, 'getSelectionButtons'], [$this, 'addDownloadButton']
		);
		$aop->beforeMethod(
			[List_\Controller::class, 'getViewParameters'], [$this, 'storeCallback']
		);
	}

	//--------------------------------------------------------------------------------- storeCallback
	/**
	 * @param $parameters Parameters
	 * @param $class_name string
	 */
	public function storeCallback(Parameters $parameters, string $class_name) : void
	{
		if (!is_a($class_name, Print_Model::class, true) || !$parameters->has('callback')) {
			return;
		}
		$this->callback = $parameters->getRawParameter('callback') ?: '';
	}

}
