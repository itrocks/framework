<?php
namespace ITRocks\Framework\Configuration\File\Builder;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Builder;

/**
 * Builder configuration file reader
 *
 * @override file @var Builder
 * @property Builder file
 */
class Reader extends File\Reader
{

	//----------------------------------------------------------------------------- readConfiguration
	/**
	 * Read configuration : the main part of the file
	 */
	protected function readConfiguration()
	{
		$built              = null;
		$built_on_next_line = false;
		$class_name         = null;
		$in_comment         = false;
		$line               = current($this->lines);
		while (!trim($line)) {
			$line = next($this->lines);
		}
		for ($ended = false; !$ended; $line = next($this->lines)) {
			if ($this->isEndLine($line)) {
				$ended = true;
			}
			else {
				// add assembled built class components
				if ($built_on_next_line && strpos($line, '=>')) {
					$built_on_next_line = false;
					$line               = TAB . $class_name . $line;
				}
				$trim_line = trim($line);
				if (str_starts_with($line, TAB . TAB)) {
					if ($in_comment) {
						$built->components[array_key_last($built->components)] .= LF . $line;
						if (str_contains($line, '*/')) {
							$in_comment = false;
						}
					}
					elseif ($built instanceof Assembled) {
						if (str_starts_with($trim_line, '//') || !$trim_line) {
							$built->components[] = $line;
						}
						elseif (str_starts_with($trim_line, '/*')) {
							$built->components[] = $line;
							$in_comment          = true;
						}
						elseif (strStartsWith($trim_line, [DQ, Q])) {
							$component = trim(rtrim($line, ','));
							if (substr($component, 1, 1) === AT) {
								$component = trim($component, DQ . Q);
							}
							$built->components[] = $component;
						}
						else {
							foreach (explode(',', lParse($line, ']')) as $class_name) {
								if (trim($class_name)) {
									$built->components[] = $this->file->fullClassNameOf($class_name);
								}
							}
						}
					}
					else {
						trigger_error(
							'Only ' . Assembled::class . ' can accept component interface / traits,'
							. ' bad built object ' . get_class($built) . ' into file ' . $this->file->file_name
							. ' line ' . (key($this->lines) + 1),
							E_USER_ERROR
						);
					}
				}
				// add built class
				elseif (str_starts_with($line, TAB)) {
					if ($in_comment) {
						$this->file->classes[array_key_last($this->file->classes)] .= LF . $line;
						if (str_contains($line, '*/')) {
							$in_comment = false;
						}
					}
					elseif (str_starts_with($trim_line, '//') || !$trim_line) {
						$this->file->classes[] = $line;
					}
					elseif (str_starts_with($trim_line, '/*')) {
						$this->file->classes[] = $line;
						$in_comment = true;
					}
					elseif (in_array($trim_line, [']', '],'])) {
						$built = null;
					}
					else {
						$class_name = $this->file->fullClassNameOf(lParse($line, '=>'));
						// Class_Name::class =>
						if (strpos($line, '=>')) {
							// Class_Name::class => [
							if (strpos($line, '[')) {
								$built = new Assembled($class_name);
								// Class_Name::class => [ Class_Name::class, ...
								// Class_Name::class => [ Class_Name::class, ... ]
								foreach (explode(',', mParse($line, '[', ']')) as $class_name) {
									if (trim($class_name)) {
										$built->components[] = $this->file->fullClassNameOf($class_name);
									}
								}
							} // Class_Name::class => Replacement::class
							else {
								$built = new Replaced(
									$class_name,
									$this->file->fullClassNameOf(rParse($line, '=>'))
								);
							}
							$this->file->classes[] = $built;
						}
						else {
							$built_on_next_line = true;
						}
					}
				}
				elseif ($built instanceof Assembled) {
					$built->components[] = $line;
				}
				else {
					$this->file->classes[] = $line;
				}
			}
		}
	}

}
