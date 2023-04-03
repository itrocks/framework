<?php
namespace ITRocks\Framework\Layout\Model;

use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Feature\Validate\Property\Max_Length;
use ITRocks\Framework\Layout\Model;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Reflection\Attribute\Property\Composite;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Unit;
use ITRocks\Framework\Reflection\Attribute\Property\User;
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
	public ?File $background;

	//------------------------------------------------------------------------------------ $font_size
	/** Default font size for document designer (in final/stored unit) */
	public float $font_size;

	//--------------------------------------------------------------------------------------- $layout
	/**
	 * Raw page layout : a json structure from html_links & document-designer that describes fields
	 * and how they are laid-out
	 */
	#[Max_Length(1000000000)]
	public string $layout = '';

	//---------------------------------------------------------------------------------------- $model
	#[Composite]
	public Model $model;

	//------------------------------------------------------------------------------------- $ordering
	/**
	 * @customized
	 * @empty_check false
	 * @no_autowidth
	 */
	#[User(User::HIDE_OUTPUT)]
	public string $ordering = '';

	//--------------------------------------------------------------------------------- $ratio_height
	/** Real height for document designer (in final/stored unit) */
	#[Store(false)]
	public float $ratio_height;

	//---------------------------------------------------------------------------------- $ratio_width
	/** Real width for document designer (in final/stored unit) */
	#[Store(false)]
	public float $ratio_width;

	//---------------------------------------------------------------------------------- $view_height
	/** View height for document designer */
	#[Store(false), Unit('px')]
	public int $view_height;

	//----------------------------------------------------------------------------------- $view_width
	/** View width for document designer */
	#[Store(false), Unit('px')]
	public int $view_width;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $ordering string|null ordering number, eg page number (see constants)
	 * @param $layout   string|null raw layout of the page
	 */
	public function __construct(string $ordering = null, string $layout = null)
	{
		if (isset($ordering)) {
			$this->ordering = $ordering;
		}
		if (isset($layout)) {
			$this->layout = $layout;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->model . SP . $this->ordering;
	}

	//---------------------------------------------------------------------------- backgroundImageUrl
	public function backgroundImageUrl() : string
	{
		$hash = $this->background?->hash;
		return Paths::$uri_base . View::link($this, 'background', null, $hash);
	}

	//------------------------------------------------------------------------------- orderingCaption
	/** Get ordering caption (e.g. first, middle, last page), or page number (free ordering number) */
	abstract public function orderingCaption() : string;

	//---------------------------------------------------------------------------- orderingToSortable
	/** Return an unsigned numeric value calculated from $this->ordering */
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
		uasort($objects_having_ordering, function (Page $object1, Page $object2) : int {
			return cmp($object1->orderingToSortable(), $object2->orderingToSortable());
		});
		return $objects_having_ordering;
	}

}
