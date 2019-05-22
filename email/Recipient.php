<?php
namespace ITRocks\Framework\Email;

use ITRocks\Framework\Traits\Has_Email;
use ITRocks\Framework\Traits\Has_Name;

/**
 * An email recipient (or sender, this object can be used for both)
 *
 * @business
 * @override name @mandatory false
 * @representative name, email
 * @sort name, email
 */
class Recipient
{
	use Has_Email;
	use Has_Name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $email string
	 * @param $name  string
	 */
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
			str_replace(['<', '>'], '', $this->name),
			$this->email
		);
	}

}
