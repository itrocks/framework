<?php
namespace ITRocks\Framework\Layout\Structure;

use ITRocks\Framework\Layout\Structure\Group\Iteration;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;

/**
 * The base of the element is : its position, nothing else
 */
abstract class Element
{

	//----------------------------------------------------------------------------------- DUMP_SYMBOL
	const DUMP_SYMBOL = '-';

	//---------------------------------------------------------------------------------------- $group
	/**
	 * If the element has been identified by Associate_Groups or Generate_Groups to be inside a group,
	 * it will be set here (and the element added to Group::$elements)
	 */
	public ?Group $group = null;

	//--------------------------------------------------------------------------------------- $height
	/** The height of the object, in mm */
	public float $height = .0;

	//------------------------------------------------------------------------------------ $iteration
	/** If the element has been associated to a group iteration, this iteration will be set here */
	public ?Iteration $iteration;

	//----------------------------------------------------------------------------------------- $left
	/** The left position of the object, in mm */
	public float $left;

	//----------------------------------------------------------------------------------------- $page
	/** @var Page The link to the page which contains the zone */
	#[Mandatory]
	public Page $page;

	//------------------------------------------------------------------------------------------ $top
	/** The top position of the object, in mm */
	public float $top;

	//---------------------------------------------------------------------------------------- $width
	/** The width of the object, in mm */
	public float $width = .0;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(Page $page)
	{
		$this->page = $page;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->dump();
	}

	//---------------------------------------------------------------------------------------- bottom
	public function bottom() : float
	{
		return $this->top + $this->height;
	}

	//------------------------------------------------------------------------------ cloneWithContext
	public function cloneWithContext(Page $page, Group $group = null, Iteration $iteration = null)
		: static
	{
		$element            = clone $this;
		$element->page      = $page;
		$element->group     = $group;
		$element->iteration = $iteration;
		return $element;
	}

	//------------------------------------------------------------------------------------------ dump
	public function dump(int $level = 0) : string
	{
		$dump_symbol = (($level > -1) ? (static::DUMP_SYMBOL . SP) : '');
		return str_repeat(SP, max(0, $level) * 2) . $dump_symbol . get_class($this) . ' : '
			. $this->left . ', ' . $this->top . ' - ' . $this->width . ', ' . $this->height;
	}

	//------------------------------------------------------------------------------------------ hotX
	public function hotX() : float
	{
		return $this->left;
	}

	//----------------------------------------------------------------------------------- insideGroup
	/** Returns true if the element is the group, or is inside the group (with recursion) */
	public function insideGroup(Group $group) : bool
	{
		return ($this === $group) || ($this->group && $this->group->insideGroup($group));
	}

	//----------------------------------------------------------------------------------------- right
	public function right() : float
	{
		return $this->left + $this->width;
	}

}
