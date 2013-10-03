<?php
namespace SAF\Framework;

/*
use PHPExcel;
use PHPExcel_IOFactory;

require dirname(__FILE__) . "/../../vendor/PHPExcel/Classes/PHPExcel.php";
*/

/**
 * Excel file
 *
 * Needs PHPOffice/PHPExcel vendor project to be installed into your framework/vendor/ folder,
 * following this install procedure (Debian/Ubuntu example) :
 * apt-get install gnumeric
 * apt-get install php_zip
 * apt-get install php_xml
 * apt-get install php_gd2
 * cd framework/vendor
 * git pull https://github.com/PHPOffice/PHPExcel.git
 */
class Spreadsheet_File // extends PHPExcel
{

	//----------------------------------------------------------------------------------- fileToArray
	/**
	 * This is a direct, fast and optimized feature to read an excel file and return it's workseets
	 * into a simple PHP array, as fastest as possible, using gnumeric.
	 *
	 * This enable you to import huge xls files of 10MB and more
	 *
	 * @param $file_name
	 * @return array three dimensions (worksheet, row, column) array of read data
	 */
	public static function fileToArray($file_name)
	{
		$csv_file = Application::current()->getTemporaryFilesPath() . "/" . uniqid() . ".csv";
		//echo "ssconvert \"$file_name\" \"$csv_file\" -S<br>";
		exec("ssconvert \"$file_name\" \"$csv_file\" -S");
		$count = 0;
		$result = array();
		while (is_file($csv_file . "." . $count)) {
			$result[$csv_file . "." . $count] = array_map("str_getcsv", file($csv_file . "." . $count));
			$count ++;
		}
		return $result;
	}

	//-------------------------------------------------------------------------------- createFromFile
	/**
	 * @param $file_name string The Excel file name to be read
	 * @return Spreadsheet_File
	 */
	public static function createFromFile($file_name)
	{
		$source_object = PHPExcel_IOFactory::load($file_name);
		$destination_object = new Spreadsheet_File();
		$source_class = Reflection_Class::getInstanceOf(get_class($source_object));
		$destination_class = Reflection_Class::getInstanceOf(__CLASS__);
		$destination_properties = $destination_class->accessProperties();
		foreach ($source_class->accessProperties() as $source_property) {
			if (!$source_property->isStatic()) {
				$destination_property = $destination_properties[$source_property->name];
				$destination_property->setValue(
					$destination_object,
					$source_property->getValue($source_object)
				);
			}
		}
		$source_class->accessPropertiesDone();
		$destination_class->accessPropertiesDone();
		return $destination_object;
	}

}
