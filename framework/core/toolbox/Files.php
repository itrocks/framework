<?php
namespace SAF\Framework;

/**
 * Utility methods for directories and files
 */
abstract class Files
{

	//----------------------------------------------------------------------------------- appendSlash
	/**
	 * Appends a slash to the string if there is none
	 *
	 * If the string is empty, it will keep empty and no slash will be appended.
	 *
	 * @param $string string The string to analyse
	 * @return string return the string with a slash if there is no but it's the same string if there
	 * are already a slash
	 */
	public static function appendSlash($string)
	{
		if ((!empty($string)) && ($string[strlen($string) - 1] != "/")) {
			$string .= "/";
		}
		return $string;
	}

	//------------------------------------------------------------------------------------------ copy
	/**
	 * Copies a file or a directory
	 *
	 * @param $source      string source file or directory path
	 * @param $destination string destination file or directory path
	 * @param $ignore      string[] List of files/directories to ignore
	 * @return boolean true if copy succeeds, else false
	 */
	public static function copy($source, $destination, $ignore = array())
	{
		if (is_dir($source)) {
			$result = true;
			$source = self::appendSlash($source);
			$destination = self::appendSlash($destination);
			foreach (scandir($source) as $entry) {
				if (!in_array($entry, $ignore)) {
					if (is_dir($source . $entry) && ($entry != ".") && ($entry != "..")) {
						mkdir($destination . $entry);
						$result = self::copy($source . $entry, $destination . $entry, $ignore) && $result;
					}
					elseif (is_file($source . $entry)) {
						$result = copy($source . $entry, $destination . $entry) && $result;
					}
				}
			}
		}
		elseif (is_file($source)) {
			$result = copy($source, $destination);
		}
		else {
			$result = false;
		}
		return $result;
	}

	//---------------------------------------------------------------------------------------- delete
	/**
	 * Delete a file or a directory, recursively
	 *
	 * @param $path string path of file or directory to delete
	 * @return boolean true if the directory is correctly deleted, else false
	 */
	public static function delete($path)
	{
		$path = self::appendSlash($path);
		if (is_dir($path)) {
			$result = true;
			$list_files = scandir($path);
			foreach ($list_files as $entry) {
				if (is_dir($path . $entry) && ($entry != ".") && ($entry != "..")) {
					$result = self::delete($path . $entry) && $result;
				}
				elseif (is_file($path . $entry)) {
					$result = unlink($path . $entry) && $result;
				}
			}
			$result = rmdir($path);
		}
		elseif (is_file($path)) {
			$result = unlink($path);
		}
		else {
			$result = false;
		}
		return $result;
	}

	//----------------------------------------------------------------------------------------- mkdir
	/**
	 * Creates a directory if it does not exist, recursively
	 *
	 * @param $directory string path of the directory to be created
	 * @return boolean true if a folder was created or existed, false if any error occurred
	 */
	public static function mkdir($directory)
	{
		return mkdir($directory, 0777, true);
	}

	//----------------------------------------------------------------------------------------- rmdir
	/**
	 * Removes a directory if it exists, and recursively delete files
	 *
	 * @param $directory string path of the directory to be deleted
	 * @return boolean true if a folder was created or existed, false if any error occurred
	 */
	public static function rmdir($directory)
	{
		if (!empty($directory) && is_dir($directory)) {
			foreach (array_diff(scandir($directory), array(".", "..")) as $entry) {
				if (is_dir($directory . "/" . $entry)) {
					self::rmdir($directory . "/" . $entry);
				}
				else {
					unlink($directory . "/" . $entry);
				}
			}
			rmdir($directory);
		}
	}

	//------------------------------------------------------------------------------- scanDirForFiles
	/**
	 * Scan directory for files and return all files names
	 *
	 * @param $directory string
	 * @return string[] each string is the path of the file, relative to the directory
	 */
	public static function scanDirForFiles($directory)
	{
		$files = array();
		foreach (array_diff(scandir($directory), array(".", "..")) as $entry) {
			if (is_dir($directory . "/" . $entry)) {
				foreach (self::scanDirForFiles($directory . "/" . $entry) as $file_name) {
					$files[] = $entry . "/" . $file_name;
				}
			}
			else {
				$files[] = $entry;
			}
		}
		return $files;
	}

}
