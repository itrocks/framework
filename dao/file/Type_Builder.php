<?php
namespace ITRocks\Framework\Dao\File;

/**
 * Builds a File Type by analysing a file extension and checking its content
 */
abstract class Type_Builder
{

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param $file_name string
	 * @return Type or null if the file type is unknown
	 */
	public static function build(string $file_name) : Type
	{
		$file_extension = rLastParse($file_name, DOT);
		$type           = Type::fileExtensionToTypeString($file_extension);
		return isset($type) ? new Type($type) : new Type('empty/empty');
	}

}
