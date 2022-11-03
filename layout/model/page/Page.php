<?php
namespace ITRocks\Framework\Layout\Model;

use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Layout\Model;
use ITRocks\Framework\Mapper\Component;
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
 * @sort ordering
 */
abstract class Page
{
	use Component;

	//----------------------------------------------------------------------------------- $background
	/**
	 * @link Object
	 * @var ?File
	 */
	public ?File $background;

	//------------------------------------------------------------------------------------ $font_size
	/**
	 * Default font size for document designer (in final/stored unit)
	 *
	 * @var float
	 */
	public float $font_size;

	//--------------------------------------------------------------------------------------- $layout
	/**
	 * Raw page layout : a json structure from html_links & document-designer that describes fields
	 * and how they are laid-out
	 *
	 * @max_length 1000000000
	 * @var string
	 */
	public string $layout;

	//---------------------------------------------------------------------------------------- $model
	/**
	 * @composite
	 * @link Object
	 * @var Model
	 */
	public Model $model;

	//------------------------------------------------------------------------------------- $ordering
	/**
	 * @customized
	 * @empty_check false
	 * @no_autowidth
	 * @user hide_output
	 * @var string
	 */
	public string $ordering;

	//--------------------------------------------------------------------------------- $ratio_height
	/**
	 * Real height for document designer (in final/stored unit)
	 *
	 * @store false
	 * @var float
	 */
	public float $ratio_height;

	//---------------------------------------------------------------------------------- $ratio_width
	/**
	 * Real width for document designer (in final/stored unit)
	 *
	 * @store false
	 * @var float
	 */
	public float $ratio_width;

	//---------------------------------------------------------------------------------- $view_height
	/**
	 * View height for document designer
	 *
	 * @store false
	 * @unit px
	 * @var integer
	 */
	public int $view_height;

	//----------------------------------------------------------------------------------- $view_width
	/**
	 * View width for document designer
	 *
	 * @store false
	 * @unit px
	 * @var integer
	 */
	public int $view_width;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $ordering integer|null ordering number, eg page number (see constants)
	 * @param $layout   string|null raw layout of the page
	 */
	public function __construct(int $ordering = null, string $layout = null)
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
	public function __toString() : string
	{
		return $this->model . SP . $this->ordering;
	}

	//---------------------------------------------------------------------------- backgroundImageUrl
	/**
	 * @return string
	 */
	public function backgroundImageUrl() : string
	{
		$hash = $this->background?->hash;
		return Paths::$uri_base . View::link($this, 'background', null, $hash);
	}

	//------------------------------------------------------------------------------- orderingCaption
	/**
	 * Get ordering caption (eg first, middle, last page), or page number if free ordering number
	 *
	 * @return string @example 'last'
	 */
	abstract public function orderingCaption() : string;

	//---------------------------------------------------------------------------- orderingToSortable
	/**
	 * Return an unsigned numeric value calculated from $this->ordering
	 *
	 * @return integer
	 */
	abstract protected function orderingToSortable() : int;

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
	public static function sort(array $objects_having_ordering) : array
	{
		uasort($objects_having_ordering, function (Page $object1, Page $object2) {
			return cmp($object1->orderingToSortable(), $object2->orderingToSortable());
		});
		return $objects_having_ordering;
	}

}
