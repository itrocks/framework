<?php
namespace SAF\Framework\Error_Handler;

use SAF\Framework\Plugin\Activable;
use SAF\Framework\Plugin\Configurable;
use SAF\Framework\Tools\Current;

/**
 * A configurable (with a php array) error handlers collection
 */
class Error_Handlers implements Activable, Configurable
{
	use Current { current as private pCurrent; }

	//------------------------------------------------------------------------------- $error_handlers
	/**
	 * @var array Error_Handler[][][]
	 */
	private $error_handlers = [];

	//------------------------------------------------------------------------------------- $instance
	/**
	 * @var Error_Handlers
	 */
	private static $instance;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct($configuration = null)
	{
		if (is_array($configuration)) {
			foreach ($configuration as $handle) {
				list($err_no, $error_handler_class) = $handle;
				$this->addHandler($err_no, new $error_handler_class());
			}
		}
	}

	//-------------------------------------------------------------------------------------- activate
	public function activate()
	{
		$this->setAsErrorHandler();
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Error_Handlers
	 * @return Error_Handlers
	 */
	public static function current(Error_Handlers $set_current = null)
	{
		return self::pCurrent($set_current);
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Register an error handler for error types
	 *
	 * @param $error_types integer
	 * @param $error_handler Error_Handler
	 */
	public static function add($error_types, Error_Handler $error_handler)
	{
		Error_Handlers::getInstance()->addHandler($error_types, $error_handler);
	}

	//------------------------------------------------------------------------------------ addHandler
	/**
	 * Add an error handler to the handled errors list
	 *
	 * You should call setAsErrorHandler() after add of error handlers, as error numbers may not have been registered
	 *
	 * @param $err_no integer
	 * @param $error_handler Error_Handler
	 * @param $priority integer
	 * @return Error_Handlers
	 */
	public function addHandler(
		$err_no, Error_Handler $error_handler, $priority = Error_Handler_Priority::NORMAL
	) {
		$this->error_handlers[$err_no][$priority][] = $error_handler;
		ksort($this->error_handlers[$err_no]);
		return $this;
	}

	//-------------------------------------------------------------------------- getHandledErrorTypes
	/**
	 * Get the handled error types list
	 *
	 * @return integer[]
	 */
	public function getHandledErrorTypes()
	{
		return array_keys($this->error_handlers);
	}

	//--------------------------------------------------------------------- getHandledErrorTypesAsInt
	/**
	 * Compile all registered handlers error types integers, to get the global handled errors type integer
	 *
	 * @return integer
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
	 * Get the unique error handlers instance
	 *
	 * @return Error_Handlers
	 */
	public static function getInstance()
	{
		return self::$instance;
	}

	//---------------------------------------------------------------------------------------- handle
	/**
	 * This method is automatically called when a registered error type occurs
	 *
	 * @param $err_no int
	 * @param $err_msg string
	 * @param $filename string
	 * @param $line_num int
	 * @param $vars array
	 * @return boolean
	 */
	public function handle($err_no, $err_msg, $filename, $line_num, $vars)
	{
		if ((error_reporting() & $err_no) == $err_no) {
			$handled_error = new Handled_Error($err_no, $err_msg, $filename, $line_num, $vars);
			foreach ($this->error_handlers as $err_no_filter => $handlers) {
				if (($err_no_filter & $err_no) == $err_no) {
					foreach ($handlers as $priority_handler) {
						/** @var $priority_handler Error_Handler[] */
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
		else {
			return false;
		}
	}

	//-------------------------------------------------------------------------------------------- on
	/**
	 * Activate error handler instance as the main error handler
	 */
	public static function on()
	{
		Error_Handlers::getInstance()->setAsErrorHandler();
	}

	//----------------------------------------------------------------------------- setAsErrorHandler
	/**
	 * Define Error_Handlers::handle() as default PHP error handler for all registered error types
	 */
	public function setAsErrorHandler()
	{
		set_error_handler([$this, 'handle'], $this->getHandledErrorTypesAsInt());
	}

}
