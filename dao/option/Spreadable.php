<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Dao\Option;

/**
 * Spreadable options are applied to the written object, and to all calls to Dao::write()
 * for its linked objects (collections, maps, objects)
 */
interface Spreadable extends Option
{

}
