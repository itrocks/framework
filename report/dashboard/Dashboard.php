<?php
namespace ITRocks\Framework\Report;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Component;
use ITRocks\Framework\Report\Dashboard\Indicator;
use ITRocks\Framework\Session;
use ITRocks\Framework\Traits\Has_Name;

/**
 * Dashboard
 *
 * @feature
 */
#[Store]
class Dashboard
{
	use Has_Name;

	//----------------------------------------------------------------------------------- $indicators
	/**
	 * @var Indicator[]
	 */
	#[Component]
	public array $indicators;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * A constructor for your Has_Name class
	 *
	 * @todo use With_Constructor : needs AOP compiler update
	 */
	public function __construct(string $name = null)
	{
		if (isset($name)) {
			$this->name = $name;
		}
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * Gets the session current / default dashboard. If none : initialized to dashboard Nr 1.
	 * If it does not exist : created.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return static
	 */
	public static function current() : static
	{
		/** @noinspection PhpUnhandledExceptionInspection class */
		return Session::current()->get(static::class)
			?: Dao::read(1, static::class)
			?: Builder::create(static::class, [Loc::tr('main')]);
	}

	//-------------------------------------------------------------------------------- fullHeightGrid
	/**
	 * @return Indicator[][] (?Indicator)[int $y][int $x]
	 */
	public function fullHeightGrid() : array
	{
		$grid = $this->grid();
		do {
			$grid[] = array_fill(0, Indicator::GRID_WIDTH, null);
		}
		while (count($grid) < 6);
		return $grid;
	}

	//------------------------------------------------------------------------------------------ grid
	/**
	 * @return Indicator[][] (?Indicator)[int $y][int $x]
	 */
	public function grid() : array
	{
		$grid        = [];
		$grid_height = 0;
		foreach ($this->indicators as $indicator) {
			if (!isset($indicator->grid_y)) {
				continue;
			}
			while ($indicator->grid_y >= $grid_height) {
				$grid[$grid_height] = array_fill(0, Indicator::GRID_WIDTH, null);
				$grid_height ++;
			}
			$grid[$indicator->grid_y][$indicator->grid_x] = $indicator;
		}
		return $grid;
	}

	//------------------------------------------------------------------------------------ setCurrent
	/**
	 * Sets this dashboard as the current / default one for the session
	 */
	public function setCurrent() : void
	{
		Session::current()->set($this);
	}

}
