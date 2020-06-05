<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Email\Account;
use ITRocks\Framework\Email\Attachment;
use ITRocks\Framework\Email\Output\Has_Output_Properties;
use ITRocks\Framework\Email\Recipient;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\Tools\Date_Time;

/**
 * A ITRocks electronic mail object to get full access to mails without depending on MIME or the else
 *
 * @before_write beforeWrite
 * @business
 * @display_order account, date, send_date, receive_date, from, to, copy_to, blind_copy_to,
 * reply_to, return_path, headers, send_message, uidl, subject, content, attachments
 * @representative date, from, to, subject
 */
class Email
{
	use Has_Output_Properties;

	//-------------------------------------------------------------------------------------- $account
	/**
	 * @link Object
	 * @user hide_empty
	 * @var Account
	 */
	public $account;

	//---------------------------------------------------------------------------------- $attachments
	/**
	 * @link Map
	 * @user hide_empty
	 * @var Attachment[]
	 */
	public $attachments;

	//-------------------------------------------------------------------------------- $blind_copy_to
	/**
	 * @alias bcc
	 * @link Map
	 * @set_store_name emails_recipients_blind_copy_to
	 * @user hide_empty
	 * @var Recipient[]
	 */
	public $blind_copy_to;

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @dao files
	 * @editor quill simple
	 * @max_length 10000000
	 * @multiline
	 * @store gz
	 * @var string
	 */
	public $content;

	//-------------------------------------------------------------------------------------- $copy_to
	/**
	 * @alias cc
	 * @link Map
	 * @set_store_name emails_recipients_copy_to
	 * @user hide_empty
	 * @var Recipient[]
	 */
	public $copy_to;

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
	 * @getter
	 * @null
	 * @store json
	 * @user invisible
	 * @var string[]
	 */
	public $headers;

	//--------------------------------------------------------------------------------- $receive_date
	/**
	 * @link DateTime
	 * @user hide_empty
	 * @var Date_Time
	 */
	public $receive_date;

	//------------------------------------------------------------------------------------- $reply_to
	/**
	 * @link Object
	 * @user hide_empty
	 * @var Recipient
	 */
	public $reply_to;

	//---------------------------------------------------------------------------------- $return_path
	/**
	 * @link Object
	 * @user hide_empty
	 * @var Recipient
	 */
	public $return_path;

	//------------------------------------------------------------------------------------ $send_date
	/**
	 * @link DateTime
	 * @user hide_empty
	 * @var Date_Time
	 */
	public $send_date;

	//--------------------------------------------------------------------------------- $send_message
	/**
	 * @user hide_empty, readonly
	 * @var string
	 */
	public $send_message;

	//-------------------------------------------------------------------------------------- $subject
	/**
	 * @data focus
	 * @var string
	 */
	public $subject;

	//------------------------------------------------------------------------------------------- $to
	/**
	 * @link Map
	 * @set_store_name emails_recipients_to
	 * @user hide_empty
	 * @var Recipient[]
	 */
	public $to;

	//----------------------------------------------------------------------------------------- $uidl
	/**
	 * The unique identification number of the mail into the distant server once it has been
	 * received / sent
	 *
	 * @user hide_empty, readonly
	 * @var string
	 */
	public $uidl;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->subject);
	}

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
		$this->uniqueRecipients();
	}

	//---------------------------------------------------------------------------------- encodeHeader
	/**
	 * @param $text string
	 * @return string
	 */
	protected function encodeHeader($text)
	{
		return mb_encode_mimeheader(strval($text), 'utf-8', 'Q');
	}

	//------------------------------------------------------------------------------------ getHeaders
	/**
	 * store json is not enough to decode the json string and change it into an array
	 *
	 * @return string[]
	 */
	protected function getHeaders()
	{
		if (is_string($this->headers)) {
			$this->headers = json_decode($this->headers, true);
		}
		return $this->headers;
	}

	//--------------------------------------------------------------------------- getHeadersAsStrings
	/**
	 * @return string[]
	 */
	public function getHeadersAsStrings()
	{
		if ($this->blind_copy_to) {
			$this->headers['Bcc'] = $this->encodeHeader(join(',', $this->blind_copy_to));
		}
		if ($this->copy_to) {
			$this->headers['Cc'] = $this->encodeHeader(join(',', $this->copy_to));
		}
		if (!isset($this->headers['Date'])) {
			$this->headers['Date'] = $this->date->format('D, j M Y H:i:s p');
		}
		if ($this->from) {
			$this->headers['From'] = $this->encodeHeader($this->from);
		}
		if (!isset($this->headers['Message-ID']) && Dao::getObjectIdentifier($this)) {
			$project = strtolower(mParse(get_class(Application::current()), BS, BS));
			$this->headers['Message-ID'] = '<'
				. Dao::getObjectIdentifier($this) . '-' . $project . '@' . Session::current()->domainName()
				. '>';
		}
		if ($this->reply_to) {
			$this->headers['Reply-To'] = $this->encodeHeader($this->reply_to);
		}
		if ($this->return_path) {
			$this->headers['Return-Path'] = $this->encodeHeader($this->return_path);
		}
		if ($this->subject) {
			$this->headers['Subject'] = $this->encodeHeader($this->subject);
		}
		if ($this->to) {
			$this->headers['To'] = $this->encodeHeader(join(',', $this->to));
		}
		if (!isset($this->headers['Content-Type'])) {
			$this->headers['Content-Type'] = 'text/html; charset=utf-8';
		}
		return $this->headers;
	}

	//------------------------------------------------------------------------------ getHeadersString
	/**
	 * @return string
	 */
	public function getHeadersString()
	{
		$headers = [];
		foreach ($this->getHeadersAsStrings() as $key => $value) {
			$headers[] = $key . ': ' . $value;
		}
		return join(LF, $headers);
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

	//------------------------------------------------------------------------------ uniqueRecipients
	/**
	 * Be sure that recipients are unique into data storage
	 * - they can be common to several emails
	 * - modification of recipients is not allowed
	 */
	private function uniqueRecipients()
	{
		$search     = Search_Object::create(Recipient::class);
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

	//---------------------------------------------------------------------------------------- update
	/**
	 * Rewrite content of all emails, in order to have it compressed
	 * This is a simple rewrite. Mysql\Link does all the work (inflate-deflate) !
	 *
	 * Call this update script using http://itrocks/sfkgroup/ITRocks/Framework/Email/update
	 *
	 * @param $parameters Parameters
	 * @return string
	 */
	public function update(Parameters $parameters)
	{
		$search = ['content' => Func::notEqual('')];
		if (!$parameters->contains('all') && !$parameters->has('all')) {
			if ($date = $parameters->getRawParameter(0)) {
				$search['date'] = Func::like(Loc::dateToIso($date) . '%');
			}
			elseif ($date = $parameters->getRawParameter('since')) {
				$search['date'] = Func::greaterOrEqual(Loc::dateToIso($date));
			}
			else {
				$search['date'] = Func::greaterOrEqual(Date_Time::today());
			}
		}
		$limit = ($parameters->getRawParameter('limit') || !$parameters->contains('limit'))
			? Dao::limit($parameters->getRawParameter('limit') ?: 1000)
			: null;
		$emails = Dao::search($search, static::class, $limit ?: []);
		foreach ($emails as $email) {
			Dao::write($email, Dao::only('content'));
		}
		return 'OK (' . count($emails) . ')';
	}

}
