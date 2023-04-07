<?php
namespace ITRocks\Framework\Objects;

use ITRocks\Framework\Feature\Validate\Property\Max_Length;
use ITRocks\Framework\Reflection\Attribute\Class_\Display_Order;
use ITRocks\Framework\Reflection\Attribute\Class_\Sort;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Default_;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Reflection\Attribute\Property\Multiline;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Tools\Date_Time;

/**
 * @feature
 * @feature summaryEdit
 * @feature summaryOutput
 */
#[Display_Order('text', 'title', 'date', 'object'), Sort('-date', 'title'), Store]
class Note
{

	//----------------------------------------------------------------------------------------- $date
	#[Default_([Date_Time::class, 'nowMinute']), Mandatory]
	public Date_Time|string $date;

	//--------------------------------------------------------------------------------------- $object
	#[User(User::HIDDEN)]
	public object $object;

	//----------------------------------------------------------------------------------------- $text
	#[Mandatory, Max_Length(1024), Multiline]
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
