<?php
namespace ITRocks\Framework\Email;

use Html2Text\Html2Text;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Email;

/**
 * Encodes MIME emails
 *
 * This offers a ITRocks interface to the PHP PEAR Mail_Mime package
 */
class Encoder
{

	//---------------------------------------------------------------------------------------- $email
	/**
	 * @var Email
	 */
	public $email;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $email Email
	 */
	public function __construct(Email $email)
	{
		$this->email = $email;
	}

	//-------------------------------------------------------------------------------- addAttachments
	/**
	 * @param $mail Mime
	 */
	protected function addAttachments(Mime $mail)
	{
		foreach ($this->email->attachments as $attachment) {
			$mail->addAttachment(
				$attachment->temporary_file_name, 'application/octet-stream', $attachment->name
			);
		}
	}

	//---------------------------------------------------------------------------------------- encode
	/**
	 * Encodes the email into MIME format
	 *
	 * Returns the MIME Multipart string
	 * If the mail is plain text without attachment, the plain text is returned without any change
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string
	 */
	public function encode()
	{
		if ($this->email->attachments || (strpos($this->email->content, '<body') !== false)) {

			/** @noinspection PhpUnhandledExceptionInspection constant */
			$mail = Builder::create(Mime::class);

			$body = $this->parseImages($mail, $this->email->content);

			$error_reporting = error_reporting(E_ALL & ~E_WARNING);
			$mail->setTXTBody((new Html2Text($this->email->content))->getText());
			error_reporting($error_reporting);

			$mail->setHTMLBody($body);

			$this->addAttachments($mail);

			$mime_params = [
				'text_encoding' => '8bit',
				'text_charset'  => 'UTF-8',
				'html_charset'  => 'UTF-8',
				'head_charset'  => 'UTF-8'
			];
			$body                 = $mail->get($mime_params);
			$this->email->headers = $mail->headers($this->email->getHeadersAsStrings());
			return $body;

		}
		return $this->email->content;
	}

	//--------------------------------------------------------------------------------- encodeHeaders
	/**
	 * @return string
	 */
	public function encodeHeaders()
	{
		return $this->email->getHeadersString();
	}

	//----------------------------------------------------------------------------------- parseImages
	/**
	 * @param $mail   Mime
	 * @param $buffer string
	 * @return string
	 */
	protected function parseImages(Mime $mail, $buffer)
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
		$buffer = strReplace(
			[
				'src=' . DQ . '/images/' => 'src=' . DQ . $parent . 'images/',
				'src=' . Q . '/images/'  => 'src=' . Q . $parent . 'images/',
				'(url=/images/'          => '(url=' . $parent . 'images/)'
			],
			$buffer
		);
		foreach (['(' => ')', Q => Q, DQ => DQ] as $open => $close) {
			$pattern = '%\\' . $open . '([\\w\\.\\/\\-\\_]+\\.(?:gif|jpg|png))\\' . $close . '%';
			preg_match_all($pattern, $buffer, $matches);
			foreach ($matches[1] as $match) {
				$mail->addHTMLImage($match);
				$html_images = $mail->getHtmlImages();
				foreach ($html_images as $key => $image) {
					if ($image['name'] == $match) {
						$html_images[$key]['c_type'] = 'image/' . rLastParse($match, DOT);
						$buffer                      = str_replace($match, 'cid:' . $image['cid'], $buffer);
					}
				}
				$mail->setHtmlImages($html_images);
			}
		}
		return $buffer;
	}

}
