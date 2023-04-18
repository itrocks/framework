<?php
namespace ITRocks\Framework\Traits\Identifier;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Traits\Identifier;

/**
 * For classes that need an unique identifier
 *
 * @before_write uniqueIdentifier
 */
trait Has_Identifier
{

	//----------------------------------------------------------------------------------- $identifier
	public ?Identifier $identifier;

	//------------------------------------------------------------------------------ uniqueIdentifier
	/**
	 * Set identifier unique
	 */
	public function uniqueIdentifier() : void
	{
		if (!isset($this->identifier)) {
			return;
		}
		if ($find = Dao::searchOne(['name' => $this->identifier->name], Identifier::class)) {
			Dao::replace($this->identifier, $find, false);
		}
		else {
			Dao::disconnect($this->identifier);
		}
	}

}
