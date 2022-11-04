<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Period;

/**
 * Use it into your objects that have begin and end dates
 */
trait Has_Begin_End_Dates
{

	//----------------------------------------------------------------------------------- $begin_date
	/**
	 * @link DateTime
	 * @var Date_Time|string
	 */
	public Date_Time|string $begin_date;

	//------------------------------------------------------------------------------------- $end_date
	/**
	 * @default Date_Time::max
	 * @link DateTime
	 * @var Date_Time|string
	 */
	public Date_Time|string $end_date;

	//-------------------------------------------------------------------------------------- activeAt
	/**
	 * Gets the active object for a given date (day)
	 *
	 * @param $date_time Date_Time|null @default Date_Time::now
	 * @return ?static
	 */
	public static function activeAt(Date_Time $date_time = null) : ?Has_Begin_End_Dates
	{
		if (!isset($date_time)) {
			$date_time = Date_Time::now();
		}
		/** @var $result static */
		$result = Dao::searchOne(
			[
				'begin_date' => Func::lessOrEqual($date_time->toEndOf(Date_Time::DAY)),
				'end_date'   => Func::greaterOrEqual($date_time->toBeginOf(Date_Time::DAY))
			],
			static::class
		);
		return $result;
	}

	//--------------------------------------------------------------------------------- checkOverlaps
	/**
	 * Returns true if there is no date overlap into $array objects, or an error message if there are
	 *
	 * @param $array      static[] an array of elements with begin-end dates
	 * @param $array_name string|null a name for the object that contains the array
	 * @return array|boolean|string
	 * boolean : true if there is no overlapping error
	 * string : first error message (if $array_name is set only)
	 * array[object $first_element, object $second_element] : if $array_name is null, returns overlaps
	 */
	public static function checkOverlaps(array $array, string $array_name = null) : array|bool|string
	{
		$overlaps = [];
		foreach ($array as $first_element) {
			foreach ($array as $second_element) {
				if (
					!Dao::is($first_element, $second_element)
					&& $first_element->datesOverlap($second_element)
				) {
					if (isset($array_name)) {
						return Loc::tr(
							"The :name can't be in :element_name :first and :second at the same time",
							Loc::replace([
								'element_name' => Names::classToDisplay(static::class),
								'first'        => $first_element,
								'name'         => $array_name,
								'second'       => $second_element
							])
						);
					}
					else {
						$overlaps[] = [$first_element, $second_element];
					}
				}
			}
		}
		return $overlaps ?: true;
	}

	//---------------------------------------------------------------------------------- datesOverlap
	/**
	 * Returns true if begin and end dates for the current object overlap dates of $with
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $with Has_Begin_End_Dates
	 * @return boolean
	 */
	public function datesOverlap(Has_Begin_End_Dates $with) : bool
	{
		/** @noinspection PhpUnhandledExceptionInspection constant */
		$this_period = Builder::create(Period::class, [$this->begin_date, $this->end_date]);
		/** @noinspection PhpUnhandledExceptionInspection constant */
		$with_period = Builder::create(Period::class, [$with->begin_date, $with->end_date]);
		return !$this_period->out($with_period);
	}

}
