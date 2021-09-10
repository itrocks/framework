<?php
namespace ITRocks\Framework\Report;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Report\Dashboard\Indicator;
use ITRocks\Framework\Session;
use ITRocks\Framework\Traits\Has_Name;

/**
 * Dashboard
 *
 * @feature
 */
class Dashboard
{
	use Has_Name;

	//----------------------------------------------------------------------------------- $indicators
	/**
	 * @link Collection
	 * @var Indicator[]
	 */
	public $indicators;

	//--------------------------------------------------------------------------------------- current
	/**
	 * Gets the session current / default dashboard. If none : initialized to dashboard Nr 1.
	 * If does not exist : created.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return static
	 */
	public static function current() : static
	{
		/** @noinspection PhpIncompatibleReturnTypeInspection Dao:read(static) */
		/** @noinspection PhpUnhandledExceptionInspection create static */
		return Session::current()->get(static::class)
			?: Dao::read(1, static::class)
			?: Builder::create(static::class);
	}

	//------------------------------------------------------------------------------------ setCurrent
	/**
	 * Sets this dashboard as the current / default one for the session
	 */
	public function setCurrent()
	{
		Session::current()->set($this);
	}

}
