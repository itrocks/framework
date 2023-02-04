<?php
namespace ITRocks\Framework\PHP\Dependency\Repository;

use ITRocks\Framework\PHP\Dependency\Repository;
use ITRocks\Framework\PHP\Dependency\Tokens_Scanner;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;

#[Extend(Repository::class)]
trait Scanner
{

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
			elseif (str_ends_with($file, '.php')) {
				$this->files[] = substr($file, $home_length);
				if (
					$this->refresh
					|| !file_exists($cache_file = $this->cacheFileName(substr($file, $home_length), 'file'))
					|| (filemtime($file) > filemtime($cache_file))
					|| str_contains($file, 'repository/Repository')
				) {
					$this->refresh_files[] = substr($file, $home_length);
					$this->scanFile($file);
				}
			}
		}
	}

	//-------------------------------------------------------------------------------------- scanFile
	public function scanFile(string $file) : void
	{
		$this->files_count ++;
		//echo "SCAN FILE $file\n";
		$tokens  = token_get_all($this->file_contents[$file] = file_get_contents($file));
		$scanner = new Tokens_Scanner();
		$scanner->scan($tokens);
		$this->references[$file] = $scanner->references;
		$this->references_count += count($scanner->references);
	}

}
