<?php
namespace ITRocks\Framework\Email;

use Html2Text\Html2Text;
use ITRocks\Framework\Email;
use ITRocks\Framework\Tools\Date_Time;
use Symfony\Component\Mime;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\DateHeader;
use Symfony\Component\Mime\Header\IdentificationHeader;

/**
 * Encodes emails using the Symfony Mime component
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

	//------------------------------------------------------------------------------------ __toString
	/**
	 * Render an email as a string
	 *
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->toMessage()->toString();
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
			/** @noinspection RegExpRedundantEscape not that redundant */
			$pattern = SL . BS . $open . '([\\w\\.\\/\\-\\_]+\\.(?:gif|jpg|png|svg))' . BS . $close . SL;
			preg_match_all($pattern, $content, $matches);
			foreach ($matches[1] as $file_name) {
				$file_path = $this->working_directory
					? ($this->working_directory . SL . $file_name)
					: $file_name;
				if (!str_contains($file_name, 'cid:') && $this->fileExists($file_path)) {
					$cid     = call_user_func($callback, $file_path);
					$content = str_replace(
						$open . $file_name . $close, $open . 'cid:' . $cid . $close, $content
					);
				}
			}
		}
		return $content;
	}

	//------------------------------------------------------------------------------------- toHeaders
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $message Mime\Email
	 * @param $headers string[]
	 */
	protected function toHeaders(Mime\Email $message, array $headers) : void
	{
		$message_headers = $message->getHeaders();
		if (!isset($headers['Message-ID'])) {
			$headers['Message-ID'] = date('YmdHis') . DOT . uniqid()
				. AT . ($_SERVER['SERVER_NAME'] ?? 'console');
		}
		foreach ($headers as $header_name => $header_value) {
			$header = $message_headers->get($header_name);
			// update header
			if ($header) {
				if ($header instanceof DateHeader) {
					/** @noinspection PhpUnhandledExceptionInspection would be a programming error */
					$header->setDateTime(new Date_Time($header_value));
					continue;
				}
				if ($header instanceof IdentificationHeader) {
					$header->setId($header_value);
					continue;
				}
				$message_headers->remove($header_name);
				$header = null;
			}
			// add header
			if (!strcasecmp($header_name, 'Message-ID')) {
				$message_headers->addIdHeader($header_name, $header_value);
			}
			elseif (!strcasecmp($header_name, 'Date')) {
				/** @noinspection PhpUnhandledExceptionInspection would be a programming error */
				$message_headers->addDateHeader($header_name, new Date_Time($header_value));
			}
			else {
				$message_headers->addTextHeader($header_name, $header_value);
			}
		}
	}

	//------------------------------------------------------------------------------------- toMessage
	/**
	 * Create a message that can be sent from an Email object
	 *
	 * @return Mime\Email
	 */
	public function toMessage() : Mime\Email
	{
		$message = new Mime\Email();

		// Headers
		$message->subject($this->email->subject);
		$message->from(new Address($this->email->from->email, $this->email->from->name));
		foreach ($this->email->to as $recipient) {
			$message->addTo(new Address($recipient->email, $recipient->name));
		}
		foreach ($this->email->copy_to as $recipient) {
			$message->addCc(new Address($recipient->email, $recipient->name));
		}
		foreach ($this->email->blind_copy_to as $recipient) {
			$message->addBcc(new Address($recipient->email, $recipient->name));
		}
		if ($this->email->reply_to) {
			$message->replyTo(new Address($this->email->reply_to->email, $this->email->reply_to->name));
		}
		if ($this->email->return_path) {
			$message->returnPath($this->email->return_path->email);
		}
		$this->toHeaders($message, $this->email->headers);

		// Body
		$html_part = $this->parseImages(
			$this->email->content,
			function(string $image_path) use($message) : string {
				$cid = rLastParse($image_path, SL);
				$message->embedFromPath($image_path, $cid);
				return $cid;
			}
		);
		$message->html($html_part);
		$message->text((new Html2Text($this->email->content))->getText());

		// Attachments
		foreach ($this->email->attachments as $a) {
			if ($a->embedded) {
				$message->embedFromPath($a->temporary_file_name, $a->name);
			}
			else {
				$message->attachFromPath($a->temporary_file_name, $a->name);
			}
		}

		return $message;
	}

}
