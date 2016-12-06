<?php
namespace ITRocks\Framework\Asynchronous\Running;

use ITRocks\Framework\Asynchronous;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Tools\Date_Time;

/**
 * @set Asynchronous_Running_Requests
 * @unique request_class_name, request_identifier
 */
class Request extends Asynchronous\Request
{
	//---------------------------------------------------------------------------------------- $tasks
	/**
	 * @var Task[]
	 */
	public $tasks;

	//--------------------------------------------------------------------------- $request_class_name
	/**
	 * Class name of request
	 *
	 * @mandatory
	 * @var string
	 */
	public $request_class_name;

	//--------------------------------------------------------------------------- $request_identifier
	/**
	 * Identifier of request
	 *
	 * @mandatory
	 * @var integer
	 */
	public $request_identifier;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Request constructor.
	 * @param $request_class_name string
	 * @param $request_identifier integer
	 */
	public function __construct($request_class_name = null, $request_identifier = null)
	{
		if ($request_class_name && $request_identifier) {
			$this->request_class_name = $request_class_name;
			$this->request_identifier = $request_identifier;
		}
		parent::__construct('Running task');
	}

	//------------------------------------------------------------------------------------ getRequest
	/**
	 * Return request to must be executed
	 *
	 * @param $request Asynchronous\Request
	 * @return static
	 */
	public static function getRequest(Asynchronous\Request $request)
	{
		/** @var $running_request static */
		$running_request = Dao::searchOne(
			[
				'request_class_name' => get_class($request),
				'request_identifier' => Dao::getObjectIdentifier($request)
			],
			static::class
		);
		if ($running_request) {
			return $running_request;
		}
		else {
			$running_request = new static(get_class($request), Dao::getObjectIdentifier($request));
			$running_request->creation = new Date_Time();
			Dao::write($running_request);
			return $running_request;
		}
	}

	//------------------------------------------------------------------------------- getRequestToRun
	/**
	 * @return Asynchronous\Request
	 */
	public function getRequestToRun()
	{
		return Dao::read($this->request_identifier, $this->request_class_name);
	}

}
