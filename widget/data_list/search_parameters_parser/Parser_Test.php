<?php
namespace ITRocks\Framework\Widget\Data_List\Search_Parameters_Parser;

use ITRocks\Framework\Locale;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Widget\Data_List\Search_Parameters_Parser;
use ITRocks\Framework\Tests\Objects\Document;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Search parameters parser unit tests
 */
class Parser_Test extends Test
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * Business object to use for tests. Should contains properties of type required for tests
	 *
	 * @var Search_Parameters_Parser
	 */
	private $class_name;

	//--------------------------------------------------------------------------- $date_format_backup
	/**
	 * @var string
	 */
	private static $date_format_backup;

	//--------------------------------------------------------------------------------------- $parser
	/**
	 * Internal object use to simulate environment for parsing
	 *
	 * @var Search_Parameters_Parser
	 */
	private $parser;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * The constructor builds an environment to test parameters parser with some simulated fields
	 */
	public function __construct()
	{
		parent::__construct();

		// TODO Build
		$this->class_name = Document::class;
		$this->parser     = new Search_Parameters_Parser($this->class_name);
		// init the date we base upon for tests
		Date::initDates(new Date_Time('2016-06-15 12:30:45'));
	}

	//------------------------------------------------------------------------------ setUpBeforeClass
	/**
	 * {@inheritdoc}
	 */
	public static function setUpBeforeClass()
	{
		$date_format              = Locale::current()->date_format;
		self::$date_format_backup = $date_format->format;
		$date_format->format      = 'd/m/Y';
	}

	//---------------------------------------------------------------------------- tearDownAfterClass
	/**
	 * {@inheritdoc}
	 */
	public static function tearDownAfterClass()
	{
		Locale::current()->date_format->format = self::$date_format_backup;
	}

	//--------------------------------------------------------- testCorrectionOfDateExprWithWildcards
	/**
	 * Test date parser for correction of date expr with wildcards
	 */
	public function testCorrectionOfDateExprWithWildcards()
	{
		$tests = [
			'%%%%' => '____',
			'%%%'  => '____',
			'%%'   => '____',
			'%'    => '____',

			'2%%%' => '2___',
			'2%%'  => '2___',
			'2%'   => '2___',
			'_%'   => '____', // additional test

			'20%%' => '20__',
			'20%'  => '20__',
			'_0%'  => '_0__', // additional test

			'%%%6' => '___6',
			'%%6'  => '___6',
			'%6'   => '___6',
			'_%6'  => '___6', // additional test

			'%%16' => '__16',
			'%16'  => '__16',
			'_%16' => '__16', // additional test
			'%_16' => '__16', // additional test
			'%%_6' => '___6', // additional test
			'%%__' => '____', // additional test

			'2%%6' => '2__6',
			'2%6'  => '2__6',
			'2%_6' => '2__6', // additional test

			'%016' => '_016',
			'2%16' => '2_16',
			'20%6' => '20_6',
			'201%' => '201_',
			'2_%6' => '2__6', // additional test

			'%0%6' => '_0_6',
			'%01%' => '_01_',
			'2%1%' => '2_1_'
		];
		foreach($tests as $check => $assume) {
			Date::checkDateWildcardExpr($check, Date_Time::YEAR);
			$this->assertEquals($assume, $check);
		}
		$tests = [
			'%%' => '__',
			'%'  => '__',
			'%_' => '__', // additional test

			'2%' => '2_',
			'_%' => '__', // additional test

			'%6' => '_6',
			'_6' => '_6'
		];
		foreach($tests as $check => $assume) {
			Date::checkDateWildcardExpr($check, Date_Time::DAY);
			$this->assertEquals($assume, $check);
		}
	}

	//------------------------------------------------------------------------------ testParseAndExpr
	/**
	 * Test date parser for a simple AND
	 */
	public function testParseAndExpr()
	{
		$this->parser->search = ['number' => 'xxx&yyy'];
		$check                = $this->parser->parse();
		$assume               = [];
		$assume['number']     = Func::andOp(['xxx', 'yyy']);
		$this->assertEquals($assume, $check);
	}

	//------------------------------------------------------------------------- testParseBooleanFalse
	/**
	 * Test date parser for a simple AND
	 */
	public function testParseBooleanFalse()
	{
		$this->parser->search = ['has_workflow' => '0,false,no,n'];
		$check                = $this->parser->parse();
		$assume               = [];
		$assume['has_workflow'] = Func::orOp([
			Func::equal('0'), Func::equal('0'), Func::equal('0'), Func::equal('0')
		]);
		$this->assertEquals($assume, $check);
	}

	//-------------------------------------------------------------------------- testParseBooleanTrue
	/**
	 * Test date parser for a simple AND
	 */
	public function testParseBooleanTrue()
	{
		$this->parser->search   = ['has_workflow' => '1,2,3.5,true,yes,y'];
		$check                  = $this->parser->parse();
		$assume                 = [];
		$assume['has_workflow'] = Func::orOp([
			Func::equal('1'), Func::equal('1'), Func::equal('1'),
			Func::equal('1'), Func::equal('1'), Func::equal('1')
		]);
		$this->assertEquals($assume, $check);
	}

	//---------------------------------------------------------------------- testParseBooleanWildcard
	/**
	 * Test date parser for a simple AND
	 */
	public function testParseBooleanWildcard()
	{
		$this->parser->search   = ['has_workflow' => '**'];
		$check                  = $this->parser->parse();
		$assume                 = [];
		$assume['has_workflow'] = Func::orOp([1, 0]);
		$this->assertEquals($assume, $check);
	}

	//-------------------------------------------------------------- testParseDateCompareWithFormulas
	/**
	 * Test date parser for a date comparison with many formulas
	 */
	public function testParseDateCompareWithFormulas()
	{
		$this->parser->search = [
			'date' => '<d-1/m-1/y-1, >d+1/m+1/y+1'
		];
		$check  = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				new Func\Comparison('<', '2015-05-14 00:00:00'),
				new Func\Comparison('>', '2017-07-16 23:59:59'),
			])
		];
		$this->assertEquals($assume, $check);
	}

	//-------------------------------------------------------------------------- testParseDateAndTime
	/**
	 * Test date parser for a full date DD/MM/YYYY with FUll time HH:II:SS
	 */
	public function testParseDateAndTime()
	{
		$this->parser->search = [
			'date'	=> '05/03/2015 20:45:57,5/3/2015 8:5:6,05/3/2015 0:0:0,5/03/2015 23:59:59'
		];
		$check  = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				'2015-03-05 20:45:57',
				'2015-03-05 08:05:06',
				'2015-03-05 00:00:00',
				'2015-03-05 23:59:59'
			])
		];
		$this->assertEquals($assume, $check);
	}

	//----------------------------------------------------------------------- testParseDateEmptyWords
	/**
	 * Test date parser for date empty words
	 */
	public function testParseDateEmptyWords()
	{
		$this->parser->search = ['date' => 'empty,none, null '];
		$check                = $this->parser->parse();
		$assume = [ 'date' => Func::orOp([ Func::isNull(), Func::isNull(), Func::isNull() ]) ];
		$this->assertEquals($assume, $check);
	}

	//----------------------------------------------------------------------------- testParseDateFull
	/**
	 * Test date parser for a full date DD/MM/YYYY
	 */
	public function testParseDateFull()
	{
		$this->parser->search = ['date' => '05/03/2015,5/3/2015,05/3/2015,5/03/2015'];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				new Func\Range('2015-03-05 00:00:00', '2015-03-05 23:59:59'),
				new Func\Range('2015-03-05 00:00:00', '2015-03-05 23:59:59'),
				new Func\Range('2015-03-05 00:00:00', '2015-03-05 23:59:59'),
				new Func\Range('2015-03-05 00:00:00', '2015-03-05 23:59:59')
			])
		];
		$this->assertEquals($assume, $check);
	}

	//--------------------------------------------------------------------- testParseDateHoursMinutes
	/**
	 * Test date parser for a full date DD/MM/YYYY with hours and minutes, not seconds
	 */
	public function testParseDateHoursMinutes()
	{
		$this->parser->search = [
			'date' => '05/03/2015 20:45,5/3/2015 8:5,05/3/2015 0:0,5/03/2015 23:59'
		];
		$check  = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				new Func\Range('2015-03-05 20:45:00', '2015-03-05 20:45:59'),
				new Func\Range('2015-03-05 08:05:00', '2015-03-05 08:05:59'),
				new Func\Range('2015-03-05 00:00:00', '2015-03-05 00:00:59'),
				new Func\Range('2015-03-05 23:59:00', '2015-03-05 23:59:59')
			])
		];
		$this->assertEquals($assume, $check);
	}

	//------------------------------------------------------------------------ testParseDateHoursOnly
	/**
	 * Test date parser for a full date DD/MM/YYYY with hours , not minutes not seconds
	 */
	public function testParseDateHoursOnly()
	{
		$this->parser->search = [
			'date' => '05/03/2015 20,5/3/2015 8,05/3/2015 0,5/03/2015 23'
		];
		$check  = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				new Func\Range('2015-03-05 20:00:00', '2015-03-05 20:59:59'),
				new Func\Range('2015-03-05 08:00:00', '2015-03-05 08:59:59'),
				new Func\Range('2015-03-05 00:00:00', '2015-03-05 00:59:59'),
				new Func\Range('2015-03-05 23:00:00', '2015-03-05 23:59:59')
			])
		];
		$this->assertEquals($assume, $check);
	}

	//---------------------------------------------------------------- testParseDateRangeWithFormulas
	/**
	 * Test date parser for a date range with many formulas
	 */
	public function testParseDateRangeWithFormulas()
	{
		$this->parser->search = [
			'date'
				=> 'd/m/y-m+1, 1/m+1/y-1-d-3/06/y, d-7/m+2/y-3 - d+7/m-2/y ,'
					. ' d-7/m+2/y-3-d+7/m-2/y, d/m-1/y-d/m/y'
		];
		$check  = $this->parser->parse();
		$assume = [
			'date' => Func::orOp([
				new Func\Range('2016-06-15 00:00:00', '2016-07-31 23:59:59'),
				new Func\Range('2015-07-01 00:00:00', '2016-06-12 23:59:59'),
				new Func\Range('2013-08-08 00:00:00', '2016-04-22 23:59:59'),
				new Func\Range('2013-08-08 00:00:00', '2016-04-22 23:59:59'),
				new Func\Range('2016-05-15 00:00:00', '2016-06-15 23:59:59')
			])
		];
		$this->assertEquals($assume, $check);
	}

	//----------------------------------------------------------------- testParseDateTimeWithFormulas
	/**
	 * Test date parser for a date with many formulas
	 */
	public function testParseDateTimeWithFormulas()
	{
		$this->parser->search = ['date' => 'd/m/y h:m:s-1,d/m/y h-1, d/m/y h-1:5, d/m/y 13:m-1'];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				'2016-06-15 12:30:44',
				new Func\Range('2016-06-15 11:00:00', '2016-06-15 11:59:59'),
				new Func\Range('2016-06-15 11:05:00', '2016-06-15 11:05:59'),
				new Func\Range('2016-06-15 13:29:00', '2016-06-15 13:29:59')
			])
		];
		$this->assume(__FUNCTION__, $check, $assume);
	}

	//----------------------------------------------------------------- testParseDateTimeWithWildcard
	/**
	 * Test date parser for a full date DD/MM/YYYY with wildcard
	 */
	public function testParseDateTimeWithWildcard()
	{
		$this->parser->search = ['date' => '05/*/2015 2*,?/3/20?5 2*:1*,05/3/20* 2*:1*:*0'];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				Func::like('2015-__-05 2_:__:__'),
				Func::like('20_5-03-__ 2_:1_:__'),
				Func::like('20__-03-05 2_:1_:_0'),
			])
		];
		$this->assertEquals($assume, $check);
	}

	//------------------------------------------------------------------------- testParseDateWildcard
	/**
	 * Test date parser for current year words
	 */
	public function testParseDateWildcard()
	{
		$this->parser->search = ['date' => '*,**,*?,?,??'];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				/*Func::like('____-__-__ __:__:__'),
				Func::like('____-__-__ __:__:__'),
				Func::like('____-__-__ __:__:__'),
				Func::like('____-__-__ __:__:__'),
				Func::like('____-__-__ __:__:__')*/
				Func::notNull(),
				Func::notNull(),
				Func::notNull(),
				Func::notNull(),
				Func::notNull()
			])
		];
		$this->assertEquals($assume, $check);
	}

	//--------------------------------------------------------------------- testParseDateWithFormulas
	/**
	 * Test date parser for a date with many formulas
	 */
	public function testParseDateWithFormulas()
	{
		$this->parser->search = ['date' => 'd/m/y,1/m+1/2016, 1/m+1/y-1, d-3/06/y, d-7/m+2/y-3'];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				new Func\Range('2016-06-15 00:00:00', '2016-06-15 23:59:59'),
				new Func\Range('2016-07-01 00:00:00', '2016-07-01 23:59:59'),
				new Func\Range('2015-07-01 00:00:00', '2015-07-01 23:59:59'),
				new Func\Range('2016-06-12 00:00:00', '2016-06-12 23:59:59'),
				new Func\Range('2013-08-08 00:00:00', '2013-08-08 23:59:59')
			])
		];
		$this->assume(__FUNCTION__, $check, $assume);
	}

	//--------------------------------------------------------------------- testParseDateWithWildcard
	/**
	 * Test date parser for a full date DD/MM/YYYY with wildcard
	 */
	public function testParseDateWithWildcard()
	{
		$this->parser->search = ['date' => '05/*/2015,?/3/20?5,05/3/20*,*/?/2015'];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				Func::like('2015-__-05 __:__:__'),
				Func::like('20_5-03-__ __:__:__'),
				Func::like('20__-03-05 __:__:__'),
				Func::like('2015-__-__ __:__:__')
			])
		];
		$this->assertEquals($assume, $check);
	}

	//----------------------------------------------------------------------------- testParseDateZero
	/**
	 * Test date parser for '00/00/0000'
	 */
	public function testParseDateZero()
	{
		$this->parser->search = ['date' => '00/00/0000,00/00,00/0000,0000,0'];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				Func::isNull(), Func::isNull(), Func::isNull(), Func::isNull(), Func::isNull()
			])
		];
		$this->assertEquals($assume, $check);
	}

	//----------------------------------------------------------------------------- testParseDayMonth
	/**
	 * Test date parser for a date DD/MM
	 */
	public function testParseDayMonth()
	{
		$this->parser->search = ['date' => '05/03,5/3,05/3,5/03'];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				new Func\Range('2016-03-05 00:00:00', '2016-03-05 23:59:59'),
				new Func\Range('2016-03-05 00:00:00', '2016-03-05 23:59:59'),
				new Func\Range('2016-03-05 00:00:00', '2016-03-05 23:59:59'),
				new Func\Range('2016-03-05 00:00:00', '2016-03-05 23:59:59')
			])
		];
		$this->assertEquals($assume, $check);
	}

	//------------------------------------------------------------------------------ testParseDayOnly
	/**
	 * Test date parser for a single day DD
	 */
	public function testParseDayOnly()
	{
		$this->parser->search = ['date' => '05,5'];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				new Func\Range('2016-06-05 00:00:00', '2016-06-05 23:59:59'),
				new Func\Range('2016-06-05 00:00:00', '2016-06-05 23:59:59')
			])
		];
		$this->assertEquals($assume, $check);
	}

	//----------------------------------------------------------------------------- testParseDayWords
	/**
	 * Test date parser for current day words
	 */
	public function testParseDayWords()
	{
		$this->parser->search = ['date' => 'today,currentday, current day ,yesterday'];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				new Func\Range('2016-06-15 00:00:00', '2016-06-15 23:59:59'),
				new Func\Range('2016-06-15 00:00:00', '2016-06-15 23:59:59'),
				new Func\Range('2016-06-15 00:00:00', '2016-06-15 23:59:59'),
				new Func\Range('2016-06-14 00:00:00', '2016-06-14 23:59:59')
			])
		];
		$this->assertEquals($assume, $check);
	}

	//--------------------------------------------------------------------------- testParseEmptyWords
	/**
	 * Test date parser for empty words
	 */
	public function testParseEmptyWords()
	{
		//TODO: Do CHeck forcing FR and EN locales
		$this->parser->search = ['number' => 'empty,none,null'];
		$check                = $this->parser->parse();
		$assume = [ 'number' => Func::orOp([ Func::isNull(), Func::isNull(), Func::isNull() ]) ];
		$this->assertEquals($assume, $check);
	}

	//------------------------------------------------------------------------------ testParseInRange
	/**
	 * Test date parser for a simple range
	 */
	public function testParseInRange()
	{
		$this->parser->search = ['number' => 'xxx-yyy'];
		$check                = $this->parser->parse();
		$assume               = ['number' => new Func\Range('xxx', 'yyy')];
		$this->assertEquals($assume, $check);
	}

	//------------------------------------------------------------------ testParseInRangeWithWildcard
	/**
	 * Test date parser for a simple range
	 */
	public function testParseInRangeWithWildcard()
	{
		$this->parser->search = ['number' => 'x*x-y?y'];
		$check                = $this->parser->parse();
		$assume               = ['number' => new Func\Range('x%x', 'y_y')];
		$this->assertEquals($assume, $check);
	}

	//--------------------------------------------------------------------------- testParseMonthWords
	/**
	 * Test date parser for current month words
	 */
	public function testParseMonthWords()
	{
		$this->parser->search = ['date' => 'currentmonth, current month '];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				new Func\Range('2016-06-01 00:00:00', '2016-06-30 23:59:59'),
				new Func\Range('2016-06-01 00:00:00', '2016-06-30 23:59:59')
			])
		];
		$this->assertEquals($assume, $check);
	}

	//---------------------------------------------------------------------------- testParseMonthYear
	/**
	 * Test date parser for a date MM/YYYY
	 */
	public function testParseMonthYear()
	{
		$this->parser->search = ['date' => '06/2016,6/2016,2016/06,2016/6'];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				new Func\Range('2016-06-01 00:00:00', '2016-06-30 23:59:59'),
				new Func\Range('2016-06-01 00:00:00', '2016-06-30 23:59:59'),
				new Func\Range('2016-06-01 00:00:00', '2016-06-30 23:59:59'),
				new Func\Range('2016-06-01 00:00:00', '2016-06-30 23:59:59')
			])
		];
		$this->assertEquals($assume, $check);
	}

	//---------------------------------------------------------------- testParseMonthYearWithFormulas
	/**
	 * Test date parser for a date m-1/y-1 where it should correctly detect month and year parts
	 */
	public function testParseMonthYearWithFormulas()
	{
		$this->parser->search = ['date' => 'm-1/y-1'];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => new Func\Range('2015-05-01 00:00:00', '2015-05-31 23:59:59')
		];
		$this->assertEquals($assume, $check);
	}

	//------------------------------------------------------------------------------ testParseNotExpr
	/**
	 * Test date parser for a simple NOT
	 */
	public function testParseNotExpr()
	{
		$this->parser->search = ['number' => '!xxx'];
		$check                = $this->parser->parse();
		$assume               = ['number' => Func::notEqual('xxx')];
		$this->assertEquals($assume, $check);
	}

	//--------------------------------------------------------------------------- testParseNotInRange
	/**
	 * Test date parser for a simple not in range
	 */
	public function testParseNotInRange()
	{
		$this->parser->search = ['number' => '!xxx-yyy'];
		$check                = $this->parser->parse();
		$assume               = ['number' => new Func\Range('xxx', 'yyy', true)];
		$this->assertEquals($assume, $check);
	}

	//---------------------------------------------------------------------- testParseNotWithWildcard
	/**
	 * Test date parser for a NOT with Wildcards
	 */
	public function testParseNotWithWildcard()
	{
		$this->parser->search = ['number' => '!x*x'];
		$check                = $this->parser->parse();
		$assume               = ['number' => Func::notLike('x%x')];
		$this->assertEquals($assume, $check);
	}

	//------------------------------------------------------------------------------- testParseOrExpr
	/**
	 * Test date parser for a simple OR
	 */
	public function testParseOrExpr()
	{
		$this->parser->search = ['number' => 'xxx,yyy'];
		$check                = $this->parser->parse();
		$assume               = ['number' => Func::orOp(['xxx', 'yyy'])];
		$this->assertEquals($assume, $check);
	}

	//---------------------------------------------------------------------------- testParseOrWithAnd
	/**
	 * Test date parser for both OR with AND
	 */
	public function testParseOrWithAnd()
	{
		$this->parser->search = ['number' => 'www&xxx,yyy&zzz,aaa'];
		$check                = $this->parser->parse();
		$assume              = [
			'number' => Func::orOp([
				Func::andOp(['www', 'xxx']),
				Func::andOp(['yyy', 'zzz']),
				'aaa'
			])
		];
		$this->assertEquals($assume, $check);
	}

	//--------------------------------------------------------------------- testParseOrWithAndWithNot
	/**
	 * test date parser for OR with AND
	 */
	public function testParseOrWithAndWithNot()
	{
		$this->parser->search = ['number' => 'www&!xxx,!yyy&zzz,!aaa,bbb'];
		$check                = $this->parser->parse();
		$assume               = [
			'number' => Func::orOp([
				Func::andOp(['www', Func::notEqual('xxx')]),
				Func::andOp([Func::notEqual('yyy'), 'zzz']),
				Func::notEqual('aaa'),
				'bbb'
			])
		];
		$this->assertEquals($assume, $check);
	}

	//----------------------------------------------- testParseOrWithAndWithNotWithRangeWithWildcards
	/**
	 * Test date parser for OR with AND
	 */
	public function testParseOrWithAndWithNotWithRangeWithWildcards()
	{
		$this->parser->search = ['number' => 'a*a-bb%&!*cc,!d?d-?e?&*f?,!g_g-h*?'];
		$check                = $this->parser->parse();
		$assume               = [
			'number' => Func::orOp([
				Func::andOp([ new Func\Range('a%a', 'bb%'), Func::notLike('%cc') ]),
				Func::andOp([ new Func\Range('d_d', '_e_', true), Func::like('%f_') ]),
				new Func\Range('g_g', 'h%_', true)
			])
		];
		$this->assertEquals($assume, $check);
	}

	//---------------------------------------------------------------------- testParseScalarFloatType
	/**
	 * Test date parser for a simple scalar of type integer
	 */
	public function testParseScalarFloatType()
	{
		$this->parser->search = ['number' => '1.1'];
		$check                = $this->parser->parse();
		$assume               = ['number' => '1.1'];
		$this->assertEquals($assume, $check);
	}

	//-------------------------------------------------------------------- testParseScalarIntegerType
	/**
	 * Test date parser for a simple scalar of type integer
	 */
	public function testParseScalarIntegerType()
	{
		$this->parser->search = ['number' => '1'];
		$check                = $this->parser->parse();
		$assume               = ['number' => '1'];
		$this->assertEquals($assume, $check);
	}

	//--------------------------------------------------------------------- testParseScalarStringType
	/**
	 * Test date parser for a simple scalar of type string
	 */
	public function testParseScalarStringType()
	{
		$this->parser->search = ['number' => 'xxx'];
		$check                = $this->parser->parse();
		$assume               = ['number' => 'xxx'];
		$this->assertEquals($assume, $check);
	}

	//------------------------------------------------------------------ testParseScalarWithWildcards
	/**
	 * Test date parser for a simple scalar with wildcards
	 */
	public function testParseScalarWithWildcards()
	{
		$this->parser->search = ['number' => 'w?wx*y_yz%'];
		$check                = $this->parser->parse();
		$assume               = ['number' => Func::like('w_wx%y_yz%')];
		$this->assertEquals($assume, $check);
	}

	//--------------------------------------------------------------------- testParseSingleDayFormula
	/**
	 * Test date parser for a single day formula
	 */
	public function testParseSingleDayFormula()
	{
		$this->parser->search = ['date' => 'd+4,d-4,d,D'];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				new Func\Range('2016-06-19 00:00:00', '2016-06-19 23:59:59'),
				new Func\Range('2016-06-11 00:00:00', '2016-06-11 23:59:59'),
				new Func\Range('2016-06-15 00:00:00', '2016-06-15 23:59:59'),
				new Func\Range('2016-06-15 00:00:00', '2016-06-15 23:59:59')
			])
		];
		$this->assertEquals($assume, $check);
	}

	//------------------------------------------------------------------- testParseSingleMonthFormula
	/**
	 * Test date parser for a single month formula
	 */
	public function testParseSingleMonthFormula()
	{
		$this->parser->search = ['date' => 'm+4,m-4,m,M'];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				new Func\Range('2016-10-01 00:00:00', '2016-10-31 23:59:59'),
				new Func\Range('2016-02-01 00:00:00', '2016-02-29 23:59:59'),
				new Func\Range('2016-06-01 00:00:00', '2016-06-30 23:59:59'),
				new Func\Range('2016-06-01 00:00:00', '2016-06-30 23:59:59')
			])
		];
		$this->assertEquals($assume, $check);
	}

	//-------------------------------------------------------------------- testParseSingleYearFormula
	/**
	 * Test date parser for a single year formula
	 */
	public function testParseSingleYearFormula()
	{
		$this->parser->search = ['date' => 'y+5,y-5,y,Y'];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				new Func\Range('2021-01-01 00:00:00', '2021-12-31 23:59:59'),
				new Func\Range('2011-01-01 00:00:00', '2011-12-31 23:59:59'),
				new Func\Range('2016-01-01 00:00:00', '2016-12-31 23:59:59'),
				new Func\Range('2016-01-01 00:00:00', '2016-12-31 23:59:59')
			])
		];
		$this->assertEquals($assume, $check);
	}

	//--------------------------------------------------------------------------------- testParseYear
	/**
	 * Test date parser for a date YYYY
	 */
	public function testParseYear()
	{
		$this->parser->search = ['date' => '2016, 2016 '];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				new Func\Range('2016-01-01 00:00:00', '2016-12-31 23:59:59'),
				new Func\Range('2016-01-01 00:00:00', '2016-12-31 23:59:59')
			])
		];
		$this->assertEquals($assume, $check);
	}

	//--------------------------------------------------------------------- testParseYearWithWildcard
	/**
	 * Test date parser for a date YYYY with wildcard
	 */
	public function testParseYearWithWildcard()
	{
		$this->parser->search = ['date' => '2*6, 201*,201?,20?6'];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				Func::like('2__6-__-__ __:__:__'),
				Func::like('201_-__-__ __:__:__'),
				Func::like('201_-__-__ __:__:__'),
				Func::like('20_6-__-__ __:__:__')
			])
		];
		$this->assertEquals($assume, $check);
	}

	//---------------------------------------------------------------------------- testParseYearWords
	/**
	 * Test date parser for current year words
	 */
	public function testParseYearWords()
	{
		$this->parser->search = ['date' => 'currentyear, current year '];
		$check                = $this->parser->parse();
		$assume               = [
			'date' => Func::orOp([
				new Func\Range('2016-01-01 00:00:00', '2016-12-31 23:59:59'),
				new Func\Range('2016-01-01 00:00:00', '2016-12-31 23:59:59')
			])
		];
		$this->assertEquals($assume, $check);
	}

}
