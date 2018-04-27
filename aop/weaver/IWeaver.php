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
	public function afterFunction($joinpoint, $advice);

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
	 *        ['class_name', 'methodName']
	 * @param $advice callable the call-back call of the advice :
	 *        ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function afterMethod($joinpoint, $advice);

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
	public function aroundFunction($joinpoint, $advice);

	//---------------------------------------------------------------------------------- aroundMethod
	/**
	 * Weave an aspect instead of the execution of a given method
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint function,
	 * and finally the value returned by the joinpoint method call.
	 * The value returned by the advice will be the pointcut returned value.
	 *
	 * @param $joinpoint callable the joinpoint defined like a call-back :
	 *        ['class_name', 'methodName']
	 * @param $advice callable the call-back call of the advice :
	 *        ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function aroundMethod($joinpoint, $advice);

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
	public function beforeFunction($joinpoint, $advice);

	//---------------------------------------------------------------------------------- beforeMethod
	/**
	 * Weave an aspect before the execution of a given method
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint method
	 * The advice can return a value : if this value is set, the execution of the joinpoint will be
	 * cancelled and the returned value replaced by this one.
	 *
	 * @param $joinpoint callable the joinpoint defined like a call-back :
	 *        ['class_name', 'methodName']
	 * @param $advice callable the call-back call of the advice :
	 *        ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function beforeMethod($joinpoint, $advice);

	//-------------------------------------------------------------------------------- loadJoinpoints
	/**
	 * @param $file_name string
	 */
	public function loadJoinpoints($file_name);

	//---------------------------------------------------------------------------------- readProperty
	/**
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *        ['class_name', 'property_name']
	 * @param $advice callable the call-back call of the advice :
	 *        ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function readProperty(array $joinpoint, $advice);

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Un-weave an aspect
	 *
	 * @param $handler IHandler
	 */
	public function remove(IHandler $handler);

	//-------------------------------------------------------------------------------- saveJoinpoints
	/**
	 * @param $file_name string
	 */
	public function saveJoinpoints($file_name);

	//--------------------------------------------------------------------------------- writeProperty
	/**
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *        ['class_name', 'property_name']
	 * @param $advice callable the call-back call of the advice :
	 *        ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 * @return IHandler
	 */
	public function writeProperty(array $joinpoint, $advice);

}
