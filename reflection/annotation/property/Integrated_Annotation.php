<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;

/**
 * An integrated property enables sub-form into main form integration
 *
 * @example '@integrated' : the object will be integrated as a sub-form, with 'field.sub_field' display
 * @example '@integrated simple' : the object will be integrated as a sub-form, with 'sub_field' display
 * @example '@integrated simple property1, property2' : the object will be integrated as a sub-form, with 'sub_field' display and will display only the specified properties
 */
class Integrated_Annotation extends List_Annotation
{

	//----------------------------------------------------------------------------------------- ALIAS
	const ALIAS = 'alias';

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'integrated';

	//----------------------------------------------------------------------------------------- BLOCK
	const BLOCK = 'block';

	//------------------------------------------------------------------------------------------ FULL
	const FULL = 'full';

	//---------------------------------------------------------------------------------------- SIMPLE
	const SIMPLE = 'simple';

	//--------------------------------------------------------------------------- $display_properties
	/**
	 * Uses to sort and display specified properties
	 *
	 * @var string[]
	 */
	public $display_properties = null;

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
			$integrated_type = self::FULL;
		}
		else {

			$i = strpos($value, ',');
			if ($i === false) {
				$i = strlen($value);
			}

			$i = strrpos(substr($value, 0, $i), SP);
			if ($i === false) {
				$i = strlen($value);
			}
			else {
				$this->display_properties = explode(',', str_replace(SP, '', substr($value, $i)));
			}

			$integrated_type = substr($value, 0, $i);

		}
		parent::__construct($integrated_type);

		if (
			$this->value
			&& !parent::has(self::SIMPLE)
			&& (parent::has(self::BLOCK) || parent::has(self::ALIAS))
		) {
				$this->value[] = self::SIMPLE;
		}

	}

}
