<?php
namespace Bappli\Company\Employee\Tests;

use Iterator;
use ITRocks\Framework\AOP\Joinpoint\Method_Joinpoint;
use ITRocks\Framework\AOP\Weaver\IWeaver;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql\Link;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Password;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Access_Control\Authorize_Activated_User;
use PHPUnit\Framework\MockObject\MockObject;

class Authorize_Activated_User_Test extends Test
{

	//------------------------------------------------------------------------------- $joinpoint_mock
	/**
	 * @var Method_Joinpoint|MockObject
	 */
	private Method_Joinpoint|MockObject $joinpoint_mock;

	//------------------------------------------------------------------------------------ $authorize
	/**
	 * @var Authorize_Activated_User
	 */
	private Authorize_Activated_User $authorize;

	//------------------------------------------------------------------------------------ $link_mock
	/**
	 * @var Link|MockObject|null
	 */
	private MockObject|Link|null $link_mock = null;

	//------------------------------------------------------------------------------------- $old_link
	/**
	 * @var Dao\Data_Link|null
	 */
	private ?Dao\Data_Link $old_link = null;

	//-------------------------------------------------------------------------------- $register_mock
	/**
	 * @var Register|MockObject
	 */
	private Register|MockObject $register_mock;

	//------------------------------------------------------------------------------------- $aop_mock
	/**
	 * @var IWeaver|MockObject
	 */
	private MockObject|IWeaver $aop_mock;

	//----------------------------------------------------------------------------------------- setUp
	public function setUp(): void
	{
		parent::setUp();
		$this->joinpoint_mock     = $this->createMock(Method_Joinpoint::class);
		$this->link_mock          = $this->createMock(Link::class);
		$this->register_mock      = $this->createMock(Register::class);
		$this->aop_mock           = $this->createMock(IWeaver::class);
		$this->old_link           = Dao::current();
		$this->authorize          = new Authorize_Activated_User();
		$this->register_mock->aop = $this->aop_mock;
		$this->authorize->register($this->register_mock);
		Dao::current($this->link_mock);
	}

	//-------------------------------------------------------------------------------------- tearDown
	public function tearDown(): void
	{
		Dao::current($this->old_link);
		$this->link_mock = null;
		$this->old_link  = null;
		parent::tearDown();
	}

	//------------------------------------------------------------------------------ dataFailProvider
	public function dataFailProvider() : Iterator
	{
		 yield['foo', ''];
		 yield['', 'foo'];
		 yield['', ''];
	}

	//--------------------------------------------------------------------------- dataSuccessProvider
	public function dataSuccessProvider() : Iterator
	{
		yield['foo', 'foo', 'onCheckAccessCurrentConnection', false];
	}

	//----------------------------------------------------------------------- dataUserEndDateProvider
	public function dataUserEndDateProvider() : Iterator
	{
		yield['foo', 'foo', new Date_Time('21-04-2021')];
		yield['foo', 'foo', new Date_Time()];
		yield['foo', 'foo', null];
	}

	//----------------------------------------------------------------------------------- testSuccess
	/**
	 * @dataProvider dataSuccessProvider
	 * @param $login    string
	 * @param $password string
	 * @param $method   string
	 * @param $expected boolean
	 */
	public function testSuccess(string $login, string $password, string $method, bool $expected)
	{
		$user = new User();
		$user->login    = $login;
		$user->password = (new Password($password, 'sha1'))->encrypted();

		$this->joinpoint_mock->parameters = ['parameters' => new Parameters(), 'form' => ['login' => $login, 'password' => $password]];

		$this->link_mock->expects($this->never())->method('search')->willReturn([$user]);
		$this->link_mock->expects($this->never())->method('searchOne')->willReturn($user);

		$loc_enabled = Loc::enable(false);
		$this->authorize->$method($this->joinpoint_mock);
		Loc::enable($loc_enabled);
		$this->assertEquals($expected, $this->joinpoint_mock->stop);
	}

}
