<?php
namespace SAF\Framework\Traits\Identifier;

use SAF\Framework\Dao;
use SAF\Framework\Mapper\Search_Object;
use SAF\Framework\Traits\Identifier;

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
	 * @var Identifier
	 */
	public $identifier;

	//------------------------------------------------------------------------------ uniqueIdentifier
	/**
	 * Set identifier unique
	 */
	public function uniqueIdentifier()
	{
		if (isset($this->identifier)) {
			/** @var $search Identifier */
			$search = Search_Object::create(Identifier::class);
			$search->name = $this->identifier->name;
			if ($find = Dao::searchOne($search)) {
				Dao::replace($this->identifier, $find, false);
			}
			else {
				Dao::disconnect($this->identifier);
			}
		}
	}

}
