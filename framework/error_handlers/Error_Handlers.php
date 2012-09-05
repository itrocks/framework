<?php
namespace SAF\Framework;

class Error_Handlers
{

	/**
	 * @var array
	 */
	private $error_handlers = array();

	/**
	 * @var Error_Handlers;
	 */
	private static $instance;

	//----------------------------------------------------------------------------------- __construct
	private function __construct()
	{
	}

	//------------------------------------------------------------------------------------ addHandler
	/**
	 * @param  integer       $err_no
	 * @param  Error_Handler $error_handler
	 * @param  integer       $priority
	 * @return Error_Handlers
	 */
	public function addHandler($err_no, $error_handler, $priority = Error_Handler_Priority::NORMAL)
	{
		$this->error_handlers[$err_no][$priority][] = $error_handler;
		ksort($this->error_handlers[$err_no]);
		return $this;
	}

	//-------------------------------------------------------------------------- getHandledErrorTypes
	/**
	 * @return array
	 */
	public function getHandledErrorTypes()
	{
		return array_keys($this->error_handlers);
	}

	//--------------------------------------------------------------------- getHandledErrorTypesAsInt
	/**
	 * @return int
	 */
	public function getHandledErrorTypesAsInt()
	{
		$error_types = 0;
		foreach (array_keys($this->error_handlers) as $error_type) {
			$error_types = $error_types | $error_type;
		}
		return $error_types;
	}

	//----------------------------------------------------------------------------------- getInstance
	/**
	 * Get main error handlers instance
	 *
	 * @return Error_Handlers
	 */
	public static function getInstance()
	{
		if (!Error_Handlers::$instance) {
			Error_Handlers::$instance = new Error_Handlers();
		}
		return Error_Handlers::$instance;
	}

	//---------------------------------------------------------------------------------------- handle
	/**
	 * 
	 * @param int    $err_no
	 * @param string $err_msg
	 * @param string $filename
	 * @param int    $line_num
	 * @param array  $vars
	 * @return bool
	 */
	public function handle($err_no, $err_msg, $filename, $line_num, $vars)
	{
		$handled_error = new Handled_Error($err_no, $err_msg, $filename, $line_num, $vars);
		foreach ($this->error_handlers as $err_no_filter => $handlers) {
			if (($err_no_filter & $err_no) == $err_no) {
				foreach ($handlers as $priority_handler) {
					foreach ($priority_handler as $handler) {
						$handler->handle($handled_error);
						if (!$handled_error->areNextErrorHandlersCalled()) {
							break 2;
						}
					}
				}
			}
		}
		return !$handled_error->isStandardPhpErrorHandlerCalled();
	}

	//----------------------------------------------------------------------------- setAsErrorHandler
	public function setAsErrorHandler()
	{
		set_error_handler(array($this, "handle"), $this->getHandledErrorTypesAsInt());
	}

}
