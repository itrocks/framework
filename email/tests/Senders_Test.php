<?php
namespace ITRocks\Framework\Email\Tests;

use ITRocks\Framework\Email\Senders;
use ITRocks\Framework\Tests\Test;

final class Senders_Test extends Test
{
	//---------------------------------------------------------------------------- $foo_configuration
	private array $foo_configuration = [];

	//----------------------------------------------------------------------------------------- setUp
	protected function setUp() : void
	{
		parent::setUp();
		$this->foo_configuration = ['foo' => [], 'bar' => []];
	}

	//-------------------------------------------------------------- testConstructorWithConfiguration
	public function testConstructorWithConfiguration() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection valid */
		$senders = new Senders($this->foo_configuration);
		$this->assertNotEmpty($senders->senders);
		$this->assertCount(2, $senders->senders);
	}

	//--------------------------------------------------------- testConstructorWithEmptyConfiguration
	public function testConstructorWithEmptyConfiguration() : void
	{
		$senders = new Senders();
		$this->assertEmpty($senders->senders);
	}

	//-------------------------------------------------------------------- testRetrieveExistingSender
	public function testRetrieveExistingSender() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection valid */
		$senders = new Senders($this->foo_configuration);
		$res = $senders->sender('foo');
		$this->assertNotNull($res);
	}

	//----------------------------------------------------------------- testRetrieveNonExistingSender
	public function testRetrieveNonExistingSender() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection valid */
		$senders = new Senders($this->foo_configuration);
		$res = $senders->sender('unknown');
		$this->assertNull($res);
	}

}
