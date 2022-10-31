<?php
namespace ITRocks\Framework\Traits;

/**
 * For all classes having a name as representative value
 *
 * @representative name
 */
trait Has_Name
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @mandatory
	 * @todo null value should disappear, as it is mandatory, but we must update serialized objects
	 * @var ?string
	 */
	public ?string $name = '';

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->name;
	}

}
