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
 * @override ordering @max_length 2 @var string
 * @property string ordering
 */
abstract class Page
{
	use Component;
	use Has_Ordering;

	//----------------------------------------------------------- page position information constants
	/**
	 * It is independent but must be the same special values than Structure\Page constants
	 */
	const ALL    = 'A';
	const FIRST  = '1';
	const LAST   = '-1';
	const MIDDLE = '0';
	const UNIQUE = 'U';

	//----------------------------------------------------------------------------------- $background
	/**
	 * @link Object
	 * @var File
	 */
	public $background;

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
		return Paths::$uri_base . SL . View::link($this, 'background', null, $hash);
	}

	//------------------------------------------------------------------------------- orderingCaption
	/**
	 * Get ordering caption (first, middle, last page), or page number if free ordering number
	 *
	 * @return integer|string @example 'last'
	 */
	public function orderingCaption()
	{
		switch ($this->ordering) {
			case static::ALL:    return 'all';
			case static::FIRST:  return 'first';
			case static::LAST:   return 'last';
			case static::MIDDLE: return 'middle';
			case static::UNIQUE: return 'unique';
		}
		return $this->ordering;
	}

	//---------------------------------------------------------------------------- orderingToSortable
	/**
	 * Return an unsigned numeric value calculated from $this->ordering
	 *
	 * @example  1 =>    1 (first)
	 * @example  2 =>    2
	 * @example  0 => 1000 (middle)
	 * @example -2 => 1998
	 * @example -1 => 1999 (last)
	 * @return integer
	 */
	protected function orderingToSortable()
	{
		$ordering = $this->ordering;
		if ($ordering === static::UNIQUE) {
			return -1001;
		}
		if ($ordering === static::ALL) {
			return 10000;
		}
		$ordering = intval($ordering);
		if (!$ordering) {
			return 1000;
		}
		if ($ordering < 0) {
			return $ordering + 2000;
		}
		return $ordering;
	}

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
