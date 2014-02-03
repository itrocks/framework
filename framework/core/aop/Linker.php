<?php
namespace SAF\Framework\Aop;

use SAF\Framework\Names;

/**
 * The Aop class is an interface to the Aop calls manager
 */
class Linker implements ILinker
{

	//---------------------------------------------------------------------------------------- $after
	/**
	 * @var array
	 */
	private $after = array();

	//--------------------------------------------------------------------------------------- $around
	/**
	 * @var array
	 */
	private $around = array();

	//--------------------------------------------------------------------------------------- $before
	/**
	 * @var array
	 */
	private $before = array();

	//--------------------------------------------------------------------------------------- $before
	/**
	 * @var array
	 */
	private $read = array();

	//--------------------------------------------------------------------------------------- $before
	/**
	 * @var array
	 */
	private $write = array();

	//-------------------------------------------------------------------------- addAfterFunctionCall
	/**
	 * Launch an advice after the execution of a given function
	 *
	 * Advices arguments are the pointcut object, then the arguments passed to the joinpoint function,
	 * and finally the value returned by the joinpoint method call.
	 * If set, the value returned by the advice will be the pointcut returned value.
	 * If not set, the result value passed as argument (that can be modified) will be returned
	 *
	 * @param $joinpoint string the joinpoint defined like a call-back : "functionName"
	 * @param $advice    callable the call-back call of the advice :
	 *                   array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @return IHandler
	 */
	public function addAfterFunctionCall($joinpoint, $advice)
	{
		if (!isset($this->after[$joinpoint])) {
			$this->after[$joinpoint][] = $advice;
		}
		array_unshift($this->after[$joinpoint], $advice);
		return new Handler($joinpoint, count($this->after[$joinpoint]) - 1, "after");
	}

	//---------------------------------------------------------------------------- addAfterMethodCall
	/**
	 * Launch an advice after the execution of a given method
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint method,
	 * and finally the value returned by the joinpoint method call.
	 * If set, the value returned by the advice will be the pointcut returned value.
	 * If not set, the result value passed as argument (that can be modified) will be returned
	 *
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *                   array("class_name", "methodName")
	 * @param $advice    callable the call-back call of the advice :
	 *                   array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @return IHandler
	 */
	public function addAfterMethodCall($joinpoint, $advice)
	{
		if (!isset($this->after[$joinpoint[0]][$joinpoint[1]])) {
			$this->after[$joinpoint[0]][$joinpoint[1]][] = $advice;
		}
		else {
			array_unshift($this->after[$joinpoint[0]][$joinpoint[1]], $advice);
		}
		return new Handler($joinpoint, count($this->after[$joinpoint[0]][$joinpoint[1]]) - 1, "after");
	}

	//------------------------------------------------------------------------- addAroundFunctionCall
	/**
	 * Launch an advice instead of the execution of a given function
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint method,
	 * and finally the value returned by the joinpoint method call.
	 * The value returned by the advice will be the pointcut returned value.
	 *
	 * @param $joinpoint string the joinpoint defined like a call-back : "functionName"
	 * @param $advice    callable the call-back call of the advice :
	 *                   array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @return IHandler
	 */
	public function addAroundFunctionCall($joinpoint, $advice)
	{
		if (!isset($this->around[$joinpoint])) {
			$this->around[$joinpoint][] = $advice;
		}
		array_unshift($this->around[$joinpoint], $advice);
		return new Handler($joinpoint, count($this->around[$joinpoint]) - 1, "around");
	}

	//--------------------------------------------------------------------------- addAroundMethodCall
	/**
	 * Launch an advice instead of the execution of a given method
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint function,
	 * and finally the value returned by the joinpoint method call.
	 * The value returned by the advice will be the pointcut returned value.
	 *
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *                   array("class_name", "methodName")
	 * @param $advice    callable the call-back call of the advice :
	 *                   array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @return IHandler
	 */
	public function addAroundMethodCall($joinpoint, $advice)
	{
		if (!isset($this->around[$joinpoint[0]][$joinpoint[1]])) {
			$this->around[$joinpoint[0]][$joinpoint[1]][] = $advice;
		}
		else {
			array_unshift($this->around[$joinpoint[0]][$joinpoint[1]], $advice);
		}
		return new Handler($joinpoint, count($this->around[$joinpoint[0]][$joinpoint[1]]) - 1, "around");
	}

