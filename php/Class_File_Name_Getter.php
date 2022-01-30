<?php
namespace ITRocks\Framework\PHP;

/**
 *
 */
interface Class_File_Name_Getter
{

	//------------------------------------------------------------------------------ getClassFileName
	/**
	 * Give a class name and gets its matching file name or Reflection Source.
	 * Implemented by autoloaders.
	 *
	 * @param $class_name string
	 * @return Reflection_Source|string|null
	 */
	public function getClassFileName(string $class_name) : Reflection_Source|string|null;

}
