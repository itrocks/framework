<?php
namespace ITRocks\Framework\Traits\Identifier;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\Traits\Identifier;

/**
 * For classes that need an unique identifier
 *
 * @before_write uniqueIdentifier
 */
trait Has_Identifier
{

	//----------------------------------------------------------------------------------- $identifier
	/**
	 * @link Object
	 * @var ?Identifier
	 */
	public ?Identifier $identifier;

	//------------------------------------------------------------------------------ uniqueIdentifier
	/**
	 * Set identifier unique
	 */
	public function uniqueIdentifier()
	{
		if (!isset($this->identifier)) {
			return;
		}
		$search       = Search_Object::create(Identifier::class);
		$search->name = $this->identifier->name;
		if ($find = Dao::searchOne($search)) {
			Dao::replace($this->identifier, $find, false);
		}
		else {
			Dao::disconnect($this->identifier);
		}
	}

}
