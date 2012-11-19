<?php
namespace SAF\Framework;

//----------------------------------------------------------------------------------------- newUser
/**
 * Build a User object, optionnaly with it's login and password initialization
 *
 * @param string $login
 * @param string $password
 */
function newUser($login = "", $password = "")
{
	return Object_Builder::newInstanceArgs("User", array($login, $password));
}
	

