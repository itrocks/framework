<?php
namespace SAF\Framework;

/**
 * Classes implementing this interface will execute beforeSearch() before the object is searched by data link
 */
interface Before_Search_Listener
{

	//---------------------------------------------------------------------------------- beforeSearch
	/**
	 * @return boolean if returns true, then the object can be searched, else it won't and search
	 * result will be an empty array !
	 */
	public static function beforeSearch();

}
