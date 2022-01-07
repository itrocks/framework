<?php
namespace ITRocks\Framework\Email;

use Html2Text\Html2Text;
use ITRocks\Framework\Email;
use ITRocks\Framework\Tools\Date_Time;
use Swift_Attachment;
use Swift_Image;
use Swift_Message;
use Swift_Mime_Headers_DateHeader;
use Swift_Mime_Headers_IdentificationHeader;

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

	//---------------------------------------------------------------------------- $working_directory
	/**
	 * You need this to embed images stored as files
	 *
	 * @var string
	 */
	public string $working_directory;
	
	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $email             Email
	 * @param $working_directory string
	 */
	public function __construct(Email $email, string $working_directory = '')
	{
		$this->email             = $email;
		$this->working_directory = $working_directory;
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

	//----------------------------------------------------------------------------------- parseImages
	/**
	 * Parse an HTML message : replace images references by MIME cid.
	 *
	 * @param $content  string   The message HTML content
	 * @param $callback callable Called for each image path (as parameter) found into content
	 *                           This must return the replacement cid, eg 'cid:205b068bb92'
	 * @return string
	 */
	protected function parseImages(string $content, callable $callback) : string
	{
		$parent      = '';
		$slash_count = substr_count(__DIR__, SL);
		while (!is_dir($parent . 'images')) {
			$parent .= '../';
			if (substr_count($parent, SL) > $slash_count) {
				$parent = '';
				break;
			}
		}
		$content = strReplace(
			[
				'src=' . DQ . '/images/' => 'src=' . DQ . $parent . 'images/',
				'src=' . Q  . '/images/' => 'src=' . Q  . $parent . 'images/',
				'(url=/images/'          => '(url=' . $parent . 'images/)'
			],
			$content
		);
		foreach (['(' => ')', Q => Q, DQ => DQ] as $open => $close) {
			$pattern = SL . BS . $open . '([\\w\\.\\/\\-\\_]+\\.(?:gif|jpg|png|svg))' . BS . $close . SL;
			preg_match_all($pattern, $content, $matches);
			foreach ($matches[1] as $file_name) {
				$file_path = $this->working_directory
					? ($this->working_directory . SL . $file_name)
					: $file_name;
				if (!str_contains($file_name, 'cid:') && $this->fileExists($file_path)) {
					$cid     = call_user_func($callback, $file_path);
					$content = str_replace($open . $file_name . $close, $open . $cid . $close, $content);
				}
			}
		}
		return $content;
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
				date('YmdHis') . DOT . uniqid() . AT . ($_SERVER['SERVER_NAME'] ?? 'console')
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
		$html_part = $this->parseImages(
			$this->email->content,
			function($image_path) use($message) : string {
				return $message->embed(Swift_Image::fromPath($image_path));
			}
		);
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
