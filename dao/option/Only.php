<?php
namespace ITRocks\Framework\Dao\Option;

/**
 * A DAO only option, to restrict the action to the given list of property names
 */
class Only extends Properties
{
	use Has_In;

}
