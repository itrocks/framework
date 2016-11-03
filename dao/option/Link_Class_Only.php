<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Dao\Option;

/**
 * Set this option to write link class data only.
 *
 * For an object which class has a @link annotation : only properties data from the link class
 * are written.
 *
 * For an object which class has no @link annotation : all proporties are written.
 *
 * This is used internally by Data_Links to avoid writing linked class data of link objects
 * collection and map.
 *
 * Developers can use this for their particular cases.
 */
class Link_Class_Only implements Option
{

}
