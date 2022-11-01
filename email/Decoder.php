<?php
namespace ITRocks\Framework\Email;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Email;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Decodes MIME emails
 *
 * Needs php-mailparse to be installed
 */
class Decoder
{

	//--------------------------------------------------------------------------------------- content
	/**
	 * @param $email   Email
	 * @param $content string
	 */
	protected function content(Email $email, string $content)
	{
		$email->content = $content;
	}

	//------------------------------------------------------------------------------------ decodeFile
	/**
	 * This decodes an email file
	 *
	 * In its conception, this can only decode files saved using ITRocks. Other uses may crash.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $filename string The path of an .eml file containing full MIME headers and parts
	 * @return Email
	 */
	public function decodeFile(string $filename) : Email
	{
		$content = '';
		/** @noinspection PhpUnhandledExceptionInspection class */
		$email = Builder::create(Email::class);

		$html_headers  = $html_content  = null;
		$main_headers  = $main_content  = null;
		$plain_headers = $plain_content = null;
		$top_headers                    = null;

		$mime  = mailparse_msg_parse_file($filename);
		$parts = mailparse_msg_get_structure($mime);
		foreach ($parts as $part_id) {

			$part = mailparse_msg_get_part($mime, $part_id);
			$data = mailparse_msg_get_part_data($part);
			ob_start();
			mailparse_msg_extract_part_file($part, $filename);
			$content = ob_get_contents();
			ob_end_clean();

			if (!$top_headers) {
				$top_headers = $data['headers'];
			}

			switch ($data['content-type']) {
				case 'multipart/alternative':
				case 'multipart/mixed':
				case 'multipart/related':
					if (!$main_headers && !$main_content) {
						$main_headers = $data['headers'];
						$main_content = $content;
					}
					break;
				case 'text/plain':
					if (!$plain_headers && !$plain_content) {
						$plain_headers = $data['headers'];
						$plain_content = $content;
					}
					break;
				case 'text/html':
					if (!$html_headers && !$html_content) {
						$html_headers = $data['headers'];
						$html_content = $content;
					}
					break;
			}

		}
		mailparse_msg_free($mime);

		Dao::begin();
		$this->content($email, $html_content ?: $plain_content ?: $main_content ?: $content);
		$this->headers($email, $top_headers);
		Dao::commit();

		return $email;
	}

	//----------------------------------------------------------------------------- headerToRecipient
	/**
	 * @param $string string
	 * @return Recipient
	 */
	protected function headerToRecipient(string $string) : Recipient
	{
		if (!str_contains($string, '<')) {
			$address[0] = '';
			$address[1] = noQuotes($string);
		}
		else {
			$address    = explode('<', $string);
			$address[0] = noQuotes(trim($address[0]));
			$address[1] = lParse($address[1], '>');
		}
		return Dao::searchOne(['email' => $address[1], 'name' => $address[0]], Recipient::class)
			?: Dao::write(new Recipient($address[1], $address[0]));
	}

	//--------------------------------------------------------------------------------------- headers
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $email   Email
	 * @param $headers string[]
	 */
	protected function headers(Email $email, array $headers)
	{
		/** @noinspection PhpUnhandledExceptionInspection must be valid */
		$email->date    = new Date_Time(lParse($headers['date'], ' ('));
		$email->from    = $this->headerToRecipient($headers['from']);
		$email->subject = iconv_mime_decode($headers['subject'], 0, 'UTF-8');
		$email->to      = [$this->headerToRecipient($headers['to'])];
		if (isset($headers['message-id'])) {
			$email->headers['Message-ID'] = trim($headers['message-id'], '<>');
		}
	}

}
