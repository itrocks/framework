<?php
namespace ITRocks\Framework\AOP;

use ITRocks\Framework\AOP\Weaver\Handler;
use ITRocks\Framework\AOP\Weaver\IHandler;
use ITRocks\Framework\AOP\Weaver\IWeaver;
use ITRocks\Framework\Session;

/**
 * The Aop class is an interface to the Aop calls manager
 */
class Weaver implements IWeaver
{

	//----------------------------------------------------------------------------------- $joinpoints
	/**
	 * All joinpoints are stored here
	 *
	 * @var array array[$function][$index] = [$type, callback $advice)
	 * @var array array[$class][$method][$index] = [$type, callback $advice]
	 */
	private $joinpoints = [];

	//--------------------------------------------------------------------------------- afterFunction
	/**
	 * Weave an aspect after the execution of a given function
	 *
	 * Advices arguments are the pointcut object, then the arguments passed to the joinpoint function,
	 * and finally the value returned by the joinpoint method call.
	 * If set, the value returned by the advice will be the pointcut returned value.
	 * If not set, the result value passed as argument (that can be modified) will be returned
	 *
	 * @param $joinpoint string the joinpoint defined like a call-back : 'functionName'
	 * @param $advice    callable the call-back call of the advice :
	 *                   ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function afterFunction($joinpoint, $advice)
	{
		if (!is_string($joinpoint)) {
			trigger_error('Joinpoint must be a function name', E_USER_ERROR);
		}
		$this->joinpoints[$joinpoint][] = [Handler::AFTER, $advice];
		return new Handler(Handler::AFTER, $joinpoint, count($this->joinpoints[$joinpoint]) - 1);
	}

	//----------------------------------------------------------------------------------- afterMethod
	/**
	 * Weave an aspect after the execution of a given method
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint method,
	 * and finally the value returned by the joinpoint method call.
	 * If set, the value returned by the advice will be the pointcut returned value.
	 * If not set, the result value passed as argument (that can be modified) will be returned
	 *
	 * @param $joinpoint callable the joinpoint defined like a call-back :
	 *                   ['class_name', 'methodName']
	 * @param $advice    callable the call-back call of the advice :
	 *                   ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function afterMethod($joinpoint, $advice)
	{
		if ((is_string($joinpoint) && !strpos($joinpoint, '::')) || !is_array($joinpoint)) {
			trigger_error('Joinpoint must be Class::method or [Class, method]', E_USER_ERROR);
		}
		$this->joinpoints[$joinpoint[0]][$joinpoint[1]][] = [Handler::AFTER, $advice];
		return new Handler(
			Handler::AFTER, $joinpoint, count($this->joinpoints[$joinpoint[0]][$joinpoint[1]]) - 1
		);
	}

	//-------------------------------------------------------------------------------- aroundFunction
	/**
	 * Weave an aspect instead of the execution of a given function
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint method,
	 * and finally the value returned by the joinpoint method call.
	 * The value returned by the advice will be the pointcut returned value.
	 *
	 * @param $joinpoint string the joinpoint defined like a call-back : 'functionName'
	 * @param $advice    callable the call-back call of the advice :
	 *                   ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function aroundFunction($joinpoint, $advice)
	{
		if (!is_string($joinpoint)) {
			trigger_error('Joinpoint must be a function name', E_USER_ERROR);
		}
		$this->joinpoints[$joinpoint][] = [Handler::AROUND, $advice];
		return new Handler(Handler::AROUND, $joinpoint, count($this->joinpoints[$joinpoint]) - 1);
	}

	//---------------------------------------------------------------------------------- aroundMethod
	/**
	 * Weave an aspect instead of the execution of a given method
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint function,
	 * and finally the value returned by the joinpoint method call.
	 * The value returned by the advice will be the pointcut returned value.
	 *
	 * @param $joinpoint callable the joinpoint defined like a call-back :
	 *                   ['class_name', 'methodName']
	 * @param $advice    callable the call-back call of the advice :
	 *                   ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function aroundMethod($joinpoint, $advice)
	{
		if ((is_string($joinpoint) && !strpos($joinpoint, '::')) || !is_array($joinpoint)) {
			trigger_error('Joinpoint must be Class::method or [Class, method]', E_USER_ERROR);
		}
		$this->joinpoints[$joinpoint[0]][$joinpoint[1]][] = [Handler::AROUND, $advice];
		return new Handler(
			Handler::AROUND, $joinpoint, count($this->joinpoints[$joinpoint[0]][$joinpoint[1]]) - 1
		);
	}

	//-------------------------------------------------------------------------------- beforeFunction
	/**
	 * Weave an aspect before the execution of a given function
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint function.
	 * The advice can return a value : if this value is set, the execution of the joinpoint will be
	 * cancelled and the returned value replaced by this one.
	 *
	 * @param $joinpoint string the joinpoint defined like a call-back :
	 *                   ['class_name', 'methodName']
	 * @param $advice    callable the call-back call of the advice :
	 *                   ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function beforeFunction($joinpoint, $advice)
	{
		if (!is_string($joinpoint)) {
			trigger_error('Joinpoint must be a function name', E_USER_ERROR);
		}
		$this->joinpoints[$joinpoint][] = [Handler::BEFORE, $advice];
		return new Handler(Handler::BEFORE, $joinpoint, count($this->joinpoints[$joinpoint]) - 1);
	}

	//---------------------------------------------------------------------------------- beforeMethod
	/**
	 * Weave an aspect before the execution of a given method
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint method
	 * The advice can return a value : if this value is set, the execution of the joinpoint will be
	 * cancelled and the returned value replaced by this one.
	 *
	 * @param $joinpoint callable the joinpoint defined like a call-back :
	 *                   ['class_name', 'methodName']
	 * @param $advice    callable the call-back call of the advice :
	 *                   ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function beforeMethod($joinpoint, $advice)
	{
		if ((is_string($joinpoint) && !strpos($joinpoint, '::')) || !is_array($joinpoint)) {
			trigger_error('Joinpoint must be Class::method or [Class, method]', E_USER_ERROR);
		}
		$this->joinpoints[$joinpoint[0]][$joinpoint[1]][] = [Handler::BEFORE, $advice];
		return new Handler(
			Handler::BEFORE, $joinpoint, count($this->joinpoints[$joinpoint[0]][$joinpoint[1]]) - 1
		);
	}

	//------------------------------------------------------------------------------------- dumpArray
	/**
	 * Change joinpoints array into a dumped php source [...].
	 * Replaces all objects by current session plugins getters
	 *
	 * @param $array array
	 * @return string
	 */
	private function dumpArray(array $array)
	{
		$lf1 = LF . TAB;
		$lf2 = $lf1 . TAB;
		$lf3 = $lf2 . TAB;
		$dump = '[';
		foreach ($array as $class => $j1) {
			$dump .= $lf1 . Q . $class . Q . ' => [';
			foreach ($j1 as $method => $j2) {
				if (is_numeric($method)) {
					$joinpoint = $j2;
					$dump .= $lf2 . '[' . Q . $joinpoint[0] . Q . ', ';
					if (is_array($advice = $joinpoint[1])) {
						$dump .= '[';
						if (is_object($advice[0])) {
							$dump .= '$plugins->get(' . get_class($advice[0]) . '::class), ';
						}
						else {
							$dump .= $advice[0] . '::class, ';
						}
						$dump .= Q . $advice[1] . Q . ']';
					}
					else {
						$dump .= Q . $advice . Q;
					}
					$dump .= '],';
				}
				else {
					$dump .= $lf2 . Q . $method . Q . ' => [';
					foreach ($j2 as $joinpoint) {
						$dump .= $lf3 . '[' . Q . $joinpoint[0] . Q . ', ';
						if (is_array($advice = $joinpoint[1])) {
							$dump .= '[';
							if (is_object($advice[0])) {
								$dump .= '$plugins->get(' . get_class($advice[0]) . '::class), ';
							}
							else {
								$dump .= $advice[0] . '::class, ';
							}
							$dump .= Q . $advice[1] . Q . ']';
						}
						else {
							$dump .= Q . $advice . Q;
						}
						$dump .= '],';
					}
					$dump .= $lf2 . '],';
				}
			}
			$dump .= $lf1 . '],';
		}
		$dump .= LF . ']';
		return $dump;
	}

