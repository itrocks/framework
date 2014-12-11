<?php
namespace SAF\Framework\Email;

/**
 * An email recipient (or sender, this object can be used for both)
 */
class Recipient
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

	//----------------------------------------------------------------------------------- __construct
	public function __construct($email = null, $name = null)
	{
		if (isset($email)) $this->email = $email;
		if (isset($name))  $this->name  = $name;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return sprintf(
			$this->name ? '%s <%s>' : '%s%s',
			str_replace('<', '>', $this->name),
			$this->email
		);
	}

}
