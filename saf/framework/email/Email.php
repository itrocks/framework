<?php
namespace SAF\Framework;

use SAF\Framework\Email\Account;
use SAF\Framework\Email\Attachment;
use SAF\Framework\Email\Recipient;
use SAF\Framework\Mapper\Search_Object;
use SAF\Framework\Tools\Date_Time;

/**
 * A SAF electronic mail object to get full access to mails without depending on MIME or the else
 *
 * @before_write
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

	//---------------------------------------------------------------------------------- $attachments
	/**
	 * @link Map
	 * @var Attachment[]
	 */
	public $attachments;

	//-------------------------------------------------------------------------------- $blind_copy_to
	/**
	 * @link Map
	 * @var Recipient[]
	 */
	public $blind_copy_to = [];

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @max_length 10000000
	 * @multiline
	 * @store hex
	 * @var string
	 */
	public $content;

	//-------------------------------------------------------------------------------------- $copy_to
	/**
	 * @link Map
	 * @var Recipient[]
	 */
	public $copy_to = [];

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $date;

	//----------------------------------------------------------------------------------------- $from
	/**
	 * @link Object
	 * @var Recipient
	 */
	public $from;

	//-------------------------------------------------------------------------------------- $headers
	/**
	 * @var string[]
	 */
	public $headers = [];

	//--------------------------------------------------------------------------------- $receive_date
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $receive_date;

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

	//------------------------------------------------------------------------------------ $send_date
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $send_date;

	//--------------------------------------------------------------------------------- $send_message
	/**
	 * @var string
	 */
	public $send_message;

	//-------------------------------------------------------------------------------------- $subject
	/**
	 * @var string
	 */
	public $subject;

	//------------------------------------------------------------------------------------------- $to
	/**
	 * @link Map
	 * @var Recipient[]
	 */
	public $to = [];

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->subject;
	}

	//----------------------------------------------------------------------------------------- $uidl
	/**
	 * The unique identification number of the mail into the distant server once it has been
	 * received / sent
	 *
	 * @var string
	 */
	public $uidl;

	//----------------------------------------------------------------------------------- beforeWrite
	/**
	 * Called before write : optimize attachments and recipients.
	 * - Reuse those which are already stored into the data storage.
	 * - Do not enable to alter an already stored attachment or recipient.
	 */
	public function beforeWrite()
	{
		if (!isset($this->date)) {
			$this->date = new Date_Time();
		}
		$this->uniqueAttachments();
		$this->uniqueRecipents();
	}

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

	//----------------------------------------------------------------------------- uniqueAttachments
	/**
	 * Be sure that attachments are unique into data storage
	 * - they can be common for several emails
	 * - modification of attachments is not allowed
	 */
	private function uniqueAttachments()
	{
		/** @var $search Attachment */
		$search = Search_Object::create(Attachment::class);
		foreach ($this->attachments as $attachment) {
			$search->hash = $attachment->hash;
			if (
				$search->hash
				&& ($find = Dao::searchOne($search))
				&& ($find->name == $attachment->name)
				&& ($find->content === $attachment->content)
			) {
				Dao::replace($attachment, $find, false);
			}
			else {
				Dao::disconnect($attachment);
			}
		}
	}

	//------------------------------------------------------------------------------- uniqueRecipents
	/**
	 * Be sure that recipients are unique into data storage
	 * - they can be common to several emails
	 * - modification of recipients is not allowed
	 */
	private function uniqueRecipents()
	{
		/** @var $search Recipient */
		$search = Search_Object::create(Recipient::class);
		$recipients = array_merge(
			[$this->from, $this->reply_to, $this->return_path],
			$this->to, $this->copy_to, $this->blind_copy_to
		);
		$already = [];
		foreach ($recipients as $recipient) {
			if (isset($recipient)) {
				$search->email = $recipient->email;
				$search->name  = $recipient->name;
				if (isset($already[strval($search)])) {
					Dao::replace($recipient, $already[strval($search)], false);
				}
				else {
					$already[strval($search)] = $recipient;
					if ($find = Dao::searchOne($recipient)) {
						Dao::replace($recipient, $find, false);
					}
					else {
						Dao::disconnect($recipient);
					}
				}
			}
		}
	}

}
