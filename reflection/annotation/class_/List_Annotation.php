<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation\Template\Options_Properties_Annotation;

/**
 * Class annotation @list [lock] property1[, property2[, etc]]
 *
 * Indicates which property we want by default for the dataList controller on the class
 * If lock is set, the user can not customize its list by adding / removing columns
 */
class List_Annotation extends Options_Properties_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'list';

	//------------------------------------------------------------------------------------ my options
	const LOCK = 'lock';

	//-------------------------------------------------------------------------------- RESERVED_WORDS
	const RESERVED_WORDS = [self::LOCK];

}
