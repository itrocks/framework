<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Dao\Option;

/**
 * When Dao::write() is called, this option force to add the object instead of updating it if
 * the object already has an object identifier
 */
class Add implements Option
{

}
