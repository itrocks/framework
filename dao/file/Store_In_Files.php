<?php
namespace ITRocks\Framework\Dao\File;

use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;

/**
 * Apply this interface to File to allow storage of file content into the file system instead of the
 * default data link
 *
 * @override content @dao files
 */
#[Extend(File::class)]
interface Store_In_Files
{

}
