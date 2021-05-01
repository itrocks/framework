<?php
namespace ITRocks\Framework\Email;

use DOMDocument;
use Html2Text\Html2Text;
use ITRocks\Framework\Email;
use ITRocks\Framework\Tools\Date_Time;
use Swift_Attachment;
use Swift_Image;
use Swift_Message;
use Swift_Mime_Headers_DateHeader;
use Swift_Mime_Headers_IdentificationHeader;
use Swift_Mime_SimpleMessage;

/**
 * Encodes emails
 *
 * This offers a ITRocks interface to the SwiftMailer package
 */
class Encoder
{

	//---------------------------------------------------------------------------------------- $email
	/**
	 * @var Email
	 */
	public Email $email;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $email Email
	 */
	public function __construct(Email $email)
	{
		$this->email = $email;
	}

	//------------------------------------------------------------------------------------ fileExists
	/**
	 * @param $path string local file path or web file URL
	 * @return boolean
	 */
	protected function fileExists(string $path) : bool
	{
		// this is an url, it exists if it has a size
		return filter_var($path, FILTER_VALIDATE_URL)
			? (bool)@getimagesize($path)
			: file_exists($path);
	}

	//------------------------------------------------------------------------------ swiftEmbedImages
	/**
	 * Parse an html string  using DOM traversal, and modify img tags so that they embed the image
	 * they refer to as an inline mime part
	 *
	 * @param $message Swift_Mime_SimpleMessage
	 * @param $content string
	 * @return string
	 */
	protected function swiftEmbedImages(Swift_Mime_SimpleMessage $message, string $content) : string
	{
		$dom = new DOMDocument('1.0');
		$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED);

		$images = $dom->getElementsByTagName('img');
		foreach ($images as $image) {
			$src = $image->getAttribute('src');
			if (!str_contains($src, 'cid:') && $this->fileExists($src)) {
				$cid = $message->embed(Swift_Image::fromPath($src));
				$image->setAttribute('src', $cid);
			}
		}

		return utf8_decode($dom->saveHTML($dom->documentElement));
	}

	//-------------------------------------------------------------------------------------- toString
	/**
	 * Render an email as a string
	 *
	 * @return string
	 */
	public function toString() : string
	{
		return $this->toSwiftMessage()->toString();
	}

	//-------------------------------------------------------------------------------- toSwiftHeaders
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $message Swift_Message
	 * @param $headers string[]
	 */
	protected function toSwiftHeaders(Swift_Message $message, array $headers)
	{
		$swift_headers = $message->getHeaders();
		foreach ($headers as $header_name => $header_value) {
			$header = $swift_headers->get($header_name);
			if ($header instanceof Swift_Mime_Headers_IdentificationHeader) {
				/** @noinspection PhpUnhandledExceptionInspection Swift_Mime_Headers_IdentificationHeader::setId */
				$header->setId($header_value);
			}
			if ($header instanceof Swift_Mime_Headers_DateHeader) {
				/** @noinspection PhpUnhandledExceptionInspection would be a programming error */
				$header->setDateTime(new Date_Time($header_value));
			}
			// TODO handle other header classes (btw a Swift::fromString() would be the simplest)
		}
		if (!isset($this->email->headers['Message-ID'])) {
			/** @noinspection PhpPossiblePolymorphicInvocationInspection I'm sure */
			$message->getHeaders()->get('Message-ID')->setId(
				date('YmdHis') . DOT . uniqid() . AT . $_SERVER['SERVER_NAME']
			);
		}
	}

	//-------------------------------------------------------------------------------- toSwiftMessage
	/**
	 * Create a message that can be sent from an Email object
	 *
	 * @return Swift_Message
	 */
	public function toSwiftMessage() : Swift_Message
	{
		$message = new Swift_Message();

		// Headers
		$message->setSubject($this->email->subject);
		$message->setFrom($this->email->from->email, $this->email->from->name);
		foreach ($this->email->to as $recipient) {
			$message->addTo($recipient->email, $recipient->name);
		}
		foreach ($this->email->copy_to as $recipient) {
			$message->addCc($recipient->email, $recipient->name);
		}
		foreach ($this->email->blind_copy_to as $recipient) {
			$message->addBcc($recipient->email, $recipient->name);
		}
		if ($this->email->reply_to) {
			$message->setReplyTo($this->email->reply_to->email, $this->email->reply_to->name);
		}
		if ($this->email->return_path) {
			$message->setReturnPath($this->email->return_path->email);
		}
		$this->toSwiftHeaders($message, $this->email->headers);

		// Body
		$html_part = $this->swiftEmbedImages($message, $this->email->content);
		$message->setBody($html_part, 'text/html', 'utf-8');
		$message->addPart((new Html2Text($this->email->content))->getText(), 'text/plain', 'utf-8');

		// Attachments
		foreach ($this->email->attachments as $a) {
			$attachment = Swift_Attachment::fromPath($a->temporary_file_name);
			$attachment->setFilename($a->name);
			if ($a->embedded) {
				$attachment->setDisposition('inline');
			}
			$message->attach($attachment);
		}

		return $message;
	}

}
