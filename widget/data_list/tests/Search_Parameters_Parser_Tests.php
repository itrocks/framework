<?php
namespace SAF\Framework\Widget\Data_List\Tests;

use SAF\Framework\Tests\Test;
use SAF\Framework\Widget\Data_List\Search_Parameters_Parser;
use SAF\Framework\Tests\Objects\Document;
use SAF\Framework\Dao\Func;
use SAF\Framework\Dao\Func\Range;
use SAF\Framework\Dao\Option;
//use SAF\Framework\Locale\Date_Format;
use SAF\Framework\Tools\Date_Time;

/**
 * Tests that should pass date and date time column
 *
 * Note: These tests are greatly dependants of object structure managed by SAF\Framework\Dao !!!
 *
 *
 * 2016
 * 03/2016
 * 3/2016
 * 05/03/2016
 * 5/3/2016
 * 05/3/2016
 * 5/03/2016
 * 05 (day of current month/year)
 * 5 (day of current month/year)
 * 05/03 (day and month of current year)
 * 5/3 (day and month of current year)
 * 05/3 (day and month of current year)
 * 5/03 (day and month of current year)
 * vide
 * aucun
 * aucune
 * nul

 * 2015-2016
 * 2015-03/2016
 * 2015-3/2016
 * 2015-05/03/2016
 * 2015-5/3/2016
 * 2015-05/3/2016
 * 2015-5/03/2016
 * 2015-05
 * 2015-5
 * 2015-05/03
 * 2015-5/3
 * 2015-05/3
 * 2015-5/03
 *
 * 07/2015-2016
 * ...
 *
 * 7/2015-2016
 * ...
 *
 * 09/07/2015-2016
 * ...
 *
 * 9/7/2015-2016
 * ...
 *
 * 09/7/2015-2016
 * ...
 *
 * 9/07/2015-2016
 * ...
 *
 * 09/2015-2016
 * ...
 *
 * 9/2015-2016
 * ...
 *
 * 09/07-2016
 * ...
 *
 * 9/7-2016
 * ...
 *
 * 09/7-2016
 * ...
 *
 * 9/07-2016
 * ...
 *
 */

/**
 * Extension to change some protected method to public method for tests
 */
class Search_Parameters_Parser_ExtendedForTests extends Search_Parameters_Parser
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 * @param $search     array user-input search string
	 */
	public function __construct($class_name, $search)
	{
		parent::__construct($class_name, $search);
		$this->currentDateTime = new Date_Time('2016-06-15 12:30:45');
		$this->currentYear = $this->currentDateTime->format('Y');
		$this->currentMonth = $this->currentDateTime->format('m');
		$this->currentDay = $this->currentDateTime->format('d');
		$this->currentHour = $this->currentDateTime->format('H');
		$this->currentMinutes = $this->currentDateTime->format('i');
		$this->currentSeconds = $this->currentDateTime->format('s');
	}

	//---------------------------------------------------------------------- _correctDateWildcardExpr
	/**
	 * @param string $expr
	 * @param $part string Date_Time::DAY | Date_Time::MONTH | Date_Time::YEAR | Date_Time::HOUR | Date_Time::MINUTE | Date_Time::SECOND
	 * @return void
	 */
	public function _correctDateWildcardExpr(&$expr, $part)
	{
		parent::_correctDateWildcardExpr($expr, $part);
	}
}

/**
 * Array function unit tests
 */
class Search_Parameters_Parser_Tests extends Test
{

	//--------------------------------------------------------------------------------------- $parser
	/**
	 * Internal object use to simulate environment for parsing
	 *
	 * @var Search_Parameters_Parser_ExtendedForTests
	 */
	private $parser;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * Business object to use for tests. Should contains properties of type required for tests
	 *
	 * @var Search_Parameters_Parser
	 */
	private $class_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * contructor build environnement to test parameters parser with some simulated fields
	 *
	 */
	public function __construct()
	{
		//TODO: Build
		$this->class_name = Document::class;
		$this->parser = new Search_Parameters_Parser_ExtendedForTests($this->class_name, null);
	}


