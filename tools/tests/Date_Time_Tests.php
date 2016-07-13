<?php
namespace SAF\Framework\Tools\Tests;

use SAF\Framework\Tests\Test;
use SAF\Framework\Tools\Date_Time;

/**
 * Date_Time tools class unit tests
 */
class Date_Time_Tests extends Test
{

	//----------------------------------------------------------------------------------- testIsAfter
	public function testIsAfter()
	{
		$empty_string    = '0000-00-00 00:00:00';
		$today_string    = '2016-07-13 09:47:05';
		$tomorrow_string = '2016-07-14 00:00:00';
		$empty_date = new Date_Time($empty_string);
		$today_date = new Date_Time($today_string);
		$min_date   = Date_Time::min();
		$max_date   = Date_Time::max();
		$this->method(__METHOD__);
		$this->assume('emptyAfterEmptyString',    $empty_date->isAfter($empty_string),    false);
		$this->assume('emptyAfterTodayString',    $empty_date->isAfter($today_string),    false);
		$this->assume('emptyAfterTomorrow',       $empty_date->isAfter($tomorrow_string), false);
		$this->assume('emptyAfterEmptyDate',      $empty_date->isAfter($empty_date),      false);
		$this->assume('emptyAfterTodayDate',      $empty_date->isAfter($today_date),      false);
		$this->assume('emptyAfterMinDate',        $empty_date->isAfter($min_date),        false);
		$this->assume('emptyAfterMaxDate',        $empty_date->isAfter($max_date),        false);
		$this->assume('todayAfterEmptyString',    $today_date->isAfter($empty_string),    true);
		$this->assume('todayAfterTodayString',    $today_date->isAfter($today_string),    false);
		$this->assume('todayAfterTomorrowString', $today_date->isAfter($tomorrow_string), false);
		$this->assume('todayAfterEmptyDate',      $today_date->isAfter($empty_date),      true);
		$this->assume('todayAfterMinDate',        $today_date->isAfter($min_date),        true);
		$this->assume('todayAfterTodayDate',      $today_date->isAfter($today_date),      false);
		$this->assume('todayAfterMaxDate',        $today_date->isAfter($max_date),        false);
		$this->assume('minAfterEmptyString',      $min_date->isAfter($empty_string),      false);
		$this->assume('minAfterTodayString',      $min_date->isAfter($today_string),      false);
		$this->assume('minAfterTomorrowString',   $min_date->isAfter($tomorrow_string),   false);
		$this->assume('minAfterEmptyDate',        $min_date->isAfter($empty_date),        false);
		$this->assume('minAfterTodayDate',        $min_date->isAfter($today_date),        false);
		$this->assume('minAfterMinDate',          $min_date->isAfter($min_date),          false);
		$this->assume('minAfterMaxDate',          $min_date->isAfter($max_date),          false);
		$this->assume('maxAfterEmptyString',      $max_date->isAfter($empty_string),      true);
		$this->assume('maxAfterTodayString',      $max_date->isAfter($today_string),      true);
		$this->assume('maxAfterTomorrowString',   $max_date->isAfter($tomorrow_string),   true);
		$this->assume('maxAfterEmptyDate',        $max_date->isAfter($empty_date),        true);
		$this->assume('maxAfterTodayDate',        $max_date->isAfter($today_date),        true);
		$this->assume('minAfterMinDate',          $max_date->isAfter($min_date),          true);
		$this->assume('maxAfterMaxDate',          $max_date->isAfter($max_date),          false);
	}

