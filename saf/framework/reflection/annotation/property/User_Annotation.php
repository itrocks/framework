<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Reflection\Annotation\Template\List_Annotation;

/**
 * User annotation : a list of parameters concerning the accessibility of the property for the user
 */
class User_Annotation extends List_Annotation
{

	const HIDDEN    = 'hidden';
	const INVISIBLE = 'invisible';
	const READONLY  = 'readonly';

	//---------------------------------------------------------------------------------------- $value
	/**
	 * Annotation value
	 * - hidden : the property will be generated into lists or output forms, but with a 'hidden' class
	 * - invisible : the property will not be displayed into lists or output forms
	 * - readonly : the property will be displayed but will not be accessible for modification
	 *
	 * @values hidden, invisible, readonly
	 * @var string[]
	 */
	public $value;

}
