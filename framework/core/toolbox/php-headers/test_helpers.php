<?php
/**
 * DO NEVER INCLUDE THIS SCRIPT
 * These are php functions prototypes for use with the test_helpers extension
 */

//--------------------------------------------------------------------------------- rename_function
/**
 * Renames the function.
 * This allows the stubbing / mocking of functions.
 *
 * @param $function_name string
 * @param $new_name      string
 */
function rename_function($function_name, $new_name) {}

//------------------------------------------------------------------------------- set_exit_overload
/**
 * Registers a callback function that is automatically invoked when exit / die is called
 *
 * @example set_exit_overload(function() { return false; });
 *          disable exit; calls
 * @example set_exit_overload(function($msg) { echo "[DIE $msg]"; return true; });
 *          format message displayed on dying
 * @param $callback string|array
 * Callback parameter : the die message, optional
 * Callback returns true to confirm that the script must end, or false to cancel the exit
 */
function set_exit_overload($callback) {}

//-------------------------------------------------------------------------------- set_new_overload
/**
 * Registers a callback function that is automatically invoked when the new operator is executed
 *
 * @param $callback string|array
 * Callback parameter : the class name string wished by the new operator
 * Callback returns : the class name string to invoke
 */
function set_new_overload($callback) {}

//----------------------------------------------------------------------------- unset_exit_overload
function unset_exit_overload() {}

//------------------------------------------------------------------------------ unset_new_overload
function unset_new_overload() {}
