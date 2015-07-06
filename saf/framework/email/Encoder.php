<?php
namespace SAF\Framework\Email;

use Mail_mime;
use SAF\Framework\Builder;
use SAF\Framework\Dao\File;
use SAF\Framework\Email;

if (!@include_once(__DIR__ . '/../../../vendor/pear/Mail/mime.php')) {
	@include_once '/usr/share/php/Mail/mime.php';
}
include_once __DIR__ . '/../../../vendor/html2text/html2text.php';

/**
 * Encodes MIME emails
 *
 * This offers a SAF interface to the PHP PEAR Mail_Mime package
 * To install it on a Debian Linux server : apt-get install php-mail-mime
 * Or from pear : pear install Mail_Mime
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
	 * @param $mail       Mail_mime
	 */
	protected function addAttachments(Mail_mime $mail)
	{
		foreach ($this->email->attachments as $attachment) {
			$mail->addAttachment($attachment->temporary_file_name);
		}
	}

	//---------------------------------------------------------------------------------------- encode
	/**
	 * Encodes the email into MIME format
	 *
	 * Returns the MIME Multipart string
	 * If the mail is plain text without attachment, the plain text is returned without any change
	 *
	 * @return string
	 */
	public function encode()
	{
		if ($this->email->attachments || strpos($this->email->content, '<body')) {

			/** @var $mail Mail_mime */
			$mail = Builder::create(Mail_mime::class);

			$body = $this->parseImages($mail, $this->email->content);

			$error_reporting = error_reporting();
			error_reporting(E_ALL & ~E_WARNING);
			$mail->setTXTBody(convert_html_to_text($this->email->content));
			error_reporting($error_reporting);

			$mail->setHTMLBody($body);

			$this->addAttachments($mail);

			$mime_params = [
				'text_encoding' => '8bit',
				'text_charset'  => 'UTF-8',
				'html_charset'  => 'UTF-8',
				'head_charset'  => 'UTF-8'
			];
			$body = $mail->get($mime_params);
			$this->email->headers = $mail->headers($this->email->getHeadersAsStrings());
			return $body;

		}
		return $this->email->content;
	}

	//----------------------------------------------------------------------------------- parseImages
	/**
	 * @param $mail   Mail_mime
	 * @param $buffer string
	 * @return string
	 */
	protected function parseImages(Mail_mime $mail, $buffer)
	{
		$parent = '';
		$slash_count = substr_count(__DIR__, SL);
		while (!is_dir($parent . 'images')) {
			$parent .= '../';
			if (substr_count($parent, SL) > $slash_count) {
				$parent = '';
				break;
			}
		}
		$buffer = str_replace(
			['src=' . DQ . '/images/',   'src=' . Q . '/images/',   '(url=/images/'],
			['src=' . DQ . $parent . 'images/', 'src=' . Q . $parent . 'images/', '(url=' . $parent . 'images/)'],
			$buffer
		);
		foreach (['(' => ')', Q => Q, DQ => DQ] as $open => $close) {
			$pattern = '%\\' . $open . '([\\w\\.\\/\\-\\_]+\\.(?:gif|jpg|png))\\' . $close . '%';
			preg_match_all($pattern, $buffer, $matches);
			foreach ($matches[1] as $match) {
				$mail->addHTMLImage($match);
				foreach ($mail->_html_images as $key => $image) {
					if ($image['name'] == $match) {
						$mail->_html_images[$key]['c_type'] = 'image/' . rLastParse($match, '.');
						$buffer = str_replace($match, 'cid:' . $image['cid'], $buffer);
					}
				}
			}
		}
		return $buffer;
	}

}
