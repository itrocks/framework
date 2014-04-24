<?php
namespace SAF\Framework\Reflection\Annotation\Class_;

use SAF\Framework\Builder;
use SAF\Framework\PHP;
use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Annotation\Template\Types_Annotation;

/**
 * This tells that the class is a link class
 *
 * It means that :
 * - it's data storage set naming will be appended by a '_links'
 * - there will be no data storage field creation for parent linked table into this data storage set
 *   but a link field
 *
 * @example '@link User' means that the inherited class of User is linked to the parent class User
 * - data storage fields will be those from this class, and immediate parent classes if they are not 'User'
 * - an additional implicit data storage field will link to the class 'User'
 */
class Link_Annotation extends Annotation
{
	use Types_Annotation;

}
