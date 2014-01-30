<?php
namespace SAF\Framework;

/**
 * The Plugin interface must be used to define plugins
 */
interface Plugin
{

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $dealer     Aop_Dealer
	 * @param $parameters array
	 */
	public function register($dealer, $parameters);

}
