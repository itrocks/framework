<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;

/**
 * An integrated property enables sub-form into main form integration
 *
 * Generals : @integrated [alias] [block] [full|simple] [property1[, property.path2[, etc]]
 *
 * Multiple keywords can be used to tell how this sub-form will be integrated into the main form :
 * - alias : the @alias value of each property will be displayed instead for their standard display,
 * - block : the sub-form will be delimited into a <fieldset> block,
 * - full : the full property.path display will be shown,
 * - simple : the name for each property will be the final property name (or alias) alone, no path.
 *
 * full and simple cannot be set together.
 *
 * If none of full or simple are set :
 * - if there is alias or block, the sub-form will be integrated with simple property names,
 * - if there is no alias and no block, the sub-form will be integrated with full property paths.
 *
 * If a comma-separated list of property paths are given, only these properties will be shown.
 *
 * If you need only one property to be displayed, put its name alone without any comma separator.
 * If this property has a reserved name like alias, block, full or simple : append the name of the
 * property with a comma.
 *
 * @example @integrated
 * The object will be integrated as a sub-form, with 'field.sub_field' display and without field-set
 * @example @integrated simple
 * The object will be integrated as a sub-form, with 'sub_field' display and without field-set
 * @example @integrated simple property1, property2
 * The object will be integrated as a sub-form, with 'sub_field' display and without field-set,
 * only the specified property paths will be displayed
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

	//-------------------------------------------------------------------------------- RESERVED_WORDS
	const RESERVED_WORDS = [self::ALIAS, self::BLOCK, self::FULL, self::SIMPLE];

	//---------------------------------------------------------------------------------------- SIMPLE
	const SIMPLE = 'simple';

	//--------------------------------------------------------------------------- $display_properties
	/**
	 * Uses to sort and display specified properties
	 *
	 * @var string[]
	 */
	public $display_properties = [];

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
		if (isset($value)) {
			if ($value) {
				$excluded = [];
				$values   = [];
				foreach (explode(SP, $value) AS $element) {
					if (
						strpos($element, ',')
						|| in_array($element, $excluded)
						|| in_array($element, $values)
						|| !in_array($element, self::RESERVED_WORDS)
					) {
						$this->display_properties[] = trim(lParse($element, ','));
					}
					else {
						$element  = trim($element);
						$values[] = $element;
						if (in_array($element, [self::FULL, self::SIMPLE])) {
							$excluded = [self::FULL, self::SIMPLE];
						}
					}
				}
				$value = join(',', $values);
			}
			else {
				$value = self::FULL;
			}
		}

		parent::__construct($value);

		if (
			$this->value
			&& !(static::has(self::FULL) || static::has(self::SIMPLE))
			&& (static::has(self::ALIAS) || static::has(self::BLOCK))
		) {
			$this->value[] = self::SIMPLE;
		}

	}

}
