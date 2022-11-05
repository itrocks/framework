<?php
namespace ITRocks\Framework\AOP\Weaver;

/**
 * Aop weaver interface
 */
interface IWeaver
{

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
	 *        ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function afterFunction(string $joinpoint, callable $advice) : IHandler;

	//----------------------------------------------------------------------------------- afterMethod
	/**
	 * Weave an aspect after the execution of a given method
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint method,
	 * and finally the value returned by the joinpoint method call.
	 * If set, the value returned by the advice will be the pointcut returned value.
	 * If not set, the result value passed as argument (that can be modified) will be returned
	 *
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *        ['class_name', 'methodName']
	 * @param $advice callable the call-back call of the advice :
	 *        ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function afterMethod(array $joinpoint, callable $advice) : IHandler;

	//-------------------------------------------------------------------------------- aroundFunction
	/**
	 * Weave an aspect instead of the execution of a given function
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint method,
	 * and finally the value returned by the joinpoint method call.
	 * The value returned by the advice will be the pointcut returned value.
	 *
	 * @param $joinpoint string the joinpoint defined like a call-back : 'functionName'
	 * @param $advice callable the call-back call of the advice :
	 *        ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function aroundFunction(string $joinpoint, callable $advice) : IHandler;

	//---------------------------------------------------------------------------------- aroundMethod
	/**
	 * Weave an aspect instead of the execution of a given method
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint function,
	 * and finally the value returned by the joinpoint method call.
	 * The value returned by the advice will be the pointcut returned value.
	 *
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *        ['class_name', 'methodName']
	 * @param $advice callable the call-back call of the advice :
	 *        ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function aroundMethod(array $joinpoint, callable $advice) : IHandler;

	//-------------------------------------------------------------------------------- beforeFunction
	/**
	 * Weave an aspect before the execution of a given function
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint function.
	 * The advice can return a value : if this value is set, the execution of the joinpoint will be
	 * cancelled and the returned value replaced by this one.
	 *
	 * @param $joinpoint string the joinpoint defined like a call-back :
	 *        ['class_name', 'methodName']
	 * @param $advice callable the call-back call of the advice :
	 *        ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function beforeFunction(string $joinpoint, callable $advice) : IHandler;

	//---------------------------------------------------------------------------------- beforeMethod
	/**
	 * Weave an aspect before the execution of a given method
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint method
	 * The advice can return a value : if this value is set, the execution of the joinpoint will be
	 * cancelled and the returned value replaced by this one.
	 *
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *        ['class_name', 'methodName']
	 * @param $advice callable the call-back call of the advice :
	 *        ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function beforeMethod(array $joinpoint, callable $advice) : IHandler;

	//------------------------------------------------------------------------------- defaultFileName
	/**
	 * @return string
	 */
	public function defaultFileName() : string;

	//-------------------------------------------------------------------------------- loadJoinpoints
	/**
	 * @param $file_name string
	 */
	public function loadJoinpoints(string $file_name) : void;

	//---------------------------------------------------------------------------------- readProperty
	/**
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *        ['class_name', 'property_name']
	 * @param $advice callable the call-back call of the advice :
	 *        ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function readProperty(array $joinpoint, callable $advice) : IHandler;

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Un-weave an aspect
	 *
	 * @param $handler IHandler
	 */
	public function remove(IHandler $handler) : void;

	//-------------------------------------------------------------------------------- saveJoinpoints
	/**
	 * @param $file_name string
	 */
	public function saveJoinpoints(string $file_name) : void;

	//--------------------------------------------------------------------------------- writeProperty
	/**
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *        ['class_name', 'property_name']
	 * @param $advice callable the call-back call of the advice :
	 *        ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function writeProperty(array $joinpoint, callable $advice) : IHandler;

}
