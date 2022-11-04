<?php
namespace ITRocks\Framework\Error_Handler;

use ITRocks\Framework\Plugin\Activable;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Tools\Current;

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
	private array $error_handlers = [];

	//------------------------------------------------------------------------------------- $instance
	/**
	 * @var Error_Handlers
	 */
	private static Error_Handlers $instance;

	//------------------------------------------------------------------- $last_handled_error_message
	/**
	 * @var string
	 */
	public string $last_handled_error_message = '';

	//-------------------------------------------------------------------- $last_handled_error_number
	/**
	 * @var integer
	 */
	public int $last_handled_error_number = 0;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct(mixed $configuration = [])
	{
		foreach ($configuration as $handle) {
			[$err_no, $error_handler_class] = $handle;
			$this->addHandler($err_no, new $error_handler_class());
		}
	}

	//-------------------------------------------------------------------------------------- activate
	public function activate()
	{
		$this->setAsErrorHandler();
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Register an error handler for error types
	 *
	 * @param $error_types   integer
	 * @param $error_handler Error_Handler
	 */
	public static function add(int $error_types, Error_Handler $error_handler)
	{
		Error_Handlers::getInstance()->addHandler($error_types, $error_handler);
	}

	//------------------------------------------------------------------------------------ addHandler
	/**
	 * Add an error handler to the handled errors list
	 *
	 * You should call setAsErrorHandler() after add of error handlers, as error numbers may not have been registered
	 *
	 * @param $err_no        integer
	 * @param $error_handler Error_Handler
	 * @param $priority      integer
	 * @return $this
	 */
	public function addHandler(
		int $err_no, Error_Handler $error_handler, int $priority = Error_Handler_Priority::NORMAL
	) : static
	{
		$this->error_handlers[$err_no][$priority][] = $error_handler;
		ksort($this->error_handlers[$err_no]);
		return $this;
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current static|null
	 * @return ?static
	 */
	public static function current(self $set_current = null) : ?static
	{
		/** @noinspection PhpIncompatibleReturnTypeInspection inspector error : more restrictive */
		return self::pCurrent($set_current);
	}

	//-------------------------------------------------------------------------- getHandledErrorTypes
	/**
	 * Get the handled error types list
	 *
	 * @return integer[]
	 */
	public function getHandledErrorTypes() : array
	{
		return array_keys($this->error_handlers);
	}

	//--------------------------------------------------------------------- getHandledErrorTypesAsInt
	/**
	 * Compile all registered handlers error types integers, to get the global handled errors type integer
	 *
	 * @return integer
	 */
	public function getHandledErrorTypesAsInt() : int
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
	 * @return self
	 */
	public static function getInstance() : self
	{
		return self::$instance;
	}

	//---------------------------------------------------------------------------------------- handle
	/**
	 * This method is automatically called when a registered error type occurs
	 *
	 * @param $err_no   integer
	 * @param $err_msg  string
	 * @param $filename string
	 * @param $line_num integer
	 * @param $vars     array
	 * @return boolean
	 */
	public function handle(
		int $err_no, string $err_msg, string $filename, int $line_num, array $vars = []
	) : bool
	{
		if ((error_reporting() & $err_no) === $err_no) {
			if (!class_exists(Handled_Error::class)) {
				trigger_error(
					'CRASH : Class Handled_Error not found : ' . LF
					. $err_no . SP . $err_msg . SP . $filename . SP . $line_num . SP . print_r($vars, true)
					. print_r($GLOBALS, true),
					E_USER_ERROR
				);
			}
			$handled_error = new Handled_Error($err_no, $err_msg, $filename, $line_num, $vars);
			foreach ($this->error_handlers as $err_no_filter => $handlers) {
				if (($err_no_filter & $err_no) === $err_no) {
					foreach ($handlers as $priority_handler) {
						/** @var $priority_handler Error_Handler[] */
						foreach ($priority_handler as $handler) {
							$this->last_handled_error_message = $err_msg;
							$this->last_handled_error_number  = $err_no;
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
		register_shutdown_function([$this, 'shutdown']);
		set_error_handler([$this, 'handle'], $this->getHandledErrorTypesAsInt());
	}

	//-------------------------------------------------------------------------------------- shutdown
	/**
	 * Deal with special case of errors that are not catch 'naturally' by the error handler
	 */
	public function shutdown()
	{
		if (
			($error = error_get_last())
			&& ($error['message'] !== $this->last_handled_error_message)
			&& in_array($error['type'], [E_CORE_ERROR, E_COMPILE_ERROR, E_ERROR, E_PARSE])
		) {
			// increase memory / time limit to manage the error
			if (str_starts_with($error['message'], 'Allowed memory size of')) {
				ini_set('memory_limit', memory_get_peak_usage(true) + 10000000);
			}
			elseif (str_starts_with($error['message'], 'Maximum execution time of')) {
				set_time_limit(10);
			}
			(new Report_Call_Stack_Error_Handler())->handle(
				new Handled_Error($error['type'], $error['message'], $error['file'], $error['line'])
			);
		}
	}

}
