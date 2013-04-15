<?php
namespace SAF\Framework;

class Email_Policy
{

	//--------------------------------------------------------------------------- $delay_between_mail
	/**
	 * Delay between each mail transfer on the same host, in seconds
	 *
	 * @var integer
	 */
	public $delay_between_mail = 0;

	//----------------------------------------------------------------------------------- $delay_send
	/**
	 * Used for send policy
	 *
	 * Value is true do always delay, false to always send, "auto" to try send and delay if any error
	 *
	 * @var boolean|string
	 */
	public $delay_send = false;

	//------------------------------------------------------------------------------- $multiple_hosts
	/**
	 * If true, multiple hosts will be used each time a mail have to be transmitted
	 *
	 * This is usefull for charge dispatching
	 *
	 * @var boolean
	 */
	public $multiple_hosts = true;

	//------------------------------------------------------------------------------- $multiple_retry
	/**
	 * If true, transfer will retry on next host if an error occurs on current host
	 *
	 * @var boolean
	 */
	public $multiple_retry = true;

	//---------------------------------------------------------------------------------------- $retry
	/**
	 * Retry count when could not send to one host
	 *
	 * @var integer
	 */
	public $retry = 1;

	//-------------------------------------------------------------------------- $delay_between_retry
	/**
	 * Delay between each retry on the same host, in seconds
	 *
	 * @var integer
	 */
	public $delay_between_retry = 60;

}
