<?php
namespace SAF\Framework;

/**
 * File type
 */
class File_Type
{

	//----------------------------------------------------------------------------- $extensions_types
	/**
	 * @var string[] key is the file extension, value is the full text file type
	 */
	private static $extensions_types;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @var string
	 */
	private $type;

	//-------------------------------------------------------------------------------------- $subtype
	/**
	 * @var string
	 */
	private $subtype;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $type string    The main type string, or "type/subtype" if $subtype is null
	 * @param $subtype string If set, the subtype string ($type must be the main type alone)
	 */
	public function __construct($type, $subtype = null)
	{
		if (isset($subtype)) {
			$this->type    = $type;
			$this->subtype = $subtype;
		}
		else {
			list($this->type, $this->subtype) = explode("/", $type);
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->type . "/" . $this->subtype;
	}

	//---------------------------------------------------------------------------------------- equals
	/**
	 * Returns true if the two files types are equivalent (same type and subtype)
	 *
	 * @param $file_type File_Type
	 * @return boolean
	 */
	public function equals(File_Type $file_type)
	{
		return ($file_type->type === $this->type) && ($file_type->subtype === $this->subtype);
	}

	//--------------------------------------------------------------------- fileExtensionToTypeString
	/**
	 * @param $file_extension string
	 * @return string
	 */
	public static function fileExtensionToTypeString($file_extension)
	{
		$file_extension = strtolower($file_extension);
		self::initExtensionsTypes();
		return isset(self::$extensions_types[$file_extension])
			? self::$extensions_types[$file_extension]
			: null;
	}

	//--------------------------------------------------------------------------- initExtensionsTypes
	/**
	 * Init the $extensions_types static property (if not already done)
	 */
	private static function initExtensionsTypes()
	{
		if (!isset(self::$extensions_types)) {
			self::$extensions_types = array(
				"csv"  => "text/csv",
				"ods"  => "application/vnd.oasis.opendocument.spreadsheet",
				"xls"  => "application/vnd.ms-excel",
				"xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
			);
		}
	}

}
