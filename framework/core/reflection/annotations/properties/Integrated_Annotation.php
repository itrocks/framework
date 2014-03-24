<?php
namespace SAF\Framework;

/**
 * An integrated property enables sub-form into main form integration
 *
 * @example '@integrated' : the object will be integrated as a sub-form, with 'field.sub_field' display
 * @example '@integrated simple' : the object will be integrated as a sub-form, with 'sub_field' display
 */
class Integrated_Annotation extends List_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Default value is 'full' when no value is given
	 *
	 * Can be empty (eq full) contain 'full', 'simple', 'block' (implicitly 'simple')
	 *
	 * @param $value string
	 * @see List_Annotation::__construct()
	 */
	public function __construct($value)
	{
		if (isset($value) && empty($value)) {
			$value = 'full';
		}
		parent::__construct($value);
		if ($this->value && !parent::has('simple') && (parent::has('block') || parent::has('alias'))) {
				$this->value[] = 'simple';
			}
	}

}
