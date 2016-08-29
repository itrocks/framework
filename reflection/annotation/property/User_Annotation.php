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
	const NO_ADD     = 'no_add';
	const NO_DELETE  = 'no_delete';

	//---------------------------------------------------------------------------------------- $value
	/**
	 * Annotation value
	 * - hidden : the property will be generated into lists or output forms, but with a 'hidden' class
	 * - hide_empty : the property will not be displayed into output views if value is empty,
	 *   but will be still visible into edit views
	 * - invisible : the property will not be displayed (nor exist) into lists, output forms, property
	 *   selector, etc. any user template
	 * - readonly : the property will be displayed but will not be accessible for modification
	 * only works for collection/map
	 * - no_add : forbids user to add a new element
	 * - no_delete : forbids user to delete any element
	 *
	 * @todo readonly should be implicitly set when @read_only is enabled
	 * @values hidden, hide_empty, invisible, readonly, no_add, no_delete
	 * @var string[]
	 */
	public $value;

}
