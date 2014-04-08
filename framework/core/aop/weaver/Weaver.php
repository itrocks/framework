<?php
namespace SAF\AOP;

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
	 * @var array array[$class][$method][$index] = [$type, callback $advice)
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
	 *                   ['class_name', 'methodName'), [$object, 'methodName'), 'functionName'
	 * @return IHandler
	 */
	public function afterFunction($joinpoint, $advice)
	{
		$this->joinpoints[$joinpoint][] = ['after', $advice];
		return new Handler('after', $joinpoint, count($this->joinpoints[$joinpoint]) - 1);
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
	 *                   ['class_name', 'methodName')
	 * @param $advice    callable the call-back call of the advice :
	 *                   ['class_name', 'methodName'), [$object, 'methodName'), 'functionName'
	 * @return IHandler
	 */
	public function afterMethod($joinpoint, $advice)
	{
		$this->joinpoints[$joinpoint[0]][$joinpoint[1]][] = ['after', $advice];
		return new Handler(
			'after', $joinpoint, count($this->joinpoints[$joinpoint[0]][$joinpoint[1]]) - 1
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
	 *                   ['class_name', 'methodName'), [$object, 'methodName'), 'functionName'
	 * @return IHandler
	 */
	public function aroundFunction($joinpoint, $advice)
	{
		$this->joinpoints[$joinpoint][] = ['around', $advice];
		return new Handler('around', $joinpoint, count($this->joinpoints[$joinpoint]) - 1);
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
	 *                   ['class_name', 'methodName')
	 * @param $advice    callable the call-back call of the advice :
	 *                   ['class_name', 'methodName'), [$object, 'methodName'), 'functionName'
	 * @return IHandler
	 */
	public function aroundMethod($joinpoint, $advice)
	{
		$this->joinpoints[$joinpoint[0]][$joinpoint[1]][] = ['around', $advice];
		return new Handler(
			'around', $joinpoint, count($this->joinpoints[$joinpoint[0]][$joinpoint[1]]) - 1
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
	 *                   ['class_name', 'methodName')
	 * @param $advice    callable the call-back call of the advice :
	 *                   ['class_name', 'methodName'), [$object, 'methodName'), 'functionName'
	 * @return IHandler
	 */
	public function beforeFunction($joinpoint, $advice)
	{
		$this->joinpoints[$joinpoint][] = ['before', $advice];
		return new Handler('before', $joinpoint, count($this->joinpoints[$joinpoint]) - 1);
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
	 *                   ['class_name', 'methodName')
	 * @param $advice    callable the call-back call of the advice :
	 *                   ['class_name', 'methodName'), [$object, 'methodName'), 'functionName'
	 * @return IHandler
	 */
	public function beforeMethod($joinpoint, $advice)
	{
		$this->joinpoints[$joinpoint[0]][$joinpoint[1]][] = ['before', $advice];
		return new Handler(
			'before', $joinpoint, count($this->joinpoints[$joinpoint[0]][$joinpoint[1]]) - 1
		);
	}

	//--------------------------------------------------------------------------------- getJoinpoints
	/**
	 * @param $joinpoint_name string joinpoint class or function name
	 * @return array
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

	//---------------------------------------------------------------------------------- readProperty
	/**
	 * @param $joinpoint callable the joinpoint defined like a call-back :
	 *                   ['class_name', 'property_name')
	 * @param $advice    callable the call-back call of the advice :
	 *                   ['class_name', 'methodName'), [$object, 'methodName'), 'functionName'
	 * @return IHandler
	 */
	public function readProperty($joinpoint, $advice)
	{
		$this->joinpoints[$joinpoint[0]][$joinpoint[1]][] = ['read', $advice];
		return new Handler(
			'read', $joinpoint, count($this->joinpoints[$joinpoint[0]][$joinpoint[1]]) - 1
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

	//---------------------------------------------------------------------------------- writeProperty
	/**
	 * @param $joinpoint callable the joinpoint defined like a call-back :
	 *                   ['class_name', 'property_name')
	 * @param $advice    callable the call-back call of the advice :
	 *                   ['class_name', 'methodName'), [$object, 'methodName'), 'functionName'
	 * @return IHandler
	 */
	public function writeProperty($joinpoint, $advice)
	{
		$this->joinpoints[$joinpoint[0]][$joinpoint[1]][] = ['write', $advice];
		return new Handler(
			'write', $joinpoint, count($this->joinpoints[$joinpoint[0]][$joinpoint[1]]) - 1
		);
	}

}
