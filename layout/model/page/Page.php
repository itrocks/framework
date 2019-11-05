<?php
namespace ITRocks\Framework\Layout\Model;

use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Layout\Model;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Tools\Has_Ordering;
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\View;

/**
 * A layout model page : a model linked to a unique page background and design
 *
 * The page number : 1 is the first page, -1 is the last page, 0 is 'middle pages' (use constants).
 * You can set specific page number presentation, too.
 *
 * Page ordering : 1 (first), 2, 3, ..., default is 0 (middle), ..., -3, -2, -1 (last).
 *
 * @override ordering @var string
 * @property string ordering
 */
abstract class Page
{
	use Component;
	use Has_Ordering;

	//----------------------------------------------------------------------------------- $background
	/**
	 * @link Object
	 * @var File
	 */
	public $background;

	//------------------------------------------------------------------------------------ $font_size
	/**
	 * Default font size for document designer (in final/stored unit)
	 *
	 * @var float
	 */
	public $font_size;

	//--------------------------------------------------------------------------------------- $layout
	/**
	 * Raw page layout : a json structure from html_links & document-designer that describes fields
	 * and how they are laid-out
	 *
	 * @max_length 1000000000
	 * @var string
	 */
	public $layout;

	//---------------------------------------------------------------------------------------- $model
	/**
	 * @composite
	 * @link Object
	 * @var Model
	 */
	public $model;

	//--------------------------------------------------------------------------------- $ratio_height
	/**
	 * Real height for document designer (in final/stored unit)
	 *
	 * @store false
	 * @var float
	 */
	public $ratio_height;

	//---------------------------------------------------------------------------------- $ratio_width
	/**
	 * Real width for document designer (in final/stored unit)
	 *
	 * @store false
	 * @var float
	 */
	public $ratio_width;

	//---------------------------------------------------------------------------------- $view_height
	/**
	 * View height for document designer
	 *
	 * @store false
	 * @unit px
	 * @var integer
	 */
	public $view_height;

	//----------------------------------------------------------------------------------- $view_width
	/**
	 * View width for document designer
	 *
	 * @store false
	 * @unit px
	 * @var integer
	 */
	public $view_width;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $ordering integer ordering number, eg page number (see constants)
	 * @param $layout   string raw layout of the page
	 */
	public function __construct($ordering = null, $layout = null)
	{
		if (isset($ordering)) {
			$this->ordering = $ordering;
		}
		if (isset($layout)) {
			$this->layout = $layout;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->model) . SP . strval($this->ordering);
	}

	//---------------------------------------------------------------------------- backgroundImageUrl
	/**
	 * @return string
	 */
	public function backgroundImageUrl()
	{
		$hash = $this->background ? $this->background->hash : null;
		return Paths::$uri_base . View::link($this, 'background', null, $hash);
	}

	//------------------------------------------------------------------------------- orderingCaption
	/**
	 * Get ordering caption (eg first, middle, last page), or page number if free ordering number
	 *
	 * @return integer|string @example 'last'
	 */
	abstract public function orderingCaption();

	//---------------------------------------------------------------------------- orderingToSortable
	/**
	 * Return an unsigned numeric value calculated from $this->ordering
	 *
	 * @return integer
	 */
	abstract protected function orderingToSortable();

	//------------------------------------------------------------------------------------------ sort
	/**
	 * Sort objects by their value of $ordering
	 *
	 * - first 1 (first), 2, etc.
	 * - then 0 (middle)
	 * - at last -2, -1 (last) come last
	 *
	 * @param $objects_having_ordering static[]
	 * @return static[]
	 */
	public static function sort(array $objects_having_ordering)
	{
		uasort($objects_having_ordering, function (Page $object1, Page $object2) {
			return cmp($object1->orderingToSortable(), $object2->orderingToSortable());
		});
		return $objects_having_ordering;
	}

}
