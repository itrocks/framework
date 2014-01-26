<?php
// TODO finish transcription of the PHP documentation from http://www.php.net/manual/en/ref.runkit

const RUNKIT_ACC_PRIVATE = 2;

const RUNKIT_ACC_PROTECTED = 1;

const RUNKIT_ACC_PUBLIC = 0;

//----------------------------------------------------------------------------- runkit_function_add
/**
 * Add a new function, similar to create_function()
 *
 * @param $funcname string Name of function to be created
 * @param $arglist  string Comma separated argument list
 * @param $code     string Code making up the function
 * @return boolean returns TRUE on success or FALSE on failure
 * @see create_function()
 */
function runkit_function_add($funcname ,$arglist, $code) {}

//-------------------------------------------------------------------------- runkit_function_rename
/**
 * Change a function's name
 *
 * Note: By default, only userspace functions may be removed, renamed, or modified.
 * In order to override internal functions, you must enable the runkit.internal_override setting in
 * php.ini.
 *
 * @param $function_name string Current function name
 * @param $new_name      string New function name
 * @return boolean Returns TRUE on success or FALSE on failure
 */
function runkit_function_rename($function_name, $new_name) {}

//------------------------------------------------------------------------------- runkit_method_add
/**
 * Dynamically adds a new method to a given class
 *
 * @param $class_name  string The class to which this method will be added
 * @param $method_name string The name of the method to add
 * @param $args        string Comma-delimited list of arguments for the newly-created method
 * @param $code        string The code to be evaluated when methodname is called
 * @param $flags       integer The type of method to create, can be RUNKIT_ACC_PUBLIC,
 *        RUNKIT_ACC_PROTECTED or RUNKIT_ACC_PRIVATE
 *        Note : This parameter is only used as of PHP 5, because, prior to this, all methods were
 *        public
 * @return boolean Returns TRUE on success or FALSE on failure
 */
function runkit_method_add($class_name, $method_name, $args, $code, $flags = RUNKIT_ACC_PUBLIC) {}

//---------------------------------------------------------------------------- runkit_method_rename
/**
 * Dynamically changes the name of the given method
 *
 * Note: This function cannot be used to manipulate the currently running (or chained) method.
 *
 * @param $class_name  string The class in which to rename the method
 * @param $method_name string The name of the method to rename
 * @param $new_name    string The new name to give to the renamed method
 * @return boolean Returns TRUE on success or FALSE on failure
 */
function runkit_method_rename($class_name, $method_name, $new_name) {}
