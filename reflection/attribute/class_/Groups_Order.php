<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Is_List;

/**
 * #[Groups_Order('Group1', 'Group2', ...)
 *
 * Declares what is the "from the most important to the less important" order for groups
 * Group1 and so on are the identifiers of the groups existing for the class
 * groups that are not into #Groups_Order will be the least important, sorted alphabetically
 */
#[Attribute(Attribute::TARGET_CLASS), Inheritable]
class Groups_Order extends Class_
{
	use Is_List;

}
