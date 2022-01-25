<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template;

/**
 * List annotation : on a property, tells what calculation on this property are useful to display on
 * lists
 */
class List_Annotation extends Template\List_Annotation
{

	//--------------------------------------------------------------------------------------- AVERAGE
	const AVERAGE = 'average';

	//------------------------------------------------------------------------------------------- SUM
	const SUM = 'sum';

}