	//---------------------------------------------------------------------------- testIsAfterOrEqual
	public function testIsAfterOrEqual()
	{
		$empty_string    = '0000-00-00 00:00:00';
		$today_string    = '2016-07-13 09:47:05';
		$tomorrow_string = '2016-07-14 00:00:00';
		$empty_date = new Date_Time($empty_string);
		$today_date = new Date_Time($today_string);
		$min_date   = Date_Time::min();
		$max_date   = Date_Time::max();
		$this->method(__METHOD__);
		$this->assume('emptyAfterEmptyString',    $empty_date->isAfterOrEqual($empty_string),    true);
		$this->assume('emptyAfterTodayString',    $empty_date->isAfterOrEqual($today_string),    false);
		$this->assume('emptyAfterTomorrow',       $empty_date->isAfterOrEqual($tomorrow_string), false);
		$this->assume('emptyAfterEmptyDate',      $empty_date->isAfterOrEqual($empty_date),      true);
		$this->assume('emptyAfterTodayDate',      $empty_date->isAfterOrEqual($today_date),      false);
		$this->assume('emptyAfterMinDate',        $empty_date->isAfterOrEqual($min_date),        true);
		$this->assume('emptyAfterMaxDate',        $empty_date->isAfterOrEqual($max_date),        false);
		$this->assume('todayAfterEmptyString',    $today_date->isAfterOrEqual($empty_string),    true);
		$this->assume('todayAfterTodayString',    $today_date->isAfterOrEqual($today_string),    true);
		$this->assume('todayAfterTomorrowString', $today_date->isAfterOrEqual($tomorrow_string), false);
		$this->assume('todayAfterEmptyDate',      $today_date->isAfterOrEqual($empty_date),      true);
		$this->assume('todayAfterMinDate',        $today_date->isAfterOrEqual($min_date),        true);
		$this->assume('todayAfterTodayDate',      $today_date->isAfterOrEqual($today_date),      true);
		$this->assume('todayAfterMaxDate',        $today_date->isAfterOrEqual($max_date),        false);
		$this->assume('minAfterEmptyString',      $min_date->isAfterOrEqual($empty_string),      true);
		$this->assume('minAfterTodayString',      $min_date->isAfterOrEqual($today_string),      false);
		$this->assume('minAfterTomorrowString',   $min_date->isAfterOrEqual($tomorrow_string),   false);
		$this->assume('minAfterEmptyDate',        $min_date->isAfterOrEqual($empty_date),        true);
		$this->assume('minAfterTodayDate',        $min_date->isAfterOrEqual($today_date),        false);
		$this->assume('minAfterMinDate',          $min_date->isAfterOrEqual($min_date),          true);
		$this->assume('minAfterMaxDate',          $min_date->isAfterOrEqual($max_date),          false);
		$this->assume('maxAfterEmptyString',      $max_date->isAfterOrEqual($empty_string),      true);
		$this->assume('maxAfterTodayString',      $max_date->isAfterOrEqual($today_string),      true);
		$this->assume('maxAfterTomorrowString',   $max_date->isAfterOrEqual($tomorrow_string),   true);
		$this->assume('maxAfterEmptyDate',        $max_date->isAfterOrEqual($empty_date),        true);
		$this->assume('maxAfterTodayDate',        $max_date->isAfterOrEqual($today_date),        true);
		$this->assume('minAfterMinDate',          $max_date->isAfterOrEqual($min_date),          true);
		$this->assume('maxAfterMaxDate',          $max_date->isAfterOrEqual($max_date),          true);
	}

	//---------------------------------------------------------------------------------- testIsBefore
	public function testIsBefore()
	{
		$empty_string    = '0000-00-00 00:00:00';
		$today_string    = '2016-07-13 09:47:05';
		$tomorrow_string = '2016-07-14 00:00:00';
		$empty_date = new Date_Time($empty_string);
		$today_date = new Date_Time($today_string);
		$min_date   = Date_Time::min();
		$max_date   = Date_Time::max();
		$this->method(__METHOD__);
		$this->assume('emptyBeforeEmptyString',    $empty_date->isBefore($empty_string),    false);
		$this->assume('emptyBeforeTodayString',    $empty_date->isBefore($today_string),    true);
		$this->assume('emptyBeforeTomorrow',       $empty_date->isBefore($tomorrow_string), true);
		$this->assume('emptyBeforeEmptyDate',      $empty_date->isBefore($empty_date),      false);
		$this->assume('emptyBeforeTodayDate',      $empty_date->isBefore($today_date),      true);
		$this->assume('emptyBeforeMinDate',        $empty_date->isBefore($min_date),        false);
		$this->assume('emptyBeforeMaxDate',        $empty_date->isBefore($max_date),        true);
		$this->assume('todayBeforeEmptyString',    $today_date->isBefore($empty_string),    false);
		$this->assume('todayBeforeTodayString',    $today_date->isBefore($today_string),    false);
		$this->assume('todayBeforeTomorrowString', $today_date->isBefore($tomorrow_string), true);
		$this->assume('todayBeforeEmptyDate',      $today_date->isBefore($empty_date),      false);
		$this->assume('todayBeforeMinDate',        $today_date->isBefore($min_date),        false);
		$this->assume('todayBeforeTodayDate',      $today_date->isBefore($today_date),      false);
		$this->assume('todayBeforeMaxDate',        $today_date->isBefore($max_date),        true);
		$this->assume('minBeforeEmptyString',      $min_date->isBefore($empty_string),      false);
		$this->assume('minBeforeTodayString',      $min_date->isBefore($today_string),      true);
		$this->assume('minBeforeTomorrowString',   $min_date->isBefore($tomorrow_string),   true);
		$this->assume('minBeforeEmptyDate',        $min_date->isBefore($empty_date),        false);
		$this->assume('minBeforeTodayDate',        $min_date->isBefore($today_date),        true);
		$this->assume('minBeforeMinDate',          $min_date->isBefore($min_date),          false);
		$this->assume('minBeforeMaxDate',          $min_date->isBefore($max_date),          true);
		$this->assume('maxBeforeEmptyString',      $max_date->isBefore($empty_string),      false);
		$this->assume('maxBeforeTodayString',      $max_date->isBefore($today_string),      false);
		$this->assume('maxBeforeTomorrowString',   $max_date->isBefore($tomorrow_string),   false);
		$this->assume('maxBeforeEmptyDate',        $max_date->isBefore($empty_date),        false);
		$this->assume('maxBeforeTodayDate',        $max_date->isBefore($today_date),        false);
		$this->assume('minBeforeMinDate',          $max_date->isBefore($min_date),          false);
		$this->assume('maxBeforeMaxDate',          $max_date->isBefore($max_date),          false);
		$this->method('-');
	}

