<?php
namespace ITRocks\Framework\Access;

use ITRocks\Framework\Application;
use ITRocks\Framework\Controller;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\View;

/**
 * An access control plugin for features available only from a set of IP
 *
 * The same set of IP accesses all features
 */
class IP implements Configurable, Registerable
{

	//------------------------------------------------------------------------------ REMOTE_ADDRESSES
	const REMOTE_ADDRESSES = 'remote_addresses';

	//------------------------------------------------------------------------------------------ URIS
	const URIS             = 'uris';

	//----------------------------------------------------------------------------- $remote_addresses
	/**
	 * Allowed remote addresses (host names or IPs)
	 *
	 * @var string[]
	 */
	public $remote_addresses;

	//----------------------------------------------------------------------------------------- $uris
	/**
	 * URIs restricted by originator access control, used to call a feature into the application
	 *
	 * @var string[]
	 */
	public $uris;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * IP configurable plugin constructor
	 *
	 * @param $configuration array
	 */
	public function __construct($configuration = null)
	{
		if (isset($configuration)) {
			foreach ($configuration as $key => $value) {
				$this->$key = is_array($value) ? array_combine($value, $value) : $value;
			}
		}
	}

	//----------------------------------------------------------------------------------- checkAccess
	/**
	 * @param $uri string
	 */
	public function checkAccess(&$uri)
	{
		if (pregMatchArray($this->uris, $uri, true)) {
			if (!$this->checkIP($_SERVER['REMOTE_ADDR'])) {
				$uri = View::link(Application::class, Controller\Feature::F_BLANK);
			}
		}
	}

	//--------------------------------------------------------------------------------------- checkIP
	/**
	 * Returns true if the remote address matches the originators list
	 *
	 * @param $remote_address string The remote client address (IP)
	 * @return boolean
	 */
	private function checkIP($remote_address)
	{
		if (isset($this->remote_addresses[$remote_address])) {
			return true;
		}
		foreach ($this->remote_addresses as $address) {
			if (!$this->isIP($address)) {
				unset($this->remote_addresses[$address]);
				$ip = gethostbyname($address);
				if ($ip !== $address) {
					$this->remote_addresses[$ip] = $ip;
					if ($ip === $remote_address) {
						return true;
					}
				}
			}
		}
		return false;
	}

	//------------------------------------------------------------------------------------------ isIP
	/**
	 * Returns true if $address is an IP
	 *
	 * @param $address string
	 * @return true
	 */
	private function isIP($address)
	{
		$address = explode(DOT, $address);
		return (count($address) == 4)
			&& is_numeric($address[0]) && is_numeric($address[1])
			&& is_numeric($address[2]) && is_numeric($address[3]);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->beforeMethod([Main::class, 'runController'], [$this, 'checkAccess']);
	}

}
