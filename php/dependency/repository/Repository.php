<?php
namespace ITRocks\Framework\PHP\Dependency;

use ITRocks\Framework\PHP\Dependency\Repository\Cache_Path;
use ITRocks\Framework\PHP\Dependency\Repository\Classify;
use ITRocks\Framework\PHP\Dependency\Repository\Counters;
use ITRocks\Framework\PHP\Dependency\Repository\Get_Data;
use ITRocks\Framework\PHP\Dependency\Repository\Save;
use ITRocks\Framework\PHP\Dependency\Repository\Scanner;

if (isset($argv[0]) && isset($_SERVER['PHP_SELF']) && ($argv[0] === $_SERVER['PHP_SELF'])) {
	include __DIR__ . '/Cache_Path.php';
	include __DIR__ . '/Classify.php';
	include __DIR__ . '/Counters.php';
	include __DIR__ . '/Get_Data.php';
	include __DIR__ . '/Save.php';
	include __DIR__ . '/Scanner.php';
}

class Repository
{
	use Cache_Path, Classify, Counters, Get_Data, Save, Scanner;

	//----------------------------------------------------------------------------------------- FLAGS
	public const REFRESH = 2;
	public const VENDOR  = 1;

	//------------------------------------------------------------------------------------- $by_class
	/** int $line[string $class][string $dependency][string $type][string $file][int] */
	protected array $by_class = [];

	//-------------------------------------------------------------------------------- $by_class_type
	/** int $line[string $class][string $type][string $dependency][string $file][int] */
	protected array $by_class_type = [];

	//-------------------------------------------------------------------------------- $by_dependency
	/** int $line[string $dependency][string $class][string $type][string $file][int] */
	protected array $by_dependency = [];

	//--------------------------------------------------------------------------- $by_dependency_type
	/** int $line[string $dependency][string $type][string $class][string $file][int] */
	protected array $by_dependency_type = [];

	//-------------------------------------------------------------------------------------- $by_file
	/** string $reference[string $file_name][string $reference_type][int] */
	protected array $by_file = [];

	//-------------------------------------------------------------------------------- $by_type_class
	/** int $line[string $type][string $class][string $dependency][string $file][int] */
	protected array $by_type_class = [];

	//--------------------------------------------------------------------------- $by_type_dependency
	/** int $line[string $type][string $dependency][string $class][string $file][int] */
	protected array $by_type_dependency = [];

	//-------------------------------------------------------------------------------- $file_contents
	/** @var string[] [$file_name => $file_content] */
	protected array $file_contents = [];

	//---------------------------------------------------------------------------------------- $files
	/** @var string[] string $file_name[int] */
	protected array $files = [];

	//----------------------------------------------------------------------------------- $references
	/** @var array[] [string $class, string $dependency, string $type, int $line][int] */
	protected array $references = [];

	//-------------------------------------------------------------------------------------- $refresh
	protected bool $refresh;

	//-------------------------------------------------------------------------------- $refresh_files
	/** @var string[] */
	public array $refresh_files = [];

	//------------------------------------------------------------------------------------ $singleton
	private static self $singleton;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $home = '', int $flags = 0)
	{
		$this->start_time = time();
		$this->refresh    = $flags & self::REFRESH;
		$this->vendor     = $flags & self::VENDOR;
		$this->setHome($home);
		$this->prepareHome();
	}

	//------------------------------------------------------------------------------------------- get
	public static function get() : static
	{
		return static::$singleton ?? (static::$singleton = new static);
	}

	//------------------------------------------------------------------------------------ runConsole
	public function runConsole() : void
	{
		error_reporting(E_ALL);
		include __DIR__ . '/../Tokens_Scanner.php';

		echo date('Y-m-d H:i:s') . "\n";
		$total = microtime(true);

		$start = microtime(true);
		$this->scanDirectory();
		echo "- scanned $this->directories_count directories and $this->files_count files in "
			. round(microtime(true) - $start, 3) . " seconds\n";

		$start = microtime(true);
		$this->classify();
		echo "- classified $this->references_count references in "
			. round(microtime(true) - $start, 3) . " seconds\n";

		$start = microtime(true);
		$this->save();
		echo "- saved $this->files_count files in "
			. round(microtime(true) - $start, 3) . " seconds\n";

		echo date('Y-m-d H:i:s') . "\n";
		echo 'duration = ' . round(microtime(true) - $total, 3) . " seconds\n";
		echo 'memory   = ' . ceil(memory_get_peak_usage(true) / 1024 / 1024) . " Mo\n";
	}

	//---------------------------------------------------------------------------------------- update
	public function update() : void
	{
		$this->references = [];
		$this->scanDirectory();
		$this->classify();
		$this->save();
	}

}

if (isset($argv[0]) && isset($_SERVER['PHP_SELF']) && ($argv[0] === $_SERVER['PHP_SELF'])) {
	(new Repository('', Repository::REFRESH/*|Repository::VENDOR*/))->runConsole();
}
