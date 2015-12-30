<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Reflection\Annotation\Template\List_Annotation;

/**
 * User annotation : a list of parameters concerning the accessibility of the property for the user
 */
class User_Annotation extends List_Annotation
{

	const ANNOTATION = 'user';
	const HIDDEN     = 'hidden';
	const HIDE_EMPTY = 'hide_empty';
	const INVISIBLE  = 'invisible';
	const READONLY   = 'readonly';

	//---------------------------------------------------------------------------------------- $value
	/**
	 * Annotation value
	 * - hidden : the property will be generated into lists or output forms, but with a 'hidden' class
	 * - hide_empty : the property will not be displayed into output views if value is empty,
	 *   but will be still visible into edit views
	 * - invisible : the property will not be displayed into lists or output forms
	 * - readonly : the property will be displayed but will not be accessible for modification
	 *
	 * @todo readonly should be implicitly set when @read_only is enabled
	 * @values hidden, hide_empty, invisible, readonly
	 * @var string[]
	 */
	public $value;

}
