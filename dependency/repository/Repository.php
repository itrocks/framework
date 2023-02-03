<?php
namespace ITRocks\Framework\Dependency;

use ITRocks\Framework\Dependency\Repository\Scanner;

class Repository
{

	//-------------------------------------------------------------------------------- Flag constants
	public const REFRESH = 2;
	public const VENDOR  = 1;

	//------------------------------------------------------------------------------------- $by_class
	/** int[$class][$dependency][$type][$file][] */
	protected array $by_class = [];

	//-------------------------------------------------------------------------------- $by_class_type
	/** int[$class][$type][$dependency][$file][] */
	protected array $by_class_type = [];

	//-------------------------------------------------------------------------------- $by_dependency
	/** int[$dependency][$class][$type][$file][] */
	protected array $by_dependency = [];

	//--------------------------------------------------------------------------- $by_dependency_type
	/** int[$dependency][$type][$class][$file][] */
	protected array $by_dependency_type = [];

	//-------------------------------------------------------------------------------------- $by_file
	/** int[$file_name][$class][$dependency][$type][] */
	protected array $by_file = [];

	//---------------------------------------------------------------------------- $directories_count
	public int $directories_count = 0;

	//---------------------------------------------------------------------------------- $files_count
	public int $files_count = 0;

	//----------------------------------------------------------------------------------------- $home
	/** Home directory, without right '/' */
	protected string $home;

	//----------------------------------------------------------------------------------- $references
	protected array $references = [];

	//----------------------------------------------------------------------------- $references_count
	public int $references_count = 0;

	//-------------------------------------------------------------------------------------- $refresh
	protected bool $refresh;

	//-------------------------------------------------------------------------------- $refresh_files
	/** @var string[] */
	protected array $refresh_files = [];

	//---------------------------------------------------------------------------------------- $start
	public int $start;

