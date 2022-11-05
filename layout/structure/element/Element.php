<?php
namespace ITRocks\Framework\Layout\Structure;

use ITRocks\Framework\Layout\Structure\Group\Iteration;

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
	 *
	 * @var ?Group
	 */
	public ?Group $group;

	//--------------------------------------------------------------------------------------- $height
	/**
	 * The height of the object, in mm
	 *
	 * @var float
	 */
	public float $height;

	//------------------------------------------------------------------------------------ $iteration
	/**
	 * If the element has been associated to an iteration of a group, this iteration will be set here
	 *
	 * @var ?Iteration
	 */
	public ?Iteration $iteration;

	//----------------------------------------------------------------------------------------- $left
	/**
	 * The left position of the object, in mm
	 *
	 * @var float
	 */
	public float $left;

	//----------------------------------------------------------------------------------------- $page
	/**
	 * The link to the page which contains the zone
	 *
	 * @mandatory
	 * @var Page
	 */
	public Page $page;

	//------------------------------------------------------------------------------------------ $top
	/**
	 * The top position of the object, in mm
	 *
	 * @var float
	 */
	public float $top;

	//---------------------------------------------------------------------------------------- $width
	/**
	 * The width of the object, in mm
	 *
	 * @var float
	 */
	public float $width;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $page Page
	 */
	public function __construct(Page $page)
	{
		$this->page = $page;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->dump();
	}

	//---------------------------------------------------------------------------------------- bottom
	/**
	 * @return float
	 */
	public function bottom() : float
	{
		return $this->top + $this->height;
	}

	//------------------------------------------------------------------------------ cloneWithContext
	/**
	 * @param $page      Page
	 * @param $group     Group|null
	 * @param $iteration Iteration|null
	 * @return static
	 */
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
	/**
	 * @param $level integer
	 * @return string
	 */
	public function dump(int $level = 0) : string
	{
		$dump_symbol = (($level > -1) ? (static::DUMP_SYMBOL . SP) : '');
		return str_repeat(SP, max(0, $level) * 2) . $dump_symbol . get_class($this) . ' : '
			. $this->left . ', ' . $this->top . ' - ' . $this->width . ', ' . $this->height;
	}

	//------------------------------------------------------------------------------------------ hotX
	/**
	 * @return float
	 */
	public function hotX() : float
	{
		return $this->left;
	}

	//----------------------------------------------------------------------------------- insideGroup
	/**
	 * Returns true if the element is the group, or is inside the group (with recursion)
	 *
	 * @param $group Group
	 * @return boolean
	 */
	public function insideGroup(Group $group) : bool
	{
		return ($this === $group) || ($this->group && $this->group->insideGroup($group));
	}

	//----------------------------------------------------------------------------------------- right
	/**
	 * @return float
	 */
	public function right() : float
	{
		return $this->left + $this->width;
	}

}
