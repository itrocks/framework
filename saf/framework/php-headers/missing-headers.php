<?php
/**
 * DO NEVER INCLUDE THIS SCRIPT
 * These are php standard functions headers missing from Eclipse / Zend Studio / PhpStorm standard
 * phpdoc.
 */

/**
 * Decodes a gzip compressed string
 *
 * @link http://www.php.net/manual/en/function.gzdecode.php
 *
 * @param $data   string  The data to decode, encoded by gzencode
 * @param $length integer [optional] The maximum length of data to decode.
 * @return string The decoded string, or false if an error occured.
 */
function gzdecode($data, $length = null) {}

/**
 * (PHP 4 &gt;= 4.2.0, PHP 5)
 * Checks if the object is of this class or has this class as one of its parents
 *
 * @link http://php.net/manual/en/function.is-a.php
 *
 * @param $object     object|string The tested object or class name
 * @param $class_name string  The class name
 * @param boolean $allow_string [optional]
 *        If this parameter set to FALSE, string class name as object is not allowed.
 *        This also prevents from calling autoloader if the class does not exist.
 * @return boolean TRUE if the object is of this class or has this class as one of its parents,
 *         FALSE otherwise.
 */
function is_a($object, $class_name, $allow_string = false) {}
