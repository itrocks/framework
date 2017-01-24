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
 *
 * @example config.php :
 *   Framework\Access\IP::class => [
 *			'Group 1' => [
 *				IP::REMOTE_ADDRESSES => [
 *					'IP1', 'IP2'
 * 				],
 *        IP::URIS => [
 *					'action1', 'action2'
 *        ]
 *      ]
 *			'Group 2' => [
 *				IP::REMOTE_ADDRESSES => [
 *					'IP1', 'IP2'
 * 				],
 *        IP::URIS => [
 *					'action1', 'action2'
 *        ]
 *      ]
 *  ]
 *
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
	 * @var array string[][] [string $free_group_name => string[]]
	 */
	public $remote_addresses;

	//----------------------------------------------------------------------------------------- $uris
	/**
	 * URIs restricted by originator access control, used to call a feature into the application
	 *
	 * @var array string[][] [string $free_group_name => string[]]
	 */
	public $uris;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * IP configurable plugin constructor
	 *
	 * @param $configuration array
	 */
	public function __construct($configuration = [])
	{
		foreach ($configuration as $group_name => $group) {
			foreach ($group as $key => $value) {
				if (is_array($value)) {
					$this->{$key}[$group_name] = array_combine($value, $value);
				}
				// retro-compatibility with one-group-only configuration (into config.php)
				else {
					$this->$group_name = array_combine($group, $group);
					break;
				}
			}
		}

		// retro-compatibility with one-group-only configuration (running sessions are compatible too)
		$first_uri = reset($this->uris);
		if (!is_array($first_uri)) {
			$this->uris = [$this->uris];
		}
		$first_remote_address = reset($this->remote_addresses);
		if (!is_array($first_remote_address)) {
			$this->remote_addresses = [$this->remote_addresses];
		}
	}

	//----------------------------------------------------------------------------------- checkAccess
	/**
	 * @param $uri string
	 */
	public function checkAccess(&$uri)
	{
		foreach ($this->uris as $group_name => $uris) {
			if (pregMatchArray($uris, $uri, true)) {
				if (!$this->checkIP($_SERVER['REMOTE_ADDR'], $group_name)) {
					$uri = View::link(Application::class, Controller\Feature::F_BLANK);
				}
			}
		}
	}

	//--------------------------------------------------------------------------------------- checkIP
	/**
	 * Returns true if the remote address matches the originators list
	 *
	 * @param $remote_address string The remote client address (IP)
	 * @param $group_name     string
	 * @return boolean
	 */
	private function checkIP($remote_address, $group_name)
	{
		if (isset($this->remote_addresses[$group_name][$remote_address])) {
			return true;
		}

		foreach ($this->remote_addresses[$group_name] as $address) {
			if (!$this->isIP($address)) {
				unset($this->remote_addresses[$group_name][$address]);
				$ip = gethostbyname($address);
				if ($ip !== $address) {
					$this->remote_addresses[$group_name][$ip] = $ip;
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
