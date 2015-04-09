<?php
namespace SAF\Framework\Email;

use SAF\Framework\Traits\Has_Name;

/**
 * An email recipient (or sender, this object can be used for both)
 */
class Recipient
{
	use Has_Name;

	//---------------------------------------------------------------------------------------- $email
	/**
	 * @var string
	 */
	public $email;

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
