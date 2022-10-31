<?php
namespace ITRocks\Framework\Dao;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Tests\Test;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Trait Has_Dao_Mock. Mock the DAO.
 *
 * How to use :
 *   Add the trait into the mock : use Has_Dao_Mock
 *   Specify method to mock      : $link_mock->expects([InvocationOrder])->method([methods names])->willReturn([return value])
 *
 * Examples :
 *   $link_mock->expects($this->once())->method('search')->willReturn([new Object()])
 *   $link_mock->expects($this->any())->method('write')->willReturn(null);
 *
 * @extends Test
 */
trait Has_Dao_Mock
{

	//----------------------------------------------------------------------------------------- $link
	/**
	 * @var Dao\Mysql\Link|null
	 */
	private Dao\Mysql\Link|null $link = null;

	//------------------------------------------------------------------------------------ $link_mock
	/**
	 * @var Dao\Mysql\Link|MockObject|null
	 */
	public Dao\Mysql\Link|MockObject|null $link_mock = null;

	//----------------------------------------------------------------------------------------- setUp
	protected function setUp() : void
	{
		$this->link_mock = $this->createMock(Dao\Mysql\Link::class);
		$this->link      = Dao::current();
		Dao::current($this->link_mock);
	}

	//-------------------------------------------------------------------------------------- tearDown
	protected function tearDown() : void
	{
		Dao::current($this->link);
	}

}
