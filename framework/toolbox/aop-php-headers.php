<?php
namespace SAF\Framework;

/**
 * DO NEVER INCLUDE THIS SCRIPT
 * These are headers for AOP-PHP phpdocumentor and Eclipse / Zend Studio auto-completion.
 */

/**
 * after a given call, may it be function, method or property access (read / write)
 */
const AOP_KIND_AFTER = 4;

/**
 * after a function call (not a method call)
 */
const AOP_KIND_AFTER_FUNCTION = 132;

/**
 * after a method call (method of an object)
 */
const AOP_KIND_AFTER_METHOD = 68;

/**
 * after a property (read or write)
 */
const AOP_KIND_AFTER_PROPERTY = 36;

/**
 * after a property access (read only)
 */
const AOP_KIND_AFTER_READ_PROPERTY = 44;

/**
 * after a property write (write only)
 */
const AOP_KIND_AFTER_WRITE_PROPERTY = 52;

/**
 * around a given call, may it be function, method or property access (read / write)
 */
const AOP_KIND_AROUND = 1;

/**
 * around a function call (not a method call)
 */
const AOP_KIND_AROUND_FUNCTION = 129;

/**
 * around a method call (method of an object)
 */
const AOP_KIND_AROUND_METHOD = 65;

/**
 * around a property (read or write)
 */
const AOP_KIND_AROUND_PROPERTY = 33;

/**
 * around a property access (read only)
 */
const AOP_KIND_AROUND_READ_PROPERTY = 41;

/**
 * around a property write (write only)
 */
const AOP_KIND_AROUND_WRITE_PROPERTY = 49;

/**
 * before a given call, may it be function, method or property access (read / write)
 */
const AOP_KIND_BEFORE = 2;

/**
 * before a function call (not a method call)
 */
const AOP_KIND_BEFORE_FUNCTION = 130;

/**
 * before a method call (method of an object)
 */
const AOP_KIND_BEFORE_METHOD = 1;

/**
 * before a property (read or write)
 */
const AOP_KIND_BEFORE_PROPERTY = 34;

/**
 * before a property access (read only)
 */
const AOP_KIND_BEFORE_READ_PROPERTY = 42;

/**
 * before a property write (write only)
 */
const AOP_KIND_BEFORE_WRITE_PROPERTY = 50;

/**
 * on an exception catch
 */
const AOP_KIND_CATCH = 256;

/**
 * on a function call
 */
const AOP_KIND_FUNCTION = 128;

/**
 * on a method call
 */
const AOP_KIND_METHOD = 64;

/**
 * on a property action
 */
const AOP_KIND_PROPERTY = 32;

/**
 * on a read
 */
const AOP_KIND_READ = 8;

/**
 * on a function / method return
 */
const AOP_KIND_RETURN = 512;

/**
 * on a write
 */
const AOP_KIND_WRITE = 16;

/**
 * An instance of AopJoinPoint will always be passed to your advices.
 * This object contains several informations, such as the pointcut who triggered the joinpoint,
 * the arguments, the returned value (if available), the raised exception (if available), and will
 * enables you to run the expected method in case you are "around" it.
 *
 * @link https://github.com/AOP-PHP/AOP/blob/master/doc/Contents/chapter2.md#aopjoinpoint-complete-reference
 */
class AopJoinPoint
{

	//---------------------------------------------------------------------------------- getArguments
	/**
	 * Return the triggering method arguments as an indexed array.
	 *
	 * The resulting array will give values when the triggering method expected values, and
	 * references when the triggering method expected references.
	 *
	 * @return array Indexes will be argument numbers (0..n), values are the arguments values.
	 */
	public function getArguments() {}

 	//------------------------------------------------------------------------------ getAssignedValue
 	/**
 	 * Returns the value assigned to the property of the triggered joinpoint.
 	 *
 	 * If the joinpoint was triggered by a method operation, will raise an error.
 	 * If the joinpoint was triggered by a read operation, will also raise an error.
 	 *
 	 * @return mixed
 	 */
 	public function  getAssignedValue() {}

	//---------------------------------------------------------------------------------- getClassName
	/**
	 * Returns the object's class name of the triggered joinpoint.
	 *
	 * If the joinpoint does not belongs to a class, getClassName returns null.
	 *
	 * If the class is declared in a namespace, getClassName indicates the full name of
	 * the class (with the namespace).
	 *
	 * @return string
	 */
	public function getClassName() {}

	//------------------------------------------------------------------------------- getFunctionName
	/**
	 * Returns the name of the function of the triggered joinpoint.
	 *
	 * If the joinpoint was triggered by a property operation, will raise an error.
	 * If the joinpoint was triggered by a method operation, will raise an error.
	 *
	 * @return string
	 */
	public function  getFunctionName() {}

	//------------------------------------------------------------------------------- getKindOfAdvice
	/**
	 * This will tell in which condition your advice was launched
	 *
	 * Returned value may have any AOP_KIND_* constant value
	 *
	 * @return constant
	 */
	public function getKindOfAdvice() {}

