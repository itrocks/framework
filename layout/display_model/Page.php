<?php
namespace ITRocks\Framework\Layout\Display_Model;

use ITRocks\Framework\Layout\Display_Model;
use ITRocks\Framework\Layout\Model;

/**
 * Print model page
 *
 * @override model @var Display_Model
 * @override ordering @max_length 2
 * @property Display_Model model
 * @store_name display_model_pages
 */
class Page extends Model\Page
{

	//----------------------------------------------------------- page position information constants
	const BIG_SCREEN            = 'B';
	const HORIZONTAL_SMARTPHONE = 'HP';
	const HORIZONTAL_TABLET     = 'HT';
	const SCREEN                = 'S';
	const VERTICAL_SMARTPHONE   = 'VP';
	const VERTICAL_TABLET       = 'VT';

	//------------------------------------------------------------------------------- sizes constants
	const FONT_SIZE   = 'font_size';
	const SIZE_FORMAT = 'size_format';
	const SIZE_RATIO  = 'size_ratio';
	const VIEW_WIDTH  = 'view_width';

	//------------------------------------------------------------------------- size format constants
	const LANDSCAPE = 'landscape';
	const PORTRAIT  = 'portrait';

	//-------------------------------------------------------------------------------------- ORDERING
	const ORDERING = [
		self::SCREEN                => 1,
		self::VERTICAL_SMARTPHONE   => 2,
		self::HORIZONTAL_SMARTPHONE => 3,
		self::VERTICAL_TABLET       => 4,
		self::HORIZONTAL_TABLET     => 5,
		self::BIG_SCREEN            => 6
	];

	//----------------------------------------------------------------------------------------- SIZES
	const SIZES = [
		self::SCREEN => [
			self::FONT_SIZE   => 1.4,
			self::SIZE_FORMAT => self::LANDSCAPE,
			self::SIZE_RATIO  => 16/9,
			self::VIEW_WIDTH  => 1280
		],
		self::VERTICAL_SMARTPHONE => [
			self::FONT_SIZE   => 10,
			self::SIZE_FORMAT => self::PORTRAIT,
			self::SIZE_RATIO  => 16/8,
			self::VIEW_WIDTH  => 300
		],
		self::HORIZONTAL_SMARTPHONE => [
			self::FONT_SIZE   => 5,
			self::SIZE_FORMAT => self::LANDSCAPE,
			self::SIZE_RATIO  => 16/8,
			self::VIEW_WIDTH  => 600
		],
		self::VERTICAL_TABLET => [
			self::FONT_SIZE   => 4,
			self::SIZE_FORMAT => self::PORTRAIT,
			self::SIZE_RATIO  => 4/3,
			self::VIEW_WIDTH  => 768
		],
		self::HORIZONTAL_TABLET => [
			self::FONT_SIZE   => 3,
			self::SIZE_FORMAT => self::LANDSCAPE,
			self::SIZE_RATIO  => 4/3,
			self::VIEW_WIDTH  => 1024
		],
		self::BIG_SCREEN => [
			self::FONT_SIZE   => 1,
			self::SIZE_FORMAT => self::LANDSCAPE,
			self::SIZE_RATIO  => 16/9,
			self::VIEW_WIDTH  => 1920
		]
	];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $ordering integer|null ordering number, eg page number (see constants)
	 * @param $layout   string|null  raw layout of the page
	 */
	public function __construct(int $ordering = null, string $layout = null)
	{
		parent::__construct($ordering, $layout);
		if ($this->ordering) {
			$this->calculateSize();
		}
	}

	//--------------------------------------------------------------------------------- calculateSize
	/**
	 * Calculate ratio, size, font size, etc.
	 */
	protected function calculateSize() : void
	{
		$ratio_width = 100;
		$size        = static::SIZES[$this->ordering];

		$this->font_size = $size[static::FONT_SIZE];

		$this->ratio_height = ($size[static::SIZE_FORMAT] === static::LANDSCAPE)
			? ($ratio_width / $size[static::SIZE_RATIO])
			: ($ratio_width * $size[static::SIZE_RATIO]);
		$this->ratio_width  = $ratio_width;

		$this->view_height = ($size[static::SIZE_FORMAT] === static::LANDSCAPE)
			? ($size[static::VIEW_WIDTH] / $size[static::SIZE_RATIO])
			: ($size[static::VIEW_WIDTH] * $size[static::SIZE_RATIO]);
		$this->view_width  = $size[static::VIEW_WIDTH];
	}

	//------------------------------------------------------------------------------- orderingCaption
	/**
	 * Get ordering caption (eg first, middle, last page), or page number if free ordering number
	 *
	 * @return string @example 'screen'
	 */
	public function orderingCaption() : string
	{
		switch ($this->ordering) {
			case static::BIG_SCREEN:            return 'big_screen';
			case static::HORIZONTAL_SMARTPHONE: return 'horizontal_smartphone';
			case static::HORIZONTAL_TABLET:     return 'horizontal_tablet';
			case static::SCREEN:                return 'screen';
			case static::VERTICAL_SMARTPHONE:   return 'vertical_smartphone';
			case static::VERTICAL_TABLET:       return 'vertical_tablet';
		}
		return $this->ordering;
	}

	//---------------------------------------------------------------------------- orderingToSortable
	/**
	 * Return an unsigned numeric value calculated from $this->ordering
	 *
	 * @return integer
	 */
	protected function orderingToSortable() : int
	{
		return static::ORDERING[$this->ordering];
	}

}
