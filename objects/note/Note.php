<?php
namespace ITRocks\Framework\Objects;

use ITRocks\Framework\Tools\Date_Time;

/**
 * @business
 * @display_order text, title, date, object
 * @feature
 * @feature summaryEdit
 * @feature summaryOutput
 * @sort -date, title
 */
class Note
{

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @default Date_Time::nowMinute
	 * @link DateTime
	 * @mandatory
	 * @see Date_Time::nowMinute
	 * @var Date_Time
	 */
	public $date;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @link Object
	 * @mandatory
	 * @user hidden
	 * @var object
	 */
	public $object;

	//----------------------------------------------------------------------------------------- $text
	/**
	 * @mandatory
	 * @max_length 1024
	 * @multiline
	 * @var string
	 */
	public $text;

	//---------------------------------------------------------------------------------------- $title
	/**
	 * @var string
	 */
	public $title;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		$text = lParse(strval($this->text), LF);
		return $this->title
			? strval($this->title)
			: ((strlen($text) > 32) ? (substr($text, 0, 32) . '...') : $text);
	}

}
