<?php
namespace SAF\Framework;

/**
 * A SAF electronic mail object to get full access to mails without depending on MIME or the else
 */
class Email
{

	//-------------------------------------------------------------------------------------- $account
	/**
	 * @link Object
	 * @var Email_Account
	 */
	public $account;

	//----------------------------------------------------------------------------------------- $from
	/**
	 * @link Object
	 * @var Email_Recipient
	 */
	public $from;

	//------------------------------------------------------------------------------------- $reply_to
	/**
	 * @link Object
	 * @var Email_Recipient
	 */
	public $reply_to;

	//---------------------------------------------------------------------------------- $return_path
	/**
	 * @link Object
	 * @var Email_Recipient
	 */
	public $return_path;

	//------------------------------------------------------------------------------------------- $to
	/**
	 * @link Map
	 * @var Email_Recipient[]
	 */
	public $to;

	//-------------------------------------------------------------------------------------- $copy_to
	/**
	 * @link Map
	 * @var Email_Recipient[]
	 */
	public $copy_to;

	//-------------------------------------------------------------------------------- $blind_copy_to
	/**
	 * @link Map
	 * @var Email_Recipient[]
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
	 * @var Email_Attachment[]
	 */
	public $attachments;

	//--------------------------------------------------------------------------- getHeadersAsStrings
	/**
	 * @return string[]
	 */
	public function getHeadersAsStrings()
	{
		return array();
	}

	//------------------------------------------------------------------------ getRecipientsAsStrings
	/**
	 * @return string[]
	 */
	public function getRecipientsAsStrings()
	{
		$recipients = array();
		/** @var $recipients_objects Email_Recipient[] */
		foreach (array($this->to, $this->copy_to, $this->blind_copy_to) as $recipients_objects) {
			foreach ($recipients_objects as $recipient) {
				$recipients[$recipient->email] = $recipient->email;
			}
		}
	}

}
