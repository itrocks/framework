<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\Options_Properties_Annotation;

/**
 * An integrated property enables sub-form into main form integration
 *
 * Generals : @integrated [alias] [block] [full|simple] [property1[, property.path2[, etc]]
 *
 * Multiple keywords can be used to tell how this sub-form will be integrated into the main form :
 * - alias : the #Alias value of each property will be displayed instead for their standard display,
 * - block : the sub-form will be delimited into a <fieldset> block,
 * - final : will not be @integrated if already into a @integrated property,
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
class Integrated_Annotation extends Options_Properties_Annotation
{

	//------------------------------------------------------------------------------------ my options
	const ALIAS  = 'alias';
	const BLOCK  = 'block';
	const FINAL_ = 'final';
	const FULL   = 'full';
	const PARENT = 'parent';
	const SIMPLE = 'simple';

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'integrated';

	//------------------------------------------------------------------------------- DEFAULT_OPTIONS
	const DEFAULT_OPTIONS = [self::FULL];

	//------------------------------------------------------------------------------ EXCLUDED_OPTIONS
	const EXCLUDED_OPTIONS = [[self::FULL, self::SIMPLE]];

	//-------------------------------------------------------------------------------- RESERVED_WORDS
	const RESERVED_WORDS = [
		self::ALIAS, self::BLOCK, self::FINAL_, self::FULL, self::PARENT, self::SIMPLE
	];

	//----------------------------------------------------------------------------------- __construct
	/** Can be empty (eq full) contain 'full', 'simple', 'block' (implicitly 'simple') */
	public function __construct(?string $value)
	{
		parent::__construct($value);

		if (
			$this->value
			&& !(static::has(self::FULL) || static::has(self::SIMPLE))
			&& (static::has(self::ALIAS) || static::has(self::BLOCK) || static::has(self::PARENT))
		) {
			$this->value[] = self::SIMPLE;
		}
	}

}