	//--------------------------------------------------------- testCorrectionOfDateExprWithWildcards
	/**
	 * test date parser for correction of date expr with wildcards
	 *
	 * @return boolean
	 */
	public function testCorrectionOfDateExprWithWildcards()
	{
		$ok = true;
		$tests = [
			'%%%%' => '____',
			'%%%'  => '____',
			'%%'   => '____',
			'%'    => '____',

			'2%%%' => '2___',
			'2%%'  => '2___',
			'2%'   => '2___',
			'_%'   => '____', /* additional test */

			'20%%' => '20__',
			'20%'  => '20__',
			'_0%'  => '_0__', /* additional test */

			'%%%6' => '___6',
			'%%6'  => '___6',
			'%6'   => '___6',
			'_%6'  => '___6', /* additional test */

			'%%16' => '__16',
			'%16'  => '__16',
			'_%16' => '__16', /* additional test */
			'%_16' => '__16', /* additional test */
			'%%_6' => '___6', /* additional test */
			'%%__' => '____', /* additional test */

			'2%%6' => '2__6',
			'2%6'  => '2__6',
			'2%_6' => '2__6', /* additional test */

			'%016' => '_016',
			'2%16' => '2_16',
			'20%6' => '20_6',
			'201%' => '201_',
			'2_%6' => '2__6', /* additional test */

			'%0%6' => '_0_6',
			'%01%' => '_01_',
			'2%1%' => '2_1_'
		];
		foreach($tests as $tocheck => $assume) {
			$check = $tocheck;
			$this->parser->_correctDateWildcardExpr($check, Date_Time::YEAR);
			$lok =$this->assume(__FUNCTION__.'_Year('.$tocheck.' => '.$assume.')', $check, $assume, false);
			$ok = $ok && $lok;
		}
		$tests = [
			'%%' => '__',
			'%'  => '__',
			'%_' => '__', /* additional test */

			'2%' => '2_',
			'_%' => '__', /* additional test */

			'%6' => '_6',
			'_6' => '_6'
		];
		foreach($tests as $tocheck => $assume) {
			$check = $tocheck;
			$this->parser->_correctDateWildcardExpr($check, Date_Time::DAY);
			$lok =$this->assume(__FUNCTION__.'_Day("'.$tocheck.'" => '.$assume.')', $check, $assume, false);
			$ok = $ok && $lok;
		}
		return $ok;
	}


