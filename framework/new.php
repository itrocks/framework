<?php
namespace SAF\Framework;

/**
 * @return User
 */
function newUser() { return Object_Builder::current()->newInstance("User"); }