	//---------------------------------------------------------------------------------- getJoinpoint
	/**
	 * Gets existing joinpoints for a class method or property
	 *
	 * @param $joinpoint callable A class method or property name
	 * @return array [$index] = [$type, callback $advice]
	 */
	public function getJoinpoint($joinpoint)
	{
		if (isset($this->joinpoints[$joinpoint[0]][$joinpoint[1]])) {
			return $this->joinpoints[$joinpoint[0]][$joinpoint[1]];
		}
		return [];
	}

	//--------------------------------------------------------------------------------- getJoinpoints
	/**
	 * Gets existing joinpoints for a class name
	 *
	 * @param $joinpoint_name string joinpoint class or function name
	 * @return array [$method][$index] = [$type, callback $advice]
	 */
	public function getJoinpoints($joinpoint_name = null)
	{
		return isset($joinpoint_name)
			? (isset($this->joinpoints[$joinpoint_name]) ? $this->joinpoints[$joinpoint_name] : [])
			: $this->joinpoints;
	}

	//--------------------------------------------------------------------------------- hasJoinpoints
	/**
	 * @return boolean
	 */
	public function hasJoinpoints()
	{
		return $this->joinpoints ? true : false;
	}

	//-------------------------------------------------------------------------------- loadJoinpoints
	/**
	 * @param $file_name string
	 */
	public function loadJoinpoints($file_name)
	{
		/** @noinspection PhpIncludeInspection */
		$this->joinpoints = file_exists($file_name) ? (include $file_name) : [];
	}

