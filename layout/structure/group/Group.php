<?php
namespace ITRocks\Framework\Layout\Structure;

use ITRocks\Framework\Layout\Structure\Field\Property;
use ITRocks\Framework\Layout\Structure\Group\Iteration;

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

	//----------------------------------------------------------------------------------- DUMP_SYMBOL
	const DUMP_SYMBOL = '>';

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
	 * Raw elements that are not $groups, $iterations nor $properties
	 *
	 * When iterations are generated, this is empty
	 *
	 * @var Element[]
	 */
	public $elements = [];

	//--------------------------------------------------------------------------------------- $groups
	/**
	 * Sub-group elements
	 *
	 * @var Group[]
	 */
	public $groups = [];

	//----------------------------------------------------------------------------------- $iterations
	/**
	 * @var Iteration[]
	 */
	public $iterations = [];

	//---------------------------------------------------------------------------------------- $links
	/**
	 * Set by Link_Groups::run : key is the structure page number, value is the same group in the page
	 *
	 * All $linked_groups are the same group in multiple pages
	 * They are all linked by reference : modify $linked_groups in a group and all the others will be
	 *
	 * @var Group[]
	 */
	public $links = [];

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var Property[]
	 */
	public $properties = [];

	//-------------------------------------------------------------------------------- $property_path
	/**
	 * The path of the property, starting from the layout model context class
	 *
	 * The final property has always a multiple type (eg Class[], string[])
	 *
	 * @var string
	 */
	public $property_path;

	//----------------------------------------------------------------------------------- allElements
	/**
	 * @return Element[]
	 */
	public function allElements()
	{
		return array_merge($this->elements, $this->groups, $this->iterations, $this->properties);
	}

	//------------------------------------------------------------------------------------------ dump
	/**
	 * @param $level integer
	 * @param $detail boolean
	 * @return string
	 */
	public function dump($level = 0, $detail = true)
	{
		if ($detail) {
			$dump = parent::dump($level) . LF;
			foreach ($this->allElements() as $element) {
				$dump .= $element->dump($level + 1) . LF;
			}
			foreach ($this->links as $link) {
				$dump .= str_repeat(SP, $level * 2 + 2) . $link->page->number
					. SP . $link->dump(0, false) . LF;
			}
			return $dump;
		}
		return parent::dump($level);
	}

}
