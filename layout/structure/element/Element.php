<?php
namespace ITRocks\Framework\Layout\Structure;

/**
 * The base of the element is : its position, nothing else
 */
abstract class Element
{

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

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $page Page
	 */
	public function __construct(Page $page)
	{
		$this->page = $page;
	}

}
