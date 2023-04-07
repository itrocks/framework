<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Is_List;

/**
 * List annotation : on a property, tells what calculation on this property are useful to display on
 * lists
 */
#[Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class List_
{
	use Common;
	use Is_List;

	//--------------------------------------------------------------------------------------- AVERAGE
	const AVERAGE = 'average';

	//------------------------------------------------------------------------------------------- SUM
	const SUM = 'sum';

}
