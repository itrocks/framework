<?php
namespace SAF\Framework;

/**
 * Builds a File Type by analysing a file extension and checking it's content
 */
abstract class File_Type_Builder
{

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param $file_name string
	 * @return File_Type or null if the file type is unknown
	 */
	public static function build($file_name)
	{
		$file_extension = rLastParse($file_name, DOT);
		$type = File_Type::fileExtensionToTypeString($file_extension);
		return isset($type) ? new File_Type($type) : new File_Type('empty/empty');
	}

}
