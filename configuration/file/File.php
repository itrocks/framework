<?php
namespace ITRocks\Framework\Configuration;

use ITRocks\Framework\Application;
use ITRocks\Framework\Configuration\File\Has_File_Name;
use ITRocks\Framework\Controller\Getter;
use ITRocks\Framework\Reflection\Type;

/**
 * Configuration file
 */
abstract class File
{
	use Has_File_Name;

	//---------------------------------------------------------------------------------- $begin_lines
	/**
	 * free code lines before the parsed configuration started
	 *
	 * @var string[]
	 */
	public array $begin_lines = [];

	//------------------------------------------------------------------------------------ $end_lines
	/**
	 * free code lines after the parsed configuration ended
	 *
	 * @var string[]
	 */
	public array $end_lines = [];

	//------------------------------------------------------------------------------------ $namespace
	/**
	 * @var ?string
	 */
	public ?string $namespace = null;

	//------------------------------------------------------------------------------------------ $use
	/**
	 * @var string[]
	 */
	public array $use = [];

	//------------------------------------------------------------------------------------- addUseFor
	/**
	 * Adds a use entry for this class name, if it can be
	 *
	 * @param $class_name string
	 * @param $force integer
	 */
	public function addUseFor(string $class_name, int $force = 0)
	{
		if ($force) {
			$use = lParse($class_name, BS, $force);
		}
		else {
			$class_name_without_vendor_project = Getter::classNameWithoutVendorProject($class_name);
			$use = lParse($class_name, BS, substr_count($class_name_without_vendor_project, BS) + 1);
		}
		$this->addUseForClassName($use);
	}

	//---------------------------------------------------------------------------- addUseForClassName
	/**
	 * Called by addUseFor when the starting used class name has been calculated
	 *
	 * @param $use string
	 */
	protected function addUseForClassName(string $use)
	{
		while (str_contains($use, BS) && !in_array($use, $this->use) && $this->useConflict($use)) {
			$use = lParse($use, BS);
		}
		if (!in_array($use, $this->use) && !$this->useConflict($use)) {
			$this->use = arrayInsertSorted($this->use, $use);
		}
	}

	//------------------------------------------------------------------------------- defaultFileName
	/**
	 * Calculates the name of the default configuration file matching the current configuration class
	 *
	 * The $short_file_name is usually useless : calculated using the name of the current class
	 *
	 * @param $short_file_name string ie 'builder', 'config', 'menu'
	 * @return string
	 */
	public static function defaultFileName(string $short_file_name = '') : string
	{
		if (!$short_file_name) {
			$short_file_name = strtolower(rLastParse(static::class, BS));
		}
		return str_replace(BS, SL, strtolower(Application::current()->getNamespace()))
			. SL . $short_file_name . '.php';
	}

	//------------------------------------------------------------------------------- fullClassNameOf
	/**
	 * Change a short class name, with maybe spaces and trailing '::class', into a clean full class
	 * name with full namespace
	 *
	 * It uses $namespace and $use for namespace completion
	 *
	 * @param $class_name string source short class name to clean-up and extend
	 * @return string resulting full and clean class name
	 */
	public function fullClassNameOf(string $class_name) : string
	{
		$class_name = lParse(trim($class_name), '::class');
		return (new Type($class_name))->applyNamespace($this->namespace, $this->use);
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read from file
	 */
	abstract public function read();

	//------------------------------------------------------------------------------ shortClassNameOf
	/**
	 * Simplify the name of the class using its longest reference into use,
	 * or its start from the current namespace
	 *
	 * @param $class_name        string
	 * @param $maximum_use_depth integer do not care about use greater than this backslashes counter
	 * @return string
	 */
	public function shortClassNameOf(string $class_name, int $maximum_use_depth = 999) : string
	{
		if (str_starts_with($class_name, AT)) {
			return $class_name;
		}
		$final_class_name = null;
		$used             = '';
		foreach ($this->use as $use) {
			if (
				str_starts_with($class_name, $use)
				&& (strlen($use) > strlen($used))
				&& (substr_count($use, BS) < $maximum_use_depth)
			) {
				$final_class_name = rParse($class_name, BS, substr_count($use, BS));
				$used             = $use;
			}
		}
		if (
			str_starts_with($class_name, $this->namespace)
			&& (strlen($this->namespace) > strlen($used))
		) {
			$final_class_name = substr($class_name, strlen($this->namespace) + 1);
		}
		return $final_class_name ?: (BS . $class_name);
	}

	//----------------------------------------------------------------------------------- useConflict
	/**
	 * @param $use string
	 * @return boolean true if there is a conflict with another use part
	 */
	protected function useConflict(string $use) : bool
	{
		$use_part = rLastParse($use, BS, 1, true);
		foreach ($this->use as $check_use) {
			$check_use_part = rLastParse($check_use, BS, 1, true);
			if ($check_use_part === $use_part) {
				return true;
			}
		}
		return false;
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * Write to file
	 */
	abstract public function write();

}
