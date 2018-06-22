<?php
namespace ITRocks\Framework\Layout\Structure;

/**
 * A group manages repeated fields
 *
 * Groups can be configured by dropping them strictly around fields that may be repeated.
 * If a field is a property.path with multiple repetitive steps, you must use multiple groups.
 * If a property.path contains repetitive properties but has no group, an auto-group will be added.
 * When the structure is built, groups are not immediately linked to elements inside :
 * Generator\Associate_Groups does this job.
 */
class Group extends Element
{

	//---------------------------------------------------------------------------- $direction @values
	const HORIZONTAL = 'horizontal';
	const VERTICAL   = 'vertical';

	//------------------------------------------------------------------------------------ $direction
	/**
	 * @values self::const local
	 * @var string
	 */
	public $direction = self::VERTICAL;

	//------------------------------------------------------------------------------------- $elements
	/**
	 * @var Element[]
	 */
	public $elements = [];

	//-------------------------------------------------------------------------------- $property_path
	/**
	 * The path of the property, starting from the layout model context class
	 * The final property has always a multiple type (eg Class[], string[])
	 *
	 * @var string
	 */
	public $property_path;

}
