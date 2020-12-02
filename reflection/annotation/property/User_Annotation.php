<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;

/**
 * User annotation : a list of parameters concerning the accessibility of the property for the user
 */
class User_Annotation extends List_Annotation
{

	//-------------------------------------------------------------------------- Constants for $value
	const ADD_ONLY         = 'add_only';
	const HIDDEN           = 'hidden';
	const HIDE_EDIT        = 'hide_edit';
	const HIDE_EMPTY       = 'hide_empty';
	const HIDE_OUTPUT      = 'hide_output';
	const IF_EMPTY         = 'if_empty';
	const INVISIBLE        = 'invisible';
	const INVISIBLE_EDIT   = 'invisible_edit';
	const INVISIBLE_OUTPUT = 'invisible_output';
	const NO_ADD           = 'no_add';
	const NO_DELETE        = 'no_delete';

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'user';

	//-------------------------------------------------------------------------------------- READONLY
	/**
	 * read-only : displayed into an <input readonly> : you will be able to use it with js but
	 * beware of performances if you have got a lot of data
	 */
	const READONLY = 'readonly';

	//------------------------------------------------------------------------------ STRICT_READ_ONLY
	/**
	 * Strict read-only : displayed without <input name=...> : you will not be able to use it with js
	 * but you will get better performance if you have got a lot of data
	 */
	const STRICT_READ_ONLY = 'strict_read_only';

	//--------------------------------------------------------------------------------------- TOOLTIP
	const TOOLTIP = 'tooltip';

	//-------------------------------------------------------------------------------- NOT_MODIFIABLE
	const NOT_MODIFIABLE = [
		self::ADD_ONLY, self::HIDDEN, self::HIDE_EDIT, self::INVISIBLE, self::INVISIBLE_EDIT,
		self::READONLY, self::STRICT_READ_ONLY
	];

	//---------------------------------------------------------------------------------------- $value
	/**
	 * Annotation value
	 * - hidden : the property will be generated into lists or output forms, but with a 'hidden' class
	 * - hide_empty : the property will not be displayed into output views if value is empty,
	 *   but will be still visible into edit views
	 * - invisible : the property will not be displayed (nor exist) into lists, output forms, property
	 *   selector, etc. any user template
	 * - readonly : the property will be displayed but will not be accessible for modification
	 * Value that only works for basic type (not supported on collection and map)
	 * - if_empty : the property will be displayed editable if value is empty, read_only otherwise
	 *   notes: incompatible with @user_default, incompatible with "hide_empty" value.
	 * Value that only works for collection/map
	 * - no_add : forbids user to add a new element
	 * - no_delete : forbids user to delete any element
	 *
	 * @todo readonly should be implicitly set when @read_only is enabled
	 * @todo readonly should be replaced by two-words read_only
	 * @values hidden, hide_empty, if_empty, invisible, no_add, no_delete, readonly
	 * @var string[]
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 */
	public function __construct($value)
	{
		parent::__construct($value);
		$this->validate();
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds a value to the annotation list of values
	 *
	 * @param $value string
	 * @return boolean
	 */
	public function add($value)
	{
		parent::add($value);
		return $this->validate();
	}

	//---------------------------------------------------------------------------------- isModifiable
	public function isModifiable() : bool
	{
		return !array_intersect($this->value, static::NOT_MODIFIABLE);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove a value and return true if the value was here and removed, false if the value
	 * already was not here
	 *
	 * @param $value string
	 * @return boolean
	 */
	public function remove($value)
	{
		return parent::remove($value) && $this->validate();
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * Check that values list are valid
	 *
	 * @return boolean
	 */
	protected function validate()
	{
		if ($this->has(self::HIDE_EMPTY) && $this->has(self::IF_EMPTY)) {
			trigger_error(
				self::IF_EMPTY . ' and ' . self::HIDE_EMPTY . ' values are incompatible',
				E_USER_ERROR
			);
			return false;
		}
		return true;
	}

}
