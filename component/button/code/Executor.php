<?php
namespace ITRocks\Framework\Component\Button\Code;

use ITRocks\Framework\Component\Button\Code;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Uri;
use ITRocks\Framework\Feature\Save;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;

/**
 * Executes code before executing the controller
 */
class Executor implements Registerable
{

	//------------------------------------------------------------------------------------------ $uri
	/**
	 * @var Uri
	 */
	private Uri $uri;

	//------------------------------------------------------------------------------- executeNotWrite
	/**
	 * Execute some code given into a URI.
	 * Called first before Main::executeController()
	 *
	 * @param $uri Uri
	 */
	public function executeNotWrite(Uri $uri) : void
	{
		if ($uri->parameters->has(Code::class) && ($uri->feature_name !== Feature::F_SAVE)) {
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
	 * Execute some code given into a URI.
	 *
	 * Called during default Save_Controller, between the time when object has been modified by the
	 * form content and the time it is written using Dao.
	 */
	public function executeWrite() : void
	{
		if (isset($this->uri) && ($this->uri->feature_name === Feature::F_SAVE)) {
			$code = $this->uri->parameters->getObject(Code::class);
			if ($code) {
				$code->execute($this->uri->parameters->getMainObject());
			}
			unset($this->uri);
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$register->aop->beforeMethod([Main::class, 'executeController'], [$this, 'executeNotWrite']);
		$register->aop->beforeMethod([Save\Controller::class, 'write'],  [$this, 'executeWrite']);
	}

}
