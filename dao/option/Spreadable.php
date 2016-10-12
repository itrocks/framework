<?php
namespace SAF\Framework\Dao\Option;

use SAF\Framework\Dao\Option;

/**
 * Spreadable options are applied to the written object, and to all calls to Dao::write()
 * for its linked objects (collections, maps, objects)
 */
interface Spreadable extends Option
{

}
