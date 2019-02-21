<?php
namespace ITRocks\Framework\Tools;

/**
 * Utility methods for directories and files
 */
abstract class Files
{

	//----------------------------------------------------------------------------------- appendSlash
	/**
	 * Appends a slash to the string if there is none
	 *
	 * If the string is empty, it will remain empty and no slash will be appended.
	 *
	 * @param $string string The string to analyse
	 * @return string return the string with a trailing slash if there is none
	 */
	public static function appendSlash($string)
	{
		if ((!empty($string)) && ($string[strlen($string) - 1] != SL)) {
			$string .= SL;
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
	public static function copy($source, $destination, array $ignore = [])
	{
		if (is_dir($source)) {
			$result = true;
			$source = self::appendSlash($source);
			$destination = self::appendSlash($destination);
			foreach (scandir($source) as $entry) {
				if (!in_array($entry, $ignore)) {
					if (is_dir($source . $entry) && ($entry != DOT) && ($entry != DD)) {
						mkdir($destination . $entry);
						$result = self::copy($source . $entry, $destination . $entry, $ignore) && $result;
					}
					else {
						$result = copy($source . $entry, $destination . $entry) && $result;
					}
				}
			}
		}
		else {
			$result = copy($source, $destination);
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
		if (is_dir($path)) {
			$path       = self::appendSlash($path);
			$result     = true;
			$list_files = scandir($path);
			foreach ($list_files as $entry) {
				if (is_dir($path . $entry) && ($entry != DOT) && ($entry != DD)) {
					$result = self::delete($path . $entry) && $result;
				}
				else {
					$result = unlink($path . $entry) && $result;
				}
			}
			$result = rmdir($path);
		}
		else {
			$result = unlink($path);
		}
		return $result;
	}

	//-------------------------------------------------------------------------------- downloadOutput
	/**
	 * Consider current output as a file download
	 *
	 * @param $name string the file name
	 * @param $type string the mime type of the file (ie 'application/xml')
	 * @param $size integer the file size, if known
	 */
	public static function downloadOutput($name, $type, $size = null)
	{
		header('Content-Disposition: attachment; filename=' . DQ . $name . DQ);
		header('Content-Type: ' . $type);
		if (isset($size)) {
			header('Content-Length: ' . $size);
		}
		header('Content-Transfer-Encoding: binary');
	}

	//-------------------------------------------------------------------------------------- isInPath
	/**
	 * Returns true is file is contained in path
	 *
	 * @param $file_name string
	 * @param $path      string
	 * @return boolean
	 */
	public static function isInPath($file_name, $path)
	{
		return ($file_name === $path) || (substr($file_name, 0, strlen($path) + 1) === ($path . SL));
	}

	//----------------------------------------------------------------------------------------- mkdir
	/**
	 * Creates a directory if it does not exist, recursively
	 *
	 * returns true if no error or if directory already exists before call
	 *
	 * @param $directory string path of the directory to be created
	 * @param $mode      integer chmod the created directory : default is the largest possible
	 * @return boolean true if a folder was created or existed, false if any error occurred
	 */
	public static function mkdir($directory, $mode = 0777)
	{
		if (is_dir($directory)) {
			$result = true;
		}
		else {
			$result = mkdir($directory, $mode, true);
			// this patch is for php versions where mkdir does not change the mode
			if ($result && ($mode !== 0777)) {
				chmod($directory, $mode);
			}
		}
		return $result;
	}

	//----------------------------------------------------------------------------------------- rmdir
	/**
	 * Removes a directory if it exists, and recursively delete files
	 *
	 * @param $directory string path of the directory to be deleted
	 * @return boolean true if a folder was removed or was not existing, false if any error occurred
	 */
	public static function rmdir($directory)
	{
		if (!empty($directory) && is_dir($directory)) {
			foreach (array_diff(scandir($directory), [DOT, DD]) as $entry) {
				if (is_dir($directory . SL . $entry)) {
					self::rmdir($directory . SL . $entry);
				}
				else {
					unlink($directory . SL . $entry);
				}
			}
			return rmdir($directory);
		}
		return true;
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
		$files = [];
		foreach (array_diff(scandir($directory), [DOT, DD]) as $entry) {
			if (is_dir($directory . SL . $entry)) {
				foreach (self::scanDirForFiles($directory . SL . $entry) as $file_name) {
					$files[] = $entry . SL . $file_name;
				}
			}
			else {
				$files[] = $entry;
			}
		}
		return $files;
	}

}