	//------------------------------------------------------------------------------- testParseOrExpr
	/**
	 * test date parser for a simple OR
	 *
	 * @return boolean
	 */
	public function testParseOrExpr()
	{
		$this->parser->search = ['number' => "xxx,yyy"];
		$check = $this->parser->parse();
		$assume = [];
		$assume['number'] = Func::orOp(["xxx", "yyy"]);
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//------------------------------------------------------------------------------ testParseAndExpr
	/**
	 * test date parser for a simple AND
	 *
	 * @return boolean
	 */
	public function testParseAndExpr()
	{
		$this->parser->search = ['number' => "xxx&yyy"];
		$check = $this->parser->parse();
		$assume = [];
		$assume['number'] = Func::andOp(["xxx", "yyy"]);
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//------------------------------------------------------------------------------ testParseNotExpr
	/**
	 * test date parser for a simple NOT
	 *
	 * @return boolean
	 */
	public function testParseNotExpr()
	{
		$this->parser->search = ['number' => "!xxx"];
		$check = $this->parser->parse();
		$assume = [];
		$assume['number'] = Func::notEqual("xxx");
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//---------------------------------------------------------------------- testParseNotWithWildcard
	/**
	 * test date parser for a NOT with Wildcards
	 *
	 * @return boolean
	 */
	public function testParseNotWithWildcard()
	{
		$this->parser->search = ['number' => "!x*x"];
		$check = $this->parser->parse();
		$assume = [];
		$assume['number'] = Func::notLike("x%x");
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//--------------------------------------------------------------------- testParseScalarStringType
	/**
	 * test date parser for a simple scalar of type string
	 *
	 * @return boolean
	 */
	public function testParseScalarStringType()
	{
		$this->parser->search = ['number' => "xxx"];
		$check = $this->parser->parse();
		$assume = [];
		$assume['number'] = "xxx";
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//-------------------------------------------------------------------- testParseScalarIntegerType
	/**
	 * test date parser for a simple scalar of type integer
	 *
	 * @return boolean
	 */
	public function testParseScalarIntegerType()
	{
		$this->parser->search = ['number' => "1"];
		$check = $this->parser->parse();
		$assume = [];
		$assume['number'] = "1";
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//---------------------------------------------------------------------- testParseScalarFloatType
	/**
	 * test date parser for a simple scalar of type integer
	 *
	 * @return boolean
	 */
	public function testParseScalarFloatType()
	{
		$this->parser->search = ['number' => "1.1"];
		$check = $this->parser->parse();
		$assume = [];
		$assume['number'] = "1.1";
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//------------------------------------------------------------------ testParseScalarWithWildcards
	/**
	 * test date parser for a simple scalar with jokers
	 *
	 * @return boolean
	 */
	public function testParseScalarWithWildcards()
	{
		$this->parser->search = ['number' => "w?wx*y_yz%"];
		$check = $this->parser->parse();
		$assume = [];
		$assume['number'] = Func::like("w_wx%y_yz%");
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//--------------------------------------------------------------------------- testParseEmptyWords
	/**
	 * test date parser for empty words
	 *
	 * @return boolean
	 */
	public function testParseEmptyWords()
	{
		//TODO: Do CHeck forcing FR and EN locales
		$this->parser->search = ['number' => "empty,none,null,vide"];
		$check = $this->parser->parse();
		$assume = [];
		$assume['number'] = Func::orOp([
			Func::orOp(['', Func::isNull()]),
			Func::orOp(['', Func::isNull()]),
			Func::orOp(['', Func::isNull()]),
			Func::orOp(['', Func::isNull()])
		]);
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//------------------------------------------------------------------------------ testParseInRange
	/**
	 * test date parser for a simple range
	 *
	 * @return boolean
	 */
	public function testParseInRange()
	{
		$this->parser->search = ['number' => "xxx-yyy"];
		$check = $this->parser->parse();
		$assume = [];
		$assume['number'] = new Range("xxx", "yyy");
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//------------------------------------------------------------------ testParseInRangeWithWildcard
	/**
	 * test date parser for a simple range
	 *
	 * @return boolean
	 */
	public function testParseInRangeWithWildcard()
	{
		$this->parser->search = ['number' => "x*x-y?y"];
		$check = $this->parser->parse();
		$assume = [];
		$assume['number'] = new Range("x%x", "y_y");
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//--------------------------------------------------------------------------- testParseNotInRange
	/**
	 * test date parser for a simple not in range
	 *
	 * @return boolean
	 */
	public function testParseNotInRange()
	{
		$this->parser->search = ['number' => "!xxx-yyy"];
		$check = $this->parser->parse();
		$assume = [];
		$assume['number'] = new Range("xxx", "yyy", true);
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//---------------------------------------------------------------------------- testParseOrWithAnd
	/**
	 * test date parser for both OR with AND
	 *
	 * @return boolean
	 */
	public function testParseOrWithAnd()
	{
		$this->parser->search = ['number' => "www&xxx,yyy&zzz,aaa"];
		$check = $this->parser->parse();
		$assume = [];
		$assume['number'] = Func::orOp([
			Func::andOp(["www", "xxx"]),
			Func::andOp(["yyy", "zzz"]),
			"aaa"
		]);
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//--------------------------------------------------------------------- testParseOrWithAndWithNot
	/**
	 * test date parser for OR with AND
	 *
	 * @return boolean
	 */
	public function testParseOrWithAndWithNot()
	{
		$this->parser->search = ['number' => "www&!xxx,!yyy&zzz,!aaa,bbb"];
		$check = $this->parser->parse();
		$assume = [];
		$assume['number'] = Func::orOp([
			Func::andOp(["www", Func::notEqual("xxx")]),
			Func::andOp([Func::notEqual("yyy"), "zzz"]),
			Func::notEqual("aaa"),
			"bbb"
		]);
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//----------------------------------------------- testParseOrWithAndWithNotWithRangeWithWildcards
	/**
	 * test date parser for OR with AND
	 *
	 * @return boolean
	 */
	public function testParseOrWithAndWithNotWithRangeWithWildcards()
	{
		$this->parser->search = ['number' => "a*a-bb%&!*cc,!d?d-?e?&*f?,!g_g-h*?"];
		$check = $this->parser->parse();
		$assume = [];
		$assume['number'] = Func::orOp([
			Func::andOp([
				new Range("a%a", "bb%"),
				Func::notLike("%cc")]),
			Func::andOp([
				new Range("d_d", "_e_", true),
				Func::like("%f_")
			]),
			new Range("g_g", "h%_", true)
		]);
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//------------------------------------------------------------------------- testParseDateWildcard
	/**
	 * test date parser for current year words
	 *
	 * @return boolean
	 */
	public function testParseDateWildcard()
	{
		$this->parser->search = ['date' => "*,**,*?,?,??"];
		$check = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				Func::like("____-__-__ __:__:__"),
				Func::like("____-__-__ __:__:__"),
				Func::like("____-__-__ __:__:__"),
				Func::like("____-__-__ __:__:__"),
				Func::like("____-__-__ __:__:__")
			])
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//---------------------------------------------------------------------------- testParseYearWords
	/**
	 * test date parser for current year words
	 *
	 * @return boolean
	 */
	public function testParseYearWords()
	{
		$this->parser->search = ['date' => "currentyear,anneecourante,anneeencours, current year , annee courante , annee en cours "];
		$check = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				new Range("2016-01-01 00:00:00", "2016-12-31 23:59:59"),
				new Range("2016-01-01 00:00:00", "2016-12-31 23:59:59"),
				new Range("2016-01-01 00:00:00", "2016-12-31 23:59:59"),
				new Range("2016-01-01 00:00:00", "2016-12-31 23:59:59"),
				new Range("2016-01-01 00:00:00", "2016-12-31 23:59:59"),
				new Range("2016-01-01 00:00:00", "2016-12-31 23:59:59")
			])
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//--------------------------------------------------------------------------- testParseMonthWords
	/**
	 * test date parser for current month words
	 *
	 * @return boolean
	 */
	public function testParseMonthWords()
	{
		$this->parser->search = ['date' => "currentmonth,moiscourant,moisencours, current month , mois courant , mois encours "];
		$check = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				new Range("2016-06-01 00:00:00", "2016-06-30 23:59:59"),
				new Range("2016-06-01 00:00:00", "2016-06-30 23:59:59"),
				new Range("2016-06-01 00:00:00", "2016-06-30 23:59:59"),
				new Range("2016-06-01 00:00:00", "2016-06-30 23:59:59"),
				new Range("2016-06-01 00:00:00", "2016-06-30 23:59:59"),
				new Range("2016-06-01 00:00:00", "2016-06-30 23:59:59")
			])
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//----------------------------------------------------------------------------- testParseDayWords
	/**
	 * test date parser for current day words
	 *
	 * @return boolean
	 */
	public function testParseDayWords()
	{
		$this->parser->search = ['date' => "today,currentday,jourcourant,jourencours,aujourd'hui,aujourdhui, current day ,jour courant,jour en cours,aujourd'hui,aujourd hui"];
		$check = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				new Range("2016-06-15 00:00:00", "2016-06-15 23:59:59"),
				new Range("2016-06-15 00:00:00", "2016-06-15 23:59:59"),
				new Range("2016-06-15 00:00:00", "2016-06-15 23:59:59"),
				new Range("2016-06-15 00:00:00", "2016-06-15 23:59:59"),
				new Range("2016-06-15 00:00:00", "2016-06-15 23:59:59"),
				new Range("2016-06-15 00:00:00", "2016-06-15 23:59:59"),
				new Range("2016-06-15 00:00:00", "2016-06-15 23:59:59"),
				new Range("2016-06-15 00:00:00", "2016-06-15 23:59:59"),
				new Range("2016-06-15 00:00:00", "2016-06-15 23:59:59"),
				new Range("2016-06-15 00:00:00", "2016-06-15 23:59:59"),
				new Range("2016-06-15 00:00:00", "2016-06-15 23:59:59")
			])
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//----------------------------------------------------------------------- testParseDateEmptyWords
	/**
	 * test date parser for date empty words
	 *
	 * @return boolean
	 */
	public function testParseDateEmptyWords()
	{
		$this->parser->search = ['date' => "empty,none, null "];
		$check = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				Func::orOp(['0000-00-00 00:00:00', Func::isNull()]),
				Func::orOp(['0000-00-00 00:00:00', Func::isNull()]),
				Func::orOp(['0000-00-00 00:00:00', Func::isNull()])
			])
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//--------------------------------------------------------------------------------- testParseYear
	/**
	 * test date parser for a date YYYY
	 *
	 * @return boolean
	 */
	public function testParseYear()
	{
		$this->parser->search = ['date' => "2016, 2016 "];
		$check = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				new Range("2016-01-01 00:00:00", "2016-12-31 23:59:59"),
				new Range("2016-01-01 00:00:00", "2016-12-31 23:59:59")
			])
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//--------------------------------------------------------------------- testParseYearWithWildcard
	/**
	 * test date parser for a date YYYY with wildcard
	 *
	 * @return boolean
	 */
	public function testParseYearWithWildcard()
	{
		$this->parser->search = ['date' => "2*6, 201*,201?,20?6"];
		$check = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				Func::like("2__6-__-__ __:__:__"),
				Func::like("201_-__-__ __:__:__"),
				Func::like("201_-__-__ __:__:__"),
				Func::like("20_6-__-__ __:__:__")
			])
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//---------------------------------------------------------------------------- testParseMonthYear
	/**
	 * test date parser for a date MM/YYYY
	 *
	 * @return boolean
	 */
	public function testParseMonthYear()
	{
		$this->parser->search = ['date' => "06/2016,6/2016,2016/06,2016/6"];
		$check = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				new Range("2016-06-01 00:00:00", "2016-06-30 23:59:59"),
				new Range("2016-06-01 00:00:00", "2016-06-30 23:59:59"),
				new Range("2016-06-01 00:00:00", "2016-06-30 23:59:59"),
				new Range("2016-06-01 00:00:00", "2016-06-30 23:59:59")
			])
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//----------------------------------------------------------------------------- testParseDateFull
	/**
	 * test date parser for a full date DD/MM/YYYY
	 *
	 * @return boolean
	 */
	public function testParseDateFull()
	{
		$this->parser->search = ['date' => "05/03/2015,5/3/2015,05/3/2015,5/03/2015"];
		$check = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				new Range("2015-03-05 00:00:00", "2015-03-05 23:59:59"),
				new Range("2015-03-05 00:00:00", "2015-03-05 23:59:59"),
				new Range("2015-03-05 00:00:00", "2015-03-05 23:59:59"),
				new Range("2015-03-05 00:00:00", "2015-03-05 23:59:59")
			])
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//----------------------------------------------------------------- testParseDateFullWithWildcard
	/**
	 * test date parser for a full date DD/MM/YYYY with wildcard
	 *
	 * @return boolean
	 */
	public function testParseDateFullWithWildcard()
	{
		$this->parser->search = ['date' => "05/*/2015,?/3/20?5,05/3/20*,*/?/2015"];
		$check = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				Func::like("2015-__-05 __:__:__"),
				Func::like("20_5-03-__ __:__:__"),
				Func::like("20__-03-05 __:__:__"),
				Func::like("2015-__-__ __:__:__")
			])
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//----------------------------------------------------------------------------- testParseDayMonth
	/**
	 * test date parser for a date DD/MM
	 *
	 * @return boolean
	 */
	public function testParseDayMonth()
	{
		$this->parser->search = ['date' => "05/03,5/3,05/3,5/03"];
		$check = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				new Range("2016-03-05 00:00:00", "2016-03-05 23:59:59"),
				new Range("2016-03-05 00:00:00", "2016-03-05 23:59:59"),
				new Range("2016-03-05 00:00:00", "2016-03-05 23:59:59"),
				new Range("2016-03-05 00:00:00", "2016-03-05 23:59:59")
			])
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//------------------------------------------------------------------------------ testParseDayOnly
	/**
	 * test date parser for a single day DD
	 *
	 * @return boolean
	 */
	public function testParseDayOnly()
	{
		$this->parser->search = ['date' => "05,5"];
		$check = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				new Range("2016-06-05 00:00:00", "2016-06-05 23:59:59"),
				new Range("2016-06-05 00:00:00", "2016-06-05 23:59:59")
			])
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//-------------------------------------------------------------------- testParseSingleYearFormula
	/**
	 * test date parser for a single year formula
	 *
	 * @return boolean
	 */
	public function testParseSingleYearFormula()
	{
		$this->parser->search = ['date' => "y+5,a-5,y,a,Y,A"];
		$check = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				new Range("2021-01-01 00:00:00", "2021-12-31 23:59:59"),
				new Range("2011-01-01 00:00:00", "2011-12-31 23:59:59"),
				new Range("2016-01-01 00:00:00", "2016-12-31 23:59:59"),
				new Range("2016-01-01 00:00:00", "2016-12-31 23:59:59"),
				new Range("2016-01-01 00:00:00", "2016-12-31 23:59:59"),
				new Range("2016-01-01 00:00:00", "2016-12-31 23:59:59")
			])
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//------------------------------------------------------------------- testParseSingleMonthFormula
	/**
	 * test date parser for a single month formula
	 *
	 * @return boolean
	 */
	public function testParseSingleMonthFormula()
	{
		$this->parser->search = ['date' => "m+4,m-4,m,M"];
		$check = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				new Range("2016-10-01 00:00:00", "2016-10-31 23:59:59"),
				new Range("2016-02-01 00:00:00", "2016-02-29 23:59:59"),
				new Range("2016-06-01 00:00:00", "2016-06-30 23:59:59"),
				new Range("2016-06-01 00:00:00", "2016-06-30 23:59:59")
			])
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//--------------------------------------------------------------------- testParseSingleDayFormula
	/**
	 * test date parser for a single day formula
	 *
	 * @return boolean
	 */
	public function testParseSingleDayFormula()
	{
		$this->parser->search = ['date' => "d+4,j-4,d,j,D,J"];
		$check = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				new Range("2016-06-19 00:00:00", "2016-06-19 23:59:59"),
				new Range("2016-06-11 00:00:00", "2016-06-11 23:59:59"),
				new Range("2016-06-15 00:00:00", "2016-06-15 23:59:59"),
				new Range("2016-06-15 00:00:00", "2016-06-15 23:59:59"),
				new Range("2016-06-15 00:00:00", "2016-06-15 23:59:59"),
				new Range("2016-06-15 00:00:00", "2016-06-15 23:59:59")
			])
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//--------------------------------------------------------------------- testParseDateWithFormulas
	/**
	 * test date parser for a date with many formulas
	 *
	 * @return boolean
	 */
	public function testParseDateWithFormulas()
	{
		$this->parser->search = ['date' => "d/m/y,1/m+1/2016, 1/m+1/y-1, d-3/06/y, d-7/m+2/y-3"];
		$check = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				new Range("2016-06-15 00:00:00", "2016-06-15 23:59:59"),
				new Range("2016-07-01 00:00:00", "2016-07-01 23:59:59"),
				new Range("2015-07-01 00:00:00", "2015-07-01 23:59:59"),
				new Range("2016-06-12 00:00:00", "2016-06-12 23:59:59"),
				new Range("2013-08-08 00:00:00", "2013-08-08 23:59:59")
			])
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//---------------------------------------------------------------- testParseDateRangeWithFormulas
	/**
	 * test date parser for a date range with many formulas
	 *
	 * @return boolean
	 */
	public function testParseDateRangeWithFormulas()
	{
		$this->parser->search = ['date' => "d/m/y-m+1, 1/m+1/y-1-d-3/06/y, d-7/m+2/y-3 - d+7/m-2/y , d-7/m+2/y-3-d+7/m-2/y, d/m-1/y-d/m/y"];
		//$this->parser->search = ['date' => "d/m/y-1/m/y"];
		$check = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				new Range("2016-06-15 00:00:00", "2016-07-31 23:59:59"),
				new Range("2015-07-01 00:00:00", "2016-06-12 23:59:59"),
				new Range("2013-08-08 00:00:00", "2016-04-22 23:59:59"),
				new Range("2013-08-08 00:00:00", "2016-04-22 23:59:59"),
				new Range("2016-05-15 00:00:00", "2016-06-15 23:59:59")
			])
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

	//---------------------------------------------------------------- testParseMonthYearWithFormulas
	/**
	 * test date parser for a date m-1/y-1 where it should correctly detect month and year parts
	 *
	 * @return boolean
	 */
	public function testParseMonthYearWithFormulas()
	{
		$this->parser->search = ['date' => "m-1/y-1"];
		$check = $this->parser->parse();
		$assume = [
			'date' => new Range("2015-05-01 00:00:00", "2015-05-31 23:59:59")
		];
		return $this->assume(__FUNCTION__, $check, $assume, false);
	}

}
