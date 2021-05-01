<?php
namespace ITRocks\Framework\Email\Tests;

use Exception;
use Generator;
use ITRocks\Framework\Email\Sender;
use ITRocks\Framework\Email\Sender\File;
use ITRocks\Framework\Email\Sender\Smtp;
use ITRocks\Framework\Tests\Test;

final class Sender_Test extends Test
{

	//-------------------------------------------------------------------------------- callOkProvider
	public function callOkProvider() : Generator
	{
		yield ['smtp', Smtp::class];
		yield ['Smtp', Smtp::class];
		yield ['SMTP', Smtp::class];
		yield ['sMtP', Smtp::class];
		yield ['File', File::class];
	}

	//------------------------------------------------------------------------------------ testCallOk
	/**
	 * @dataProvider callOkProvider
	 * @param $transport_name string
	 * @param $expected_class string
	 * @throws Exception
	 */
	public function testCallOk(string $transport_name, string $expected_class)
	{
		$sender = Sender::call($transport_name);
		$this->assertInstanceOf($expected_class, $sender);
	}

	//------------------------------------------------------- testCallWithUnknownClassRaisesException
	public function testCallWithUnknownClassRaisesException()
	{
		$this->expectException('Exception');
		$this->expectErrorMessage('Class ITRocks\Framework\Email\Sender\Foo not found');
		Sender::call('Foo');
	}

}
