<?php
namespace ITRocks\Framework\Layout\Print_Model;

use ITRocks\Framework\Feature\Validate\Property\Max_Length;
use ITRocks\Framework\Layout\Model;
use ITRocks\Framework\Layout\Print_Model;
use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;

/**
 * Print model page
 *
 * @override model @var Print_Model
 * @property Print_Model model
 */
#[Override('layout',   new Getter('getLayout'))]
#[Override('ordering', new Max_Length(2))]
#[Store('print_model_pages')]
class Page extends Model\Page
{

	//----------------------------------------------------------- page position information constants
	/**
	 * It is independent but must be the same special values than Structure\Page constants
	 */
	const ALL    = 'A';
	const FIRST  = '1';
	const LAST   = '-1';
	const MIDDLE = '0';
	const UNIQUE = 'U';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $ordering string|null ordering number, eg page number (see constants)
	 * @param $layout   string|null raw layout of the page
	 */
	public function __construct(string $ordering = null, string $layout = null)
	{
		$this->ratio_height = 297;
		$this->ratio_width  = 210;
		$this->font_size    = 10;
		$this->view_height  = 1188;
		$this->view_width   = 840;
		parent::__construct($ordering, $layout);
	}

	//--------------------------------------------------------------------------------------- caption
	public function caption() : string
	{
		switch ($this->ordering) {
			case static::ALL:    return 'all pages';
			case static::FIRST:  return 'first page';
			case static::LAST:   return 'last page';
			case static::MIDDLE: return 'middle pages';
			case static::UNIQUE: return 'unique page';
		}
		return $this->ordering;
	}

	//------------------------------------------------------------------------------------- getLayout
	protected function getLayout() : string
	{
		if (($this->layout === '[]') || !$this->layout) {
			/** @noinspection PhpUnhandledExceptionInspection */
			$this->layout = jsonEncode(
				[
					['class' => 'horizontal snap line', 'top'  => 10 ],
					['class' => 'horizontal snap line', 'top'  => 287],
					['class' => 'vertical snap line',   'left' => 10 ],
					['class' => 'vertical snap line',   'left' => 200]
				]
			);
		}
		return $this->layout;
	}

	//------------------------------------------------------------------------------- orderingCaption
	/** Get ordering caption (first, middle, last page), or page number if free ordering number */
	public function orderingCaption() : string
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
	 */
	protected function orderingToSortable() : int
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

}