	//--------------------------------------------------------------------------------- getMethodName
	/**
	 * Returns the name of the method of the triggered joinpoint.
	 *
	 * If the joinpoint was triggered by a property operation, will raise an error.
	 * If the joinpoint was triggered by a function operation, will raise an error.
	 *
	 * @return string
	 */
	public function getMethodName() {}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Returns the object of the triggered joinppoint.
	 *
	 * If the joinpoint does not belongs to an object, getObject returns null.
	 *
	 * @return object
	 */
	public function getObject() {}

	//----------------------------------------------------------------------------------- getPointcut
	/**
	 * Returns the pointcut (as a string) that triggered the joinpoint.
	 *
	 * @return string
	 */
	public function getPointcut() {}

	//------------------------------------------------------------------------------- getPropertyName
	/**
	 * Returns the name of the triggering property.
	 *
	 * If the joinpoint was triggered by a method operation, will raise an error.
	 *
	 * @return string
	 */
	public function getPropertyName() {}

	//------------------------------------------------------------------------------ getReturnedValue
	/**
	 * Will give you the returned value of the triggering method.
	 *
	 * Will only be populated in advices of the kind "after". In every other kind of advices,
	 * getReturnedValue will be null.
	 *
	 * If the triggering method returns a reference and you want to update the given reference, you
	 * will have to explicitely asks for the reference while calling getReturnedValue.
	 *
	 * @return mixed The triggering method returned value.
	 */
	public function getReturnedValue() {}

	//--------------------------------------------------------------------------------------- process
	/**
	 * Explicitely launch the triggering method or property operation (read / write).
	 *
	 * The process method will only be available for advices of kind around. Any call to process in
	 * advices of other kinds will launch an AopException with a message like "Cannot launch the
	 * process method in an advice of kind XXX".
	 *
	 * @throws AopException
	 */
	public function process() {}

	//---------------------------------------------------------------------------------- setArguments
	/**
	 * Enables you to replace all the arguments the triggering method will receive.
	 *
	 * Beware that if you want to keep references, you will have to explicitely pass them back
	 * to setArguments.
	 *
	 * @param array $new_args Indexes must be argument number (0..n), values the new arguments values.
	 */
	public function setArguments($new_args) {}

	//------------------------------------------------------------------------------ setReturnedValue
	/**
	 * Enables you to define the resulting value of the triggering method.
	 *
	 * This function makes sense for advices of kind after, around, exception and final.
	 *
	 * If you are assigning a returned value to a method that was expected to return a reference,
	 * the original reference will be lost and won't be replaced. To replace the content of an
	 * original reference, just proceed as explained in the getReturnedValue() documentation.
	 *
	 * @param mixed $new_returned_value The new triggering method returned value.
	 */
	public function setReturnedValue($new_returned_value) {}

}

//----------------------------------------------------------------------------------- aop_add_after
/**
 * Launch advice $call_back after the execution of the joinpoint function $function
 *
 * @param string $function can be "functionName()" or "Class_Name->methodName()"
 *   or "Class_Name->property_name". May contain joker * characters or be prefixed by NameSpace\.
 * @param mixed  $call_back string(function name) or array(class name or object, method)
 *   or function as a closure
 */
function aop_add_after($function, $call_back) {}

//------------------------------------------------------------------------- aop_add_after_returning
/**
 * Launch advice $call_back after the joinpoint function $function calls return
 *
 * @param string $function can be "functionName()" or "Class_Name->methodName()"
 *   or "Class_Name->property_name". May contain joker * characters or be prefixed by NameSpace\.
 * @param mixed  $call_back string(function name) or array(class name or object, method)
 *   or function as a closure
 */
function aop_add_after_returning($function, $call_back) {}

//-------------------------------------------------------------------------- aop_add_after_throwing
/**
 * Launch advice $call_back after the joinpoint function $function throws an exception
 *
 * @param string $function can be "functionName()" or "Class_Name->methodName()"
 *   or "Class_Name->property_name". May contain joker * characters or be prefixed by NameSpace\.
 * @param mixed  $call_back string(function name) or array(class name or object, method)
 *   or function as a closure
 */
function aop_add_after_throwing($function, $call_back) {}

//---------------------------------------------------------------------------------- aop_add_around
/**
 * Launch advice $call_back after the execution of the joinpoint function $function
 *
 * @param string $function can be "functionName()" or "Class_Name->methodName()"
 *   or "Class_Name->property_name". May contain joker * characters or be prefixed by NameSpace\.
 * @param mixed  $call_back string(function name) or array(class name or object, method)
 *   or function as a closure
 */
function aop_add_around($function , $call_back) {}

//---------------------------------------------------------------------------------- aop_add_before
/**
 * Launch advice $call_back as a replacement of the execution of the joinpoint function $function
 *
 * @param string $function can be "functionName()" or "Class_Name->methodName()"
 *   or "Class_Name->property_name". May contain joker * characters or be prefixed by NameSpace\.
 * @param mixed  $call_back string(function name) or array(class name or object, method)
 *   or function as a closure
 */
function aop_add_before($function, $call_back) {}
