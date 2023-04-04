<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Is_List;

/**
 * Identifies a list of property that are the unique tuple of data that identify a record.
 * Used with @link classes to allow the same object multiple times with different link property
 * values (ie a client can have the same contract several times, with different dates)
 */
#[Attribute(Attribute::TARGET_CLASS), Inheritable]
class Unique extends Class_
{
	use Is_List;

}
