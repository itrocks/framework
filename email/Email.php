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
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Class_\Display_Order;
use ITRocks\Framework\Reflection\Attribute\Property\Alias;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Reflection\Attribute\Property\Multiline;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Tools\Date_Time;

/**
 * A ITRocks electronic mail object to get full access to mails without depending on MIME or the else
 *
 * @before_write beforeWrite
 * @feature admin
 * @feature edit
 * @feature json
 * @feature output
 * @feature resend
 * @feature send
 * @representative date, from, to, subject
 */
#[Class_\Store]
#[Display_Order(
	'account', 'date', 'send_date', 'receive_date', 'from', 'to', 'copy_to', 'blind_copy_to',
	'reply_to', 'return_path', 'headers', 'send_message', 'uidl', 'subject', 'content', 'attachments'
)]
class Email
{
	use Has_Output_Properties;

	//-------------------------------------------------------------------------------------- $account
	#[User(User::HIDE_EMPTY)]
	public ?Account $account = null;

	//---------------------------------------------------------------------------------- $attachments
	/** @var Attachment[] */
	#[User(User::HIDE_EMPTY)]
	public array $attachments = [];

	//-------------------------------------------------------------------------------- $blind_copy_to
	/**
	 * @set_store_name emails_recipients_blind_copy_to
	 * @var Recipient[]
	 */
	#[Alias('bcc'), User(User::HIDE_EMPTY)]
	public array $blind_copy_to = [];

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @dao files
	 * @editor quill simple
	 * @max_length 10000000
	 */
	#[Multiline, Store(Store::GZ)]
	public string $content = '';

	//-------------------------------------------------------------------------------------- $copy_to
	/**
	 * @set_store_name emails_recipients_copy_to
	 * @var Recipient[]
	 */
	#[Alias('cc'), User(User::HIDE_EMPTY)]
	public array $copy_to = [];

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @default Date_Time::now
	 * @see Date_Time::now
	 */
	public Date_Time|string $date;

	//----------------------------------------------------------------------------------------- $from
	#[User(User::INVISIBLE_EDIT)]
	public ?Recipient $from;

	//-------------------------------------------------------------------------------------- $headers
	/**
	 * @null
	 * @var string|string[]
	 */
	#[Getter, Store(Store::JSON), User(User::INVISIBLE)]
	public array|string $headers = [];

	//--------------------------------------------------------------------------------- $receive_date
	#[User(User::HIDE_EMPTY)]
	public Date_Time|string $receive_date;

	//------------------------------------------------------------------------------------- $reply_to
	#[User(User::HIDE_EMPTY)]
	public ?Recipient $reply_to = null;

	//---------------------------------------------------------------------------------- $return_path
	#[User(User::HIDE_EMPTY)]
	public ?Recipient $return_path = null;

	//------------------------------------------------------------------------------------ $send_date
	#[User(User::HIDE_EMPTY)]
	public Date_Time|string $send_date;

	//--------------------------------------------------------------------------------- $send_message
	#[User(User::HIDE_EMPTY, User::READONLY)]
	public string $send_message = '';

	//-------------------------------------------------------------------------------------- $subject
	/** @data focus */
	public string $subject = '';

	//------------------------------------------------------------------------------------------- $to
	/**
	 * @set_store_name emails_recipients_to
	 * @var Recipient[]
	 */
	#[User(User::HIDE_EMPTY)]
	public array $to = [];