	//--------------------------------------------------------------------------------------- $vendor
	protected bool $vendor;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $home = '', int $flags = 0)
	{
		if ($home === '') {
			$home = str_replace('\\', '/', getcwd());
			while (
				str_contains($home, '/')
				&& !file_exists("$home/cache")
				&& !file_exists("$home/composer.lock")
			) {
				$home = substr($home, 0, strrpos($home, '/'));
			}
		}
		if (!is_dir($home)) die("Missing directory $home");
		if (
			($flags & self::REFRESH)
			&& !str_contains('"', $home)
			&& is_dir("$home/cache/dependencies")
		) {
			exec('rm -r "'.$home.'/cache/dependencies"');
			clearstatcache(true);
		}
		if (!is_dir("$home/cache"))              mkdir("$home/cache");
		if (!is_dir("$home/cache/dependencies")) mkdir("$home/cache/dependencies");
		$this->refresh = $flags & self::REFRESH;
		$this->home    = $home;
		$this->start   = time();
		$this->vendor  = $flags & self::VENDOR;
	}

	//-------------------------------------------------------------------------------- cacheDirectory
	protected function cacheDirectory() : string
	{
		return $this->home . '/cache/dependencies';
	}

	//--------------------------------------------------------------------------------- cacheFileName
	protected function cacheFileName(string $name, string $type = 'file') : string
	{
		$directory = $this->cacheDirectory();
		$file_name = str_replace(['/', '\\'], '-', $name);
		$file_name = (str_ends_with($file_name, '.php') ? substr($file_name, 0, -4) : $file_name)
			. '.json';
		return "$directory/$type/$file_name";
	}

	//-------------------------------------------------------------------------------------- classify
	public function classify() : void
	{
		$home_length = strlen($this->home) + 1;
		foreach ($this->references as $file_name => &$references) {
			$file_name = substr($file_name, $home_length);
			if (!$references) {
				$this->by_file[$file_name] = [];
			}
			foreach ($references as $reference) {
				$this->by_file[$file_name][$reference[0]][$reference[1]][$reference[2]][]
					= $reference[3];
				if ($reference[0] !== '') {
					$this->by_class[$reference[0]][$reference[1]][$reference[2]][$file_name][]
						= $reference[3];
					$this->by_class_type[$reference[0]][$reference[2]][$reference[1]][$file_name][]
						= $reference[3];
				}
				if (!$this->refresh && ($reference[0] !== $reference[1])) {
					if (
						!isset($this->by_dependency[$reference[1]])
						&& file_exists($cache_file = $this->cacheFileName($reference[1], 'dependency'))
					) {
						$this->by_dependency[$reference[1]] = json_decode(
							file_get_contents($cache_file), JSON_OBJECT_AS_ARRAY
						);
						$this->removeFileFrom($this->by_dependency[$reference[1]], $file_name);
					}
					if (
						!isset($this->by_dependency_type[$reference[1]])
						&& file_exists($cache_file = $this->cacheFileName($reference[1], 'dependency_type'))
					) {
						$this->by_dependency_type[$reference[1]] = json_decode(
							file_get_contents($cache_file), JSON_OBJECT_AS_ARRAY
						);
						$this->removeFileFrom($this->by_dependency_type[$reference[1]], $file_name);
					}
				}
				if ($reference[1] !== '') {
					$this->by_dependency[$reference[1]][$reference[0]][$reference[2]][$file_name][]
						= $reference[3];
					$this->by_dependency_type[$reference[1]][$reference[2]][$reference[0]][$file_name][]
						= $reference[3];
				}
			}
			$references = null;
		}
		$this->references = [];
	}

	//-------------------------------------------------------------------------------- removeFileFrom
	protected function removeFileFrom(array &$references, $file_name) : void
	{
		foreach ($references as $key => &$references1) {
			foreach ($references1 as $key1 => &$references2) {
				unset($references2[$file_name]);
				if (!$references2) unset($references1[$key1]);
			}
			if (!$references1) unset($references[$key]);
		}
	}

	//------------------------------------------------------------------------------------------- run
	public function run() : void
	{
		$this->scanDirectory();
		$this->classify();
		$this->save();
	}

	//------------------------------------------------------------------------------------ runConsole
	public function runConsole() : void
	{
		error_reporting(E_ALL);
		include 'Scanner.php';

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

	//------------------------------------------------------------------------------------------ save
	public function save() : void
	{
		$this->files_count = 0;
		$json_flags = JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES;
		$directory  = $this->cacheDirectory();
		if (!is_dir("$directory/file")) mkdir("$directory/file");
		foreach ($this->by_file as $file_name => $references) {
			$file_name = $this->cacheFileName($file_name);
			$classes   = array_keys($references);
			file_put_contents($file_name, json_encode($classes, $json_flags));
			touch($file_name, $this->start);
			$this->files_count ++;
		}
		foreach (['class', 'class_type', 'dependency', 'dependency_type'] as $type) {
			if (!is_dir("$directory/$type")) mkdir("$directory/$type");
			foreach ($this->{"by_$type"} as $name => $references) if ($name !== '') {
				$file_name = $this->cacheFileName($name, $type);
				file_put_contents($file_name, json_encode($references, $json_flags));
				touch($file_name, $this->start);
				$this->files_count ++;
			}
		}
	}

	//--------------------------------------------------------------------------------- scanDirectory
	public function scanDirectory(string $directory = '', int $depth = 0) : void
	{
		$home_length = strlen($this->home) + 1;
		if ($directory === '') {
			$directory = $this->home;
		}
		$this->directories_count ++;
		foreach (scandir($directory) as $file) if (!str_starts_with($file, '.')) {
			$file = "$directory/$file";
			if (is_dir($file) && ($depth || $this->vendor || !str_ends_with($file, '/vendor'))) {
				$this->scanDirectory($file, $depth + 1);
			}
			elseif (
				str_ends_with($file, '.php')
				&& (
					$this->refresh
					|| !file_exists($cache_file = $this->cacheFileName(substr($file, $home_length)))
					|| (filemtime($file) > filemtime($cache_file))
					|| str_contains($file, 'repository/Repository')
				)
			) {
				if (!$this->refresh) {
					$this->refresh_files[substr($file, $home_length)] = true;
				}
				$this->scanFile($file);
			}
		}
	}

	//-------------------------------------------------------------------------------------- scanFile
	public function scanFile(string $file) : void
	{
		$this->files_count ++;
		echo "SCAN FILE $file\n";
		$tokens  = token_get_all(file_get_contents($file));
		$scanner = new Scanner();
		$scanner->scan($tokens);
		$this->references[$file] = $scanner->references;
		$this->references_count += count($scanner->references);
	}

}

if (isset($argv[0]) && isset($_SERVER['PHP_SELF']) && ($argv[0] === $_SERVER['PHP_SELF'])) {
	(new Repository('', Repository::REFRESH))->runConsole();
}