	//---------------------------------------------------------------------------------- readProperty
	/**
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *                   ['class_name', 'property_name']
	 * @param $advice    callable the call-back call of the advice :
	 *                   ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function readProperty(array $joinpoint, $advice)
	{
		$this->joinpoints[$joinpoint[0]][$joinpoint[1]][] = [Handler::READ, $advice];
		return new Handler(
			Handler::READ, $joinpoint, count($this->joinpoints[$joinpoint[0]][$joinpoint[1]]) - 1
		);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Unweave an aspect
	 *
	 * @param IHandler $handler
	 */
	public function remove(IHandler $handler)
	{
		/** @var $handler Handler */
		if (is_string($handler->joinpoint)) {
			$this->joinpoints[$handler->joinpoint][$handler->index] = null;
		}
		else {
			$this->joinpoints[$handler->joinpoint[0]][$handler->joinpoint[1]][$handler->index]
				= null;
		}
	}

	//-------------------------------------------------------------------------------- saveJoinpoints
	/**
	 * @param $file_name string
	 */
	public function saveJoinpoints($file_name)
	{
		file_put_contents(
			$file_name,
			'<?php' . LF . LF
			. '$plugins = ' . Session::class . '::current()->plugins;' . LF . LF
			. 'return ' . $this->dumpArray($this->joinpoints) . ';' . LF
		);
	}

	//--------------------------------------------------------------------------------- writeProperty
	/**
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *                   ['class_name', 'property_name']
	 * @param $advice    callable the call-back call of the advice :
	 *                   ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function writeProperty(array $joinpoint, $advice)
	{
		$this->joinpoints[$joinpoint[0]][$joinpoint[1]][] = [Handler::WRITE, $advice];
		return new Handler(
			Handler::WRITE, $joinpoint, count($this->joinpoints[$joinpoint[0]][$joinpoint[1]]) - 1
		);
	}

}
