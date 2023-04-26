<?php
namespace ITRocks\Framework\Email\Tests;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Email;
use ITRocks\Framework\Email\Encoder;
use ITRocks\Framework\Email\Recipient;
use ITRocks\Framework\Tests\Test;
use Symfony\Component\Mime;

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
		/** @noinspection PhpUnhandledExceptionInspection class */
		$this->email = Builder::create(Email::class);
		$this->email->content = str_replace(
			'app://',
			'',
			'<p>Image: <img alt="" src="app://itrocks/framework/skins/default/img/delete.png"></p>'
		);
		$this->email->from = new Recipient('test@email.co', 'Test recipient');
	}

	//------------------------------------------------------------------------------- testConstructor
	public function testConstructor() : void
	{
		$encoder = new Encoder($this->email);
		self::assertEquals($encoder->email, $this->email);
	}

	//----------------------------------------------------------------------------- testCreateMessage
	public function testCreateMessage() : void
	{
		$encoder = new Encoder($this->email);
		$message = $encoder->toMessage();
		self::assertInstanceOf(Mime\Email::class, $message);
	}

	//------------------------------------------------------------------------------- testEmbedImages
	public function testEmbedImages() : void
	{
		$encoder = new Encoder($this->email);
		$message = $encoder->toMessage();
		// Image should be embedded as the first MIME child
		self::assertNotEmpty($message->getAttachments());
		$embedded_image = $message->getAttachments()[0];
		$cid            = $embedded_image->getContentId();
		self::assertMatchesRegularExpression('#[[:xdigit:]]{32}@symfony#', $cid);
		// Embedded image should be referenced in html mail body
		$body = $message->getBody()->bodyToString();
		/** @noinspection HtmlUnknownTarget Inspector bug : it is a cid, not a file, so it's ok */
		self::assertMatchesRegularExpression('#src=3D"cid:[[:xdigit:]]{32}@symfony#', $body);
		self::assertMatchesRegularExpression(
			'#Content-Type: image/png; name="[[:xdigit:]]{32}@symfony"#', $body
		);
	}

	//------------------------------------------------------------------------------- testEmptyHeader
	public function testEmptyHeader() : void
	{
		$encoder = new Encoder($this->email);
		$message = $encoder->toMessage();
		$from    = $message->getFrom();
		self::assertNotEquals([], $from);
		$headers = $message->getHeaders()->toString();
		self::assertMatchesRegularExpression('/^From: Test recipient <test@email.co>\R/m', $headers);
	}

	//------------------------------------------------------------------------------- testNoBccHeader
	public function testNoBccHeader() : void
	{
		$encoder = new Encoder($this->email);
		$message = $encoder->toMessage();
		$bcc     = $message->getBcc();
		self::assertEmpty($bcc);
		$headers = $message->getHeaders()->toString();
		self::assertDoesNotMatchRegularExpression('/^Bcc: /m', $headers);
	}

	//-------------------------------------------------------------------------------- testNoCcHeader
	public function testNoCcHeader() : void
	{
		$encoder = new Encoder($this->email);
		$message = $encoder->toMessage();
		$cc      = $message->getCc();
		self::assertEmpty($cc);
		$headers = $message->getHeaders()->toString();
		self::assertDoesNotMatchRegularExpression('/^Cc: /m', $headers);
	}

	//-------------------------------------------------------------------------------- testNoToHeader
	public function testNoToHeader() : void
	{
		$encoder = new Encoder($this->email);
		$message = $encoder->toMessage();
		$to      = $message->getTo();
		self::assertEmpty($to);
		$headers = $message->getHeaders()->toString();
		self::assertDoesNotMatchRegularExpression('/^To: /m', $headers);
	}

	//--------------------------------------------------------------------------- testSingleBccHeader
	public function testSingleBccHeader() : void
	{
		$this->email->blind_copy_to = [new Recipient('foo@example.org', 'Foo Bar')];
		$encoder = new Encoder($this->email);
		$message = $encoder->toMessage();
		$bcc     = $message->getBcc();
		self::assertCount(1, $bcc);
		self::assertEquals('foo@example.org', $bcc[0]->getAddress());
		self::assertEquals('Foo Bar',         $bcc[0]->getName());
		$headers = $message->getHeaders()->toString();
		self::assertMatchesRegularExpression('/^Bcc: Foo Bar <foo@example.org>\R/m', $headers);
	}

	//---------------------------------------------------------------------------- testSingleCcHeader
	public function testSingleCcHeader() : void
	{
		$this->email->copy_to = [new Recipient('foo@example.org', 'Foo Bar')];
		$encoder = new Encoder($this->email);
		$message = $encoder->toMessage();
		$cc      = $message->getCc();
		self::assertCount(1, $cc);
		self::assertEquals('foo@example.org', $cc[0]->getAddress());
		self::assertEquals('Foo Bar',         $cc[0]->getName());
		$headers = $message->getHeaders()->toString();
		self::assertMatchesRegularExpression('/^Cc: Foo Bar <foo@example.org>\R/m', $headers);
	}

	//-------------------------------------------------------------------------- testSingleFromHeader
	public function testSingleFromHeader() : void
	{
		$this->email->from = new Recipient('foo@example.org', 'Foo Bar');
		$encoder = new Encoder($this->email);
		$message = $encoder->toMessage();
		$from    = $message->getFrom();
		self::assertCount(1, $from);
		self::assertEquals('foo@example.org', $from[0]->getAddress());
		self::assertEquals('Foo Bar',         $from[0]->getName());
		$headers = $message->getHeaders()->toString();
		self::assertMatchesRegularExpression('/^From: Foo Bar <foo@example.org>\R/m', $headers);
	}

	//---------------------------------------------------------------------------- testSingleToHeader
	public function testSingleToHeader() : void
	{
		$this->email->to = [new Recipient('foo@example.org', 'Foo Bar')];
		$encoder = new Encoder($this->email);
		$message = $encoder->toMessage();
		$to     = $message->getTo();
		self::assertCount(1, $to);
		self::assertEquals('foo@example.org', $to[0]->getAddress());
		self::assertEquals('Foo Bar',         $to[0]->getName());
		$headers = $message->getHeaders()->toString();
		self::assertMatchesRegularExpression('/^To: Foo Bar <foo@example.org>\R/m', $headers);
	}

}