	//----------------------------------------------------------------------------------------- $uidl
	/**
	 * The unique identification number of the mail into the distant server once it has been
	 * received / sent
	 */
	#[User(User::HIDE_EMPTY, User::READONLY)]
	public string $uidl = '';

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->subject;
	}

	//----------------------------------------------------------------------------------- beforeWrite
	/**
	 * Called before write : optimize attachments and recipients.
	 * - Reuse those which are already stored into the data storage.
	 * - Do not enable to alter an already stored attachment or recipient.
	 */
	public function beforeWrite() : void
	{
		if (!isset($this->date)) {
			$this->date = new Date_Time();
		}
		$this->uniqueAttachments();
		$this->uniqueRecipients();
	}

	//---------------------------------------------------------------------------------- encodeHeader
	protected function encodeHeader(string $text) : string
	{
		return mb_encode_mimeheader($text, 'utf-8', 'Q');
	}

	//------------------------------------------------------------------------------- encodeRecipient
	protected function encodeRecipient(Recipient $recipient) : string
	{
		if ($recipient->name) {
			$name = str_replace([DQ, '<', '>'], [BS . DQ, '', ''], $recipient->name);
			return mb_encode_mimeheader($name, 'utf-8', 'Q') . SP . '<' . $recipient->email . '>';
		}
		return $recipient->email;
	}

	//------------------------------------------------------------------------------------ getHeaders
	/**
	 * Store json is not enough to decode the json string and change it into an array.
	 *
	 * @return string[]
	 */
	protected function & getHeaders() : array
	{
		if (is_string($this->headers)) {
			$this->headers = json_decode($this->headers, true);
		}
		return $this->headers;
	}

	//--------------------------------------------------------------------------- getHeadersAsStrings
	/** @return string[] */
	public function getHeadersAsStrings() : array
	{
		if ($this->blind_copy_to) {
			$this->headers['Bcc'] = $this->encodeHeader($this->mimeRecipients($this->blind_copy_to));
		}
		if ($this->copy_to) {
			$this->headers['Cc'] = $this->encodeHeader($this->mimeRecipients($this->copy_to));
		}
		if (!isset($this->headers['Date'])) {
			$this->headers['Date'] = $this->date->format('D, j M Y H:i:s O');
		}
		if ($this->from) {
			$from = $this->from;
			if ($from->name) {
				$from       = clone $from;
				$from->name = str_replace(DOT, SP, $from->name);
			}
			$this->headers['From'] = $this->encodeRecipient($from);
		}
		if (!isset($this->headers['Message-ID']) && Dao::getObjectIdentifier($this)) {
			$project = strtolower(mParse(get_class(Application::current()), BS, BS));
			$this->headers['Message-ID']
				= Dao::getObjectIdentifier($this) . '-' . $project . '@' . Session::current()->domainName();
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
			$this->headers['To'] = $this->encodeHeader($this->mimeRecipients($this->to));
		}
		if (!isset($this->headers['Content-Type'])) {
			$this->headers['Content-Type'] = 'text/html; charset=utf-8';
		}
		return $this->headers;
	}

	//------------------------------------------------------------------------------ getHeadersString
	public function getHeadersString() : string
	{
		$headers = [];
		foreach ($this->getHeadersAsStrings() as $key => $value) {
			$headers[] = $key . ': ' . $value;
		}
		return join(LF, $headers);
	}

	//------------------------------------------------------------------------ getRecipientsAsStrings
	/** @return string[] */
	public function getRecipientsAsStrings() : array
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

	//-------------------------------------------------------------------------------- mimeRecipients
	/**
	 * @param $recipients Recipient[]
	 * @return string
	 */
	protected function mimeRecipients(array $recipients) : string
	{
		$result = [];
		foreach ($recipients as $recipient) {
			$result[] = $this->encodeRecipient($recipient);
		}
		return join(',', $result);
	}

	//----------------------------------------------------------------------------- uniqueAttachments
	/**
	 * Be sure that attachments are unique into data storage
	 * - they can be common for several emails
	 * - modification of attachments is not allowed
	 */
	private function uniqueAttachments() : void
	{
		foreach ($this->attachments as $attachment) {
			if (
				($search['hash'] = $attachment->hash)
				&& ($find = Dao::searchOne($search, Attachment::class))
				&& ($find->name === $attachment->name)
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
	private function uniqueRecipients() : void
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
	 */
	public function update(Parameters $parameters) : string
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
