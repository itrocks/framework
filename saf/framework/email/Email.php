<?php
namespace SAF\Framework;

use SAF\Framework\Email\Account;
use SAF\Framework\Email\Attachment;
use SAF\Framework\Email\Recipient;
use SAF\Framework\Tools\Date_Time;

/**
 * A SAF electronic mail object to get full access to mails without depending on MIME or the else
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
		return [];
	}

	//------------------------------------------------------------------------ getRecipientsAsStrings
	/**
	 * @return string[]
	 */
	public function getRecipientsAsStrings()
	{
		$recipients = [];
		/** @var $recipients_objects Recipient[] */
		foreach ([$this->to, $this->copy_to, $this->blind_copy_to] as $recipients_objects) {
			foreach ($recipients_objects as $recipient) {
				$recipients[$recipient->email] = $recipient->email;
			}
		}
	}

}