	//------------------------------------------------------------------------- addBeforeFunctionCall
	/**
	 * Launch an advice before the execution of a given function
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint function.
	 * The advice can return a value : if this value is set, the execution of the joinpoint will be
	 * cancelled and the returned value replaced by this one.
	 *
	 * @param $joinpoint string the joinpoint defined like a call-back :
	 *                   array("class_name", "methodName")
	 * @param $advice    callable the call-back call of the advice :
	 *                   array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @return IHandler
	 */
	public function addBeforeFunctionCall($joinpoint, $advice)
	{
		if (!isset($this->before[$joinpoint])) {
			$this->before[$joinpoint][] = $advice;
		}
		array_unshift($this->before[$joinpoint], $advice);
		return new Handler($joinpoint, count($this->before[$joinpoint]) - 1, "before");
	}

	//--------------------------------------------------------------------------- addBeforeMethodCall
	/**
	 * Launch an advice before the execution of a given method
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint method
	 * The advice can return a value : if this value is set, the execution of the joinpoint will be
	 * cancelled and the returned value replaced by this one.
	 *
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *                   array("class_name", "methodName")
	 * @param $advice    callable the call-back call of the advice :
	 *                   array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @return IHandler
	 */
	public function addBeforeMethodCall($joinpoint, $advice)
	{
		if (!isset($this->before[$joinpoint[0]][$joinpoint[1]])) {
			$this->before[$joinpoint[0]][$joinpoint[1]][] = $advice;
		}
		else {
			array_unshift($this->before[$joinpoint[0]][$joinpoint[1]], $advice);
		}
		return new Handler($joinpoint, count($this->before[$joinpoint[0]][$joinpoint[1]]) - 1, "before");
	}

	//----------------------------------------------------------------------------- addOnPropertyRead
	/**
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *                   array("class_name", "property_name")
	 * @param $advice    callable the call-back call of the advice :
	 *                   array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @return IHandler
	 */
	public function addOnPropertyRead($joinpoint, $advice)
	{
		$joinpoint[1] = Names::propertyToMethod($joinpoint[1], "get");
		if (!isset($this->read[$joinpoint[0]][$joinpoint[1]])) {
			$this->read[$joinpoint[0]][$joinpoint[1]][] = $advice;
		}
		else {
			array_unshift($this->read[$joinpoint[0]][$joinpoint[1]], $advice);
		}
		return new Handler($joinpoint, count($this->read[$joinpoint[0]][$joinpoint[1]]) - 1, "read");
	}

	//---------------------------------------------------------------------------- addOnPropertyWrite
	/**
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *                   array("class_name", "property_name")
	 * @param $advice    callable the call-back call of the advice :
	 *                   array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @return IHandler
	 */
	public function addOnPropertyWrite($joinpoint, $advice)
	{
		$joinpoint[1] = Names::propertyToMethod($joinpoint[1], "set");
		if (!isset($this->write[$joinpoint[0]][$joinpoint[1]])) {
			$this->write[$joinpoint[0]][$joinpoint[1]][] = $advice;
		}
		else {
			array_unshift($this->write[$joinpoint[0]][$joinpoint[1]], $advice);
		}
		return new Handler($joinpoint, count($this->write[$joinpoint[0]][$joinpoint[1]]) - 1, "write");
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove an AOP link
	 *
	 * @param IHandler $handler
	 */
	public function remove(IHandler $handler)
	{
		/** @var $handler Handler */
		$type = $handler->type;
		if (is_string($handler->joinpoint)) {
			$this->$type[$handler->joinpoint][$handler->index] = null;
		}
		else {
			$this->$type[$handler->joinpoint[0]][$handler->joinpoint[1]][$handler->index] = null;
		}
	}

}
