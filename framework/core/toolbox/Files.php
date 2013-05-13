<?php
namespace SAF\Framework;

/**
 * Class Files : methods for folders and files
 */
class Files
{

	//----------------------------------------------------------------------------------- addSlashEnd
	/**
	 * Add a slash in end of the string if there is no
	 * @param string $string The string to analyse
	 * @return string return the string with a slash if there is no but it's the same string if there
	 * are already a slash
	 */
	public static function addSlashEnd($string)
	{
		if ($string[strlen($string) - 1] != "/") {
			$string .= "/";
		}
		return $string;
	}

	//--------------------------------------------------------------------------------- copyDirectory
	/**
	 * Copy recursively a folder to the destination
	 * @param string $source Address of folder to copy
	 * @param string $destination Address of destination
	 * @param array $ignore List of ignore files/folders
	 * @return boolean True if copy is correct, else false
	 */
	public static function copyDirectory($source, $destination, $ignore = array())
	{
		$ret = false;
		$source = self::addSlashEnd($source);
		$destination = self::addSlashEnd($destination);
		if (is_dir($source)) {
			$list_files = scandir($source);
			foreach ($list_files as $entry) {
				$is_ignore = false;
				if (!empty($ignore)) {
					if (in_array($entry, $ignore)) {
						$is_ignore = true;
					}
				}
				if (!$is_ignore) {
					if (is_dir($source.$entry) && !($entry == "." || $entry == "..")) {
						mkdir($destination.$entry);
						$ret = self::copyDirectory($source.$entry, $destination.$entry, $ignore);
					}
					else if (is_file($source.$entry)) {
						$ret = copy($source.$entry, $destination.$entry);
					}
				}
			}
		}

		return $ret;
	}

	//---------------------------------------------------------------------------------- deleteFolder
	/**
	 * Delete a folder recursively.
	 * @param string $path address of folder to delete
	 * @return boolean true if folder is correctly deleted, false else
	 */
	public static function deleteFolder($path)
	{
		$ret = false;
		$path = self::addSlashEnd($path);
		if (is_dir($path)) {
			$list_files = scandir($path);
			foreach ($list_files as $entry) {
				if (is_dir($path.$entry) && !($entry == "." || $entry == "..")) {
					$ret = self::deleteFolder($path.$entry);
				}
				else if (is_file($path.$entry)) {
					$ret = unlink($path.$entry);
				}
			}
			rmdir($path);
		}

		return $ret;
	}

	//------------------------------------------------------------------------------------- setFolder
	/**
	 * Test if a folder exist and if not exist, it is create
	 * @param string $folder address of folder to test
	 * @return bool True if a folder was created, false if folder exist already or are not created
	 */
	public static function setFolder($folder)
	{
		$ret = false;
		if (!is_dir($folder)) {
			$ret = mkdir($folder);
		}
		return $ret;
	}
}
