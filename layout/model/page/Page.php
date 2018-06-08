<?php
namespace ITRocks\Framework\Layout\Model;

use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Layout\Model;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Tools\Has_Ordering;

/**
 * A layout model page : a model linked to a unique page background and design
 *
 * The page number : 1 is the first page, 99 is the last page, 98 is 'middle pages' (use constants).
 * You can set specific page number presentation, too.
 *
 * @store_name layout_model_pages
 */
class Page
{
	use Component;
	use Has_Ordering;

	//----------------------------------------------------------- page position information constants
	const FIRST  = 1;
	const LAST   = 99;
	const MIDDLE = 98;

	//----------------------------------------------------------------------------------- $background
	/**
	 * @link Object
	 * @var File
	 */
	public $background;

	//--------------------------------------------------------------------------------------- $fields
	/**
	 * @link Collection
	 * @var Field[]
	 */
	public $fields;

	//---------------------------------------------------------------------------------------- $model
	/**
	 * @composite
	 * @link Object
	 * @var Model
	 */
	public $model;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $ordering integer|string ordering number of constant
	 */
	public function __construct($ordering = null)
	{
		if (isset($ordering)) {
			$this->ordering = $ordering;
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

	//------------------------------------------------------------------------------- orderingCaption
	/**
	 * Get ordering caption (first, middle, last page), or page number if free ordering number
	 *
	 * @return integer|string @example 'last'
	 */
	public function orderingCaption()
	{
		switch ($this->ordering) {
			case static::FIRST:  return 'first';
			case static::LAST:   return 'last';
			case static::MIDDLE: return 'middle';
		}
		return $this->ordering;
	}

}
