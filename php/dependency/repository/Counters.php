<?php
namespace ITRocks\Framework\PHP\Dependency\Repository;

use ITRocks\Framework\PHP\Dependency\Repository;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;

#[Extend(Repository::class)]
trait Counters
{

	//---------------------------------------------------------------------------- $directories_count
	public int $directories_count = 0;

	//---------------------------------------------------------------------------------- $files_count
	public int $files_count = 0;

	//----------------------------------------------------------------------------- $references_count
	public int $references_count = 0;

}
