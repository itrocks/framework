<?php
namespace SAF\Framework;

/**
 * An email recipient (or sender, this object can be used for both)
 */
class Email_Recipient
{

	//---------------------------------------------------------------------------------------- $email
	/**
	 * @var string
	 */
	public $email;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return str_replace(['<', '>'], '', $this->name) . ' <' . $this->email . '>';
	}

}
