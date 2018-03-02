<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Builder\Assembled;
use ITRocks\Framework\Configuration\File\Builder\Built;
use ITRocks\Framework\Configuration\File\Builder\Replaced;

/**
 * The builder.php configuration file
 */
class Builder extends File
{

	//-------------------------------------------------------------------------------------- $classes
	/**
	 * @var Built[]|string[] Built classes, or comments if trim begins with '/', or empty lines ''
	 */
	public $classes;

	//----------------------------------------------------------------------------- readConfiguration
	/**
	 * @param $lines string[]
	 * @return static
	 */
	protected function readConfiguration(array &$lines)
	{
		$built              = null;
		$built_on_next_line = false;
		$class_name         = null;
		for (($line = current($lines)), ($ended = false); !$ended; $line = next($lines)) {
			if ($this->isEndLine($line)) {
				$ended = true;
			}
			else {
				// add assembled built class components
				if ($built_on_next_line && strpos($line, '=>')) {
					$built_on_next_line = false;
					$line               = TAB . $class_name . $line;
				}
				if (beginsWith($line, TAB . TAB)) {
					if ($built instanceof Assembled) {
						if (beginsWith(trim($line), ['//', '/*']) || !trim($line)) {
							$built->components[] = $line;
						}
						else {
							foreach (explode(',', lParse($line, ']')) as $class_name) {
								if (trim($class_name)) {
									$built->components[] = $this->fullClassNameOf($class_name);
								}
							}
						}
					}
					else {
						trigger_error(
							'Only ' . Assembled::class . ' can accept component interface / traits,'
							. ' bad built object ' . get_class($built) . ' into file ' . $this->file_name
							. ' line ' . (key($lines) + 1),
							E_USER_ERROR
						);
					}
				}
				// add built class
				elseif (beginsWith($line, TAB)) {
					if (beginsWith(trim($line), ['//', '/*']) || !trim($line)) {
						$this->classes[] = $line;
					}
					elseif (in_array(trim($line), [']', '],'])) {
						$built = null;
					}
					else {
						$class_name = $this->fullClassNameOf(lParse($line, '=>'));
						// Class_Name::class =>
						if (strpos($line, '=>')) {
							// Class_Name::class => [
							if (strpos($line, '[')) {
								$built = new Assembled($class_name);
								// Class_Name::class => [ Class_Name::class, ...
								// Class_Name::class => [ Class_Name::class, ... ]
								foreach (explode(',', mParse($line, '[', ']')) as $class_name) {
									if (trim($class_name)) {
										$built->components[] = $this->fullClassNameOf($class_name);
									}
								}
							} // Class_Name::class => Replacement::class
							else {
								$built = new Replaced($class_name, $this->fullClassNameOf(rParse($line, '=>')));
							}
							$this->classes[] = $built;
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
					$this->classes[] = $line;
				}
			}
		}
		return $this;
	}

}
