<?php
namespace ITRocks\Framework\Layout\Structure;

use ITRocks\Framework\Layout\Structure\Draw\Snap_Line;
use ITRocks\Framework\Layout\Structure\Field\Property;

/**
 * A structured page
 */
class Page
{

	//----------------------------------------------------------- page position information constants
	/**
	 * It is independent but must be the same special values than Model\Page constants
	 */
	const ALL    = 'A';
	const FIRST  = '1';
	const LAST   = '-1';
	const MIDDLE = '0';

	//------------------------------------------------------------------------ ALL_ELEMENT_PROPERTIES
	const ALL_ELEMENT_PROPERTIES = ['elements', 'groups', 'properties'];

	//------------------------------------------------------------------------------------- $elements
	/**
	 * All elements in page but those that are into $groups or $properties
	 *
	 * @var Element[]
	 */
	public $elements = [];

	//--------------------------------------------------------------------------------------- $groups
	/**
	 * Group elements
	 *
	 * @var Group[]
	 */
	public $groups = [];

	//--------------------------------------------------------------------------------------- $height
	/**
	 * @var float
	 */
	public $height = 297;

	//--------------------------------------------------------------------------------------- $number
	/**
	 * Page number
	 *
	 * '-*' and 'A' are temporary page numbers from the source structure :
	 * they will be replaced by final pages during the generation process.
	 *
	 * @signed
	 * @var integer|string
	 */
	public $number;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * Property elements
	 *
	 * @var Property[]
	 */
	public $properties = [];

	//---------------------------------------------------------------------------------------- $width
	/**
	 * @var float
	 */
	public $width = 210;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->dump();
	}

	//---------------------------------------------------------------------------------- allButGroups
	/**
	 * @return Element[]
	 */
	public function allButGroups()
	{
		return array_merge($this->elements, $this->properties);
	}

	//----------------------------------------------------------------------------------- allElements
	/**
	 * @return Element[]
	 */
	public function allElements()
	{
		return array_merge($this->elements, $this->groups, $this->properties);
	}

	//------------------------------------------------------------------------------------------ dump
	/**
	 * @return string
	 */
	public function dump()
	{
		$dump = '########## ' . $this->number . LF . LF;
		foreach ($this->allElements() as $element) {
			$dump .= $element->dump() . LF;
		}
		return $dump;
	}

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * @return boolean true if the page contains no visible elements
	 */
	public function isEmpty()
	{
		if (!$this->elements) {
			return true;
		}
		foreach ($this->elements as $element) {
			if (!(
				($element instanceof Snap_Line)
				|| ($element instanceof Group)
			)) {
				return false;
			}
		}
		return true;
	}

}
