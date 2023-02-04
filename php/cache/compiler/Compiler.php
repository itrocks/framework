<?php
namespace ITRocks\Framework\PHP\Cache;

use ITRocks\Framework\AOP\Weaver;
use ITRocks\Framework\PHP\Dependency\Repository;
use ITRocks\Framework\Session;

class Compiler
{

	//---------------------------------------------------------------------------------------- $files
	/** @var string[][] string $line[string $file_name][int $line_number-1] */
	protected array $files;

	//----------------------------------------------------------------------------------- $repository
	protected Repository $repository;

	//----------------------------------------------------------------------------------- __construct
	public function __construct()
	{
		$this->repository = Repository::get();
	}

	//--------------------------------------------------------------------------------------- compile
	public function compile() : void
	{
		$this->repository->update();
		$joinpoint_classes = array_keys(Session::current()->plugins->get(Weaver::class)->getJoinpoints());
		foreach ($this->repository->refresh_files as $file_name) {
			foreach ($this->repository->fileClasses($file_name) as $class_name) {
				if (in_array($class_name, $joinpoint_classes)) {
					echo "Apply AOP on $class_name<br>";
				}
			}
		}
	}

}
