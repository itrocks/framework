<?php
namespace ITRocks\Framework\Layout\Structure;

/**
 * The base of the element is : its position, nothing else
 */
abstract class Element
{

	//---------------------------------------------------------------------------------------- $group
	/**
	 * If the element has been identified by Associate_Groups or Generate_Groups to be inside a group,
	 * it will be set here (and the element added to Group::$elements)
	 *
	 * @var Group
	 */
	public $group;

	//--------------------------------------------------------------------------------------- $height
	/**
	 * The height of the object, in mm
	 *
	 * @var float
	 */
	public $height;

	//----------------------------------------------------------------------------------------- $left
	/**
	 * The left position of the object, in mm
	 *
	 * @var float
	 */
	public $left;

	//----------------------------------------------------------------------------------------- $page
	/**
	 * The link to the page which contains the zone
	 *
	 * @mandatory
	 * @var Page
	 */
	public $page;

	//------------------------------------------------------------------------------------------ $top
	/**
	 * The top position of the object, in mm
	 *
	 * @var float
	 */
	public $top;

	//---------------------------------------------------------------------------------------- $width
	/**
	 * The width of the object, in mm
	 *
	 * @var float
	 */
	public $width;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $page Page
	 */
	public function __construct(Page $page)
	{
		$this->page = $page;
	}

	//---------------------------------------------------------------------------------------- bottom
	/**
	 * @return float
	 */
	public function bottom()
	{
		return $this->top + $this->height;
	}

	//----------------------------------------------------------------------------------- insideGroup
	/**
	 * Returns true if the element is the group, or is inside the group (with recursion)
	 *
	 * @param $group Group
	 * @return boolean
	 */
	public function insideGroup(Group $group)
	{
		return ($this === $group) || ($this->group && $this->group->insideGroup($group));
	}

	//----------------------------------------------------------------------------------------- right
	/**
	 * @return float
	 */
	public function right()
	{
		return $this->left + $this->width;
	}

}
