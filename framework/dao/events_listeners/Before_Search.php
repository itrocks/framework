<?php
namespace SAF\Framework;

/**
 * Classes implementing this interface will execute beforeSearch() before the object is searched by data link
 */
interface Before_Search
{

	//---------------------------------------------------------------------------------- beforeSearch
	/**
	 * @param $what       object|array source object for filter, or filter array (need class_name) only set properties will be used for search
	 * @return boolean if returns true, then the object can be searched, else it won't and search
	 * result will be an empty array !
	 */
	public static function beforeSearch(&$what);

}
