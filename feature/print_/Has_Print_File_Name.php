<?php
namespace ITRocks\Framework\Feature\Print_;

/**
 * Apply this interface to documents you want to get a specific name on print
 */
interface Has_Print_File_Name
{

	//--------------------------------------------------------------------------------- printFileName
	/**
	 * Returns the name of the print file for a given list of documents
	 *
	 * @param $objects array
	 * @return string
	 */
	public function printFileName(array $objects);

}
