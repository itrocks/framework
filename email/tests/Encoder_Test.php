<?php
namespace ITRocks\Framework\Email\Tests;

use ITRocks\Framework\Email;
use ITRocks\Framework\Email\Encoder;
use ITRocks\Framework\Email\Recipient;
use ITRocks\Framework\Tests\Test;
use Swift_Message;

/**
 * Email\Encoder tests
 *
 * TODO test more headers: Reply-To, ...
 * TODO test attachments
 */
class Encoder_Test extends Test
{

	//---------------------------------------------------------------------------------------- $email
	private Email $email;

	//----------------------------------------------------------------------------------------- setUp
	protected function setUp() : void
	{
		parent::setUp();
		$this->email = new Email();
		$this->email->content
			= '<p>Image: <img alt="" src="itrocks/framework/skins/default/img/delete.png"></p>';
		$this->email->from = new Recipient('test@email.co', 'Test recipient');
	}

	//------------------------------------------------------------------------------- testConstructor
	public function testConstructor()
	{
		$encoder = new Encoder($this->email);
		$this->assertEquals($encoder->email, $this->email);
	}

	//----------------------------------------------------------------------------- testCreateMessage
	public function testCreateMessage()
	{
		$encoder = new Encoder($this->email);
		$message = $encoder->toSwiftMessage();
		$this->assertInstanceOf(Swift_Message::class, $message);
	}

	//------------------------------------------------------------------------------- testEmbedImages
	public function testEmbedImages()
	{
		$encoder = new Encoder($this->email);
		$message = $encoder->toSwiftMessage();
		// Image should be embedded as the first MIME child
		$this->assertNotEmpty($message->getChildren());
		$embedded_image = $message->getChildren()[0];
		$cid = $embedded_image->getId();
		$this->assertMatchesRegularExpression('/[[:xdigit:]]{32}@swift.generated/', $cid);
		// Embedded image should be referenced in html mail body
		$body = $message->getBody();
		$this->assertEquals("<p>Image: <img alt=\"\" src=\"cid:$cid\"></p>", $body);
	}

	//------------------------------------------------------------------------------- testEmptyHeader
	public function testEmptyHeader()
	{
		$encoder = new Encoder($this->email);
		$message = $encoder->toSwiftMessage();
		$from = $message->getFrom();
		$this->assertEmpty($from);
		$headers = $message->getHeaders()->toString();
		$this->assertMatchesRegularExpression('/^From: \R/m', $headers);
	}

	//------------------------------------------------------------------------------- testNoBccHeader
	public function testNoBccHeader()
	{
		$encoder = new Encoder($this->email);
		$message = $encoder->toSwiftMessage();
		$bcc = $message->getBcc();
		$this->assertEmpty($bcc);
		$headers = $message->getHeaders()->toString();
		$this->assertDoesNotMatchRegularExpression('/^Bcc: /m', $headers);
	}

	//-------------------------------------------------------------------------------- testNoCcHeader
	public function testNoCcHeader()
	{
		$encoder = new Encoder($this->email);
		$message = $encoder->toSwiftMessage();
		$cc = $message->getCc();
		$this->assertEmpty($cc);
		$headers = $message->getHeaders()->toString();
		$this->assertDoesNotMatchRegularExpression('/^Cc: /m', $headers);
	}

	//-------------------------------------------------------------------------------- testNoToHeader
	public function testNoToHeader()
	{
		$encoder = new Encoder($this->email);
		$message = $encoder->toSwiftMessage();
		$to = $message->getTo();
		$this->assertEmpty($to);
		$headers = $message->getHeaders()->toString();
		$this->assertDoesNotMatchRegularExpression('/^To: /m', $headers);
	}

	//--------------------------------------------------------------------------- testSingleBccHeader
	public function testSingleBccHeader()
	{
		$this->email->blind_copy_to = [new Recipient('foo@example.org', 'Foo Bar')];
		$encoder = new Encoder($this->email);
		$message = $encoder->toSwiftMessage();
		$bcc = $message->getBcc();
		$this->assertArrayHasKey('foo@example.org', $bcc);
		$this->assertCount(1, $bcc);
		$headers = $message->getHeaders()->toString();
		$this->assertMatchesRegularExpression('/^Bcc: Foo Bar <foo@example.org>\R/m', $headers);
	}

	//---------------------------------------------------------------------------- testSingleCcHeader
	public function testSingleCcHeader()
	{
		$this->email->copy_to = [new Recipient('foo@example.org', 'Foo Bar')];
		$encoder = new Encoder($this->email);
		$message = $encoder->toSwiftMessage();
		$cc = $message->getCc();
		$this->assertArrayHasKey('foo@example.org', $cc);
		$this->assertCount(1, $cc);
		$headers = $message->getHeaders()->toString();
		$this->assertMatchesRegularExpression('/^Cc: Foo Bar <foo@example.org>\R/m', $headers);
	}

	//-------------------------------------------------------------------------- testSingleFromHeader
	public function testSingleFromHeader()
	{
		$this->email->from = new Recipient('foo@example.org', 'Foo Bar');
		$encoder = new Encoder($this->email);
		$message = $encoder->toSwiftMessage();
		$from = $message->getFrom();
		$this->assertArrayHasKey('foo@example.org', $from);
		$this->assertCount(1, $from);
		$headers = $message->getHeaders()->toString();
		$this->assertMatchesRegularExpression('/^From: Foo Bar <foo@example.org>\R/m', $headers);
	}

	//---------------------------------------------------------------------------- testSingleToHeader
	public function testSingleToHeader()
	{
		$this->email->to = [new Recipient('foo@example.org', 'Foo Bar')];
		$encoder = new Encoder($this->email);
		$message = $encoder->toSwiftMessage();
		$to = $message->getTo();
		$this->assertArrayHasKey('foo@example.org', $to);
		$this->assertCount(1, $to);
		$headers = $message->getHeaders()->toString();
		$this->assertMatchesRegularExpression('/^To: Foo Bar <foo@example.org>\R/m', $headers);
	}

}
