<?php
namespace ITRocks\Framework\Dao\Option;

/**
 * Pre-load objects from the data storage during the query
 *
 * For optimization purpose : this allows to get multiple linked objects in only one query.
 */
class Pre_Load extends Properties
{
	use Has_In;

}