	//--------------------------------------------------------------------------- testIsBeforeOrEqual
	public function testIsBeforeOrEqual()
	{
		$empty_string    = '0000-00-00 00:00:00';
		$today_string    = '2016-07-13 09:47:05';
		$tomorrow_string = '2016-07-14 00:00:00';
		$empty_date = new Date_Time($empty_string);
		$today_date = new Date_Time($today_string);
		$min_date   = Date_Time::min();
		$max_date   = Date_Time::max();
		$this->method(__METHOD__);
		$this->assume('emptyBeforeEmptyString',    $empty_date->isBeforeOrEqual($empty_string),    true);
		$this->assume('emptyBeforeTodayString',    $empty_date->isBeforeOrEqual($today_string),    true);
		$this->assume('emptyBeforeTomorrow',       $empty_date->isBeforeOrEqual($tomorrow_string), true);
		$this->assume('emptyBeforeEmptyDate',      $empty_date->isBeforeOrEqual($empty_date),      true);
		$this->assume('emptyBeforeTodayDate',      $empty_date->isBeforeOrEqual($today_date),      true);
		$this->assume('emptyBeforeMinDate',        $empty_date->isBeforeOrEqual($min_date),        true);
		$this->assume('emptyBeforeMaxDate',        $empty_date->isBeforeOrEqual($max_date),        true);
		$this->assume('todayBeforeEmptyString',    $today_date->isBeforeOrEqual($empty_string),    false);
		$this->assume('todayBeforeTodayString',    $today_date->isBeforeOrEqual($today_string),    true);
		$this->assume('todayBeforeTomorrowString', $today_date->isBeforeOrEqual($tomorrow_string), true);
		$this->assume('todayBeforeEmptyDate',      $today_date->isBeforeOrEqual($empty_date),      false);
		$this->assume('todayBeforeMinDate',        $today_date->isBeforeOrEqual($min_date),        false);
		$this->assume('todayBeforeTodayDate',      $today_date->isBeforeOrEqual($today_date),      true);
		$this->assume('todayBeforeMaxDate',        $today_date->isBeforeOrEqual($max_date),        true);
		$this->assume('minBeforeEmptyString',      $min_date->isBeforeOrEqual($empty_string),      true);
		$this->assume('minBeforeTodayString',      $min_date->isBeforeOrEqual($today_string),      true);
		$this->assume('minBeforeTomorrowString',   $min_date->isBeforeOrEqual($tomorrow_string),   true);
		$this->assume('minBeforeEmptyDate',        $min_date->isBeforeOrEqual($empty_date),        true);
		$this->assume('minBeforeTodayDate',        $min_date->isBeforeOrEqual($today_date),        true);
		$this->assume('minBeforeMinDate',          $min_date->isBeforeOrEqual($min_date),          true);
		$this->assume('minBeforeMaxDate',          $min_date->isBeforeOrEqual($max_date),          true);
		$this->assume('maxBeforeEmptyString',      $max_date->isBeforeOrEqual($empty_string),      false);
		$this->assume('maxBeforeTodayString',      $max_date->isBeforeOrEqual($today_string),      false);
		$this->assume('maxBeforeTomorrowString',   $max_date->isBeforeOrEqual($tomorrow_string),   false);
		$this->assume('maxBeforeEmptyDate',        $max_date->isBeforeOrEqual($empty_date),        false);
		$this->assume('maxBeforeTodayDate',        $max_date->isBeforeOrEqual($today_date),        false);
		$this->assume('minBeforeMinDate',          $max_date->isBeforeOrEqual($min_date),          false);
		$this->assume('maxBeforeMaxDate',          $max_date->isBeforeOrEqual($max_date),          true);
		$this->method('-');
	}

	//----------------------------------------------------------------------------------- testToMonth
	public function testToMonth()
	{
		$month = (new Date_Time('2016-06-04 12:35:00'))->toMonth()->format('Y-m-d H:i:s');
		$this->assume(__METHOD__, $month, '2016-06-01 00:00:00');
	}

}
