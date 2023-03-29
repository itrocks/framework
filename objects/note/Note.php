<?php
namespace ITRocks\Framework\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Display_Order;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Tools\Date_Time;

/**
 * @feature
 * @feature summaryEdit
 * @feature summaryOutput
 * @sort -date, title
 */
#[Display_Order('text', 'title', 'date', 'object'), Store]
class Note
{

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @default Date_Time::nowMinute
	 * @mandatory
	 * @see Date_Time::nowMinute
	 */
	public Date_Time|string $date;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @user hidden
	 */
	public object $object;

	//----------------------------------------------------------------------------------------- $text
	/**
	 * @mandatory
	 * @max_length 1024
	 * @multiline
	 */
	public string $text;

	//---------------------------------------------------------------------------------------- $title
	public string $title;

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		$text = lParse($this->text, LF);
		return $this->title ?: ((strlen($text) > 32) ? (substr($text, 0, 32) . '...') : $text);
	}

}
