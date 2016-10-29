<?php
namespace SAF\Framework\Dao\Option;

use SAF\Framework\Dao\Option;

/**
 * A DAO limit option
 */
class Limit implements Option
{

	//---------------------------------------------------------------------------------------- $count
	/**
	 * If set, Dao queries will work only on $count elements
	 *
	 * @example Dao::readAll('SAF\Framework\User', Dao::limit(10));
	 * Will return the 10 first read users objects
	 * @mandatory
	 * @var integer
	 */
	public $count;

	//---------------------------------------------------------------------------------- $double_pass
	/**
	 * @var boolean
	 */
	public $double_pass = false;

	//----------------------------------------------------------------------------------------- $from
	/**
	 * If set, Dao queries will start only from the $from'th element
	 *
	 * @example Dao::readAll('SAF\Framework\User', Dao::limit(2, 10));
	 * Will return 10 read users objects, starting with the second read user
	 * @var integer
	 */
	public $from;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a DAO limit option
	 * if only one parameter is given, it will be the value for $count and $from will be null
	 *
	 * @example Dao::readAll(SAF\Framework\User::class, Dao::limit(2, 10));
	 * Will return 10 read users objects, starting with the second read user
	 * @example Dao::readAll(SAF\Framework\User::class, Dao::limit(10));
	 * Will return the 10 first read users objects
	 *
	 * @param $from        integer The offset of the first object to return
	 *                     (or the maximum number of objects to return if $count is null)
	 * @param $count       integer The maximum number of objects to return
	 * @param $double_pass boolean If true, two queries will be used to get faster
	 */
	public function __construct($from = null, $count = null, $double_pass = false)
	{
		if (isset($from)) {
			if (isset($count)) {
				$this->from = $from;
			}
			else {
				$this->count = $from;
			}
		}
		if (isset($count)) {
			$this->count = $count;
		}
		if (isset($double_pass)) {
			$this->double_pass = $double_pass;
		}
	}

}
