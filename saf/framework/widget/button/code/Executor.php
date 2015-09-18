<?php
namespace SAF\Framework\Widget\Button\Code;

use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Main;
use SAF\Framework\Controller\Uri;
use SAF\Framework\Plugin;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Widget\Button\Code;
use SAF\Framework\Widget\Write\Write_Controller;

/**
 * Executes code before executing the controller
 */
class Executor implements Registerable
{

	//------------------------------------------------------------------------------------------ $uri
	/**
	 * @var Uri
	 */
	private $uri;

	//------------------------------------------------------------------------------- executeNotWrite
	/**
	 * Execute some code given into an URI.
	 * Called first before Main::executeController()
	 *
	 * @param $uri Uri
	 */
	public function executeNotWrite(Uri $uri)
	{
		if ($uri->parameters->has(Code::class) && ($uri->feature_name !== Feature::F_WRITE)) {
			$code = $uri->parameters->getObject(Code::class);
			if ($code) {
				$code->execute($uri);
			}
		}
		else {
			$this->uri = $uri;
		}
	}

	//---------------------------------------------------------------------------------- executeWrite
	/**
	 * Execute some code given into an URI.
	 *
	 * Called during default Write_Controller, between the time when object has been modified by the
	 * form content and the time it is written using Dao.
	 */
	public function executeWrite()
	{
		if (isset($this->uri) && ($this->uri->feature_name === Feature::F_WRITE)) {
			/** @var $code Code */
			$code = $this->uri->parameters->getObject(Code::class);
			if ($code) {
				$code->execute($this->uri->parameters->getMainObject());
			}
			unset($this->uri);
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->beforeMethod([Main::class, 'executeController'], [$this, 'executeNotWrite']);
		$register->aop->beforeMethod([Write_Controller::class, 'write'], [$this, 'executeWrite']);
	}

}
