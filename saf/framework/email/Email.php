<?php
namespace SAF\Framework;

use SAF\Framework\Email\Account;
use SAF\Framework\Email\Attachment;
use SAF\Framework\Email\Recipient;
use SAF\Framework\Tools\Date_Time;

/**
 * A SAF electronic mail object to get full access to mails without depending on MIME or the else
 *
 * @representative date, from, to, subject
 */
class Email
{

	//-------------------------------------------------------------------------------------- $account
	/**
	 * @link Object
	 * @var Account
	 */
	public $account;

	//----------------------------------------------------------------------------------------- $from
	/**
	 * @link Object
	 * @var Recipient
	 */
	public $from;

	//------------------------------------------------------------------------------------- $reply_to
	/**
	 * @link Object
	 * @var Recipient
	 */
	public $reply_to;

	//---------------------------------------------------------------------------------- $return_path
	/**
	 * @link Object
	 * @var Recipient
	 */
	public $return_path;

	//------------------------------------------------------------------------------------------- $to
	/**
	 * @link Map
	 * @var Recipient[]
	 */
	public $to;

	//-------------------------------------------------------------------------------------- $copy_to
	/**
	 * @link Map
	 * @var Recipient[]
	 */
	public $copy_to;

	//-------------------------------------------------------------------------------- $blind_copy_to
	/**
	 * @link Map
	 * @var Recipient[]
	 */
	public $blind_copy_to;

	//-------------------------------------------------------------------------------------- $subject
	/**
	 * @var string
	 */
	public $subject;

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @var Date_Time
	 */
	public $date;

	//-------------------------------------------------------------------------------------- $headers
	/**
	 * @var string[]
	 */
	public $headers = [];

	//------------------------------------------------------------------------------------ $send_date
	/**
	 * @var Date_Time
	 */
	public $send_date;

	//--------------------------------------------------------------------------------- $receive_date
	/**
	 * @var Date_Time
	 */
	public $receive_date;

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @var string
	 * @multiline
	 * @max_length 10000000
	 */
	public $content;

	//---------------------------------------------------------------------------------- $attachments
	/**
	 * @link Map
	 * @var Attachment[]
	 */
	public $attachments;

	//--------------------------------------------------------------------------- getHeadersAsStrings
	/**
	 * @return string[]
	 */
	public function getHeadersAsStrings()
	{
		if ($this->blind_copy_to) {
			$this->headers['Bcc'] = join(',', $this->blind_copy_to);
		}
		if ($this->copy_to) {
			$this->headers['Cc'] = join(',', $this->copy_to);
		}
		if ($this->from) {
			$this->headers['From'] = strval($this->from);
		}
		if ($this->reply_to) {
			$this->headers['Reply-To'] = strval($this->reply_to);
		}
		if ($this->return_path) {
			$this->headers['Return-Path'] = strval($this->return_path);
		}
		if ($this->subject) {
			$this->headers['Subject'] = $this->subject;
		}
		if ($this->to) {
			$this->headers['To'] = join(',', $this->to);
		}
		if (!isset($this->headers['Content-Type'])) {
			$this->headers['Content-Type'] = 'text/html; charset=UTF-8';
		}
		return $this->headers;
	}

	//------------------------------------------------------------------------ getRecipientsAsStrings
	/**
	 * @return string[]
	 */
	public function getRecipientsAsStrings()
	{
		$recipients = [];
		foreach ([$this->to, $this->copy_to, $this->blind_copy_to] as $recipients_objects) {
			/** @var $recipients_objects Recipient[] */
			foreach ($recipients_objects as $recipient) {
				$recipients[$recipient->email] = $recipient->email;
			}
		}
		return $recipients;
	}

}
