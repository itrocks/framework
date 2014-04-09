<?php
namespace SAF\Framework\Dao\File;

/**
 * File type
 */
class Type
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
	 * @param $type string    The main type string, or 'type/subtype' if $subtype is null
	 * @param $subtype string If set, the subtype string ($type must be the main type alone)
	 */
	public function __construct($type, $subtype = null)
	{
		if (isset($subtype)) {
			$this->type    = $type;
			$this->subtype = $subtype;
		}
		else {
			list($this->type, $this->subtype) = explode(SL, $type);
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->type . SL . $this->subtype;
	}

	//---------------------------------------------------------------------------------------- equals
	/**
	 * Returns true if the two files types are equivalent (same type and subtype)
	 *
	 * @param $file_type Type
	 * @return boolean
	 */
	public function equals(Type $file_type)
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
			// source : http://www.iana.org/assignments/media-types/media-types.xhtml
			self::$extensions_types = [
				'bz2'  => 'application/x-bz2',
				'csv'  => 'text/csv',
				'gif'  => 'image/gif',
				'gz'   => 'multipart/x-gzip',
				'gzip' => 'multipart/x-gzip',
				'jpe'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpg'  => 'image/jpeg',
				'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
				'png'  => 'image/png',
				'tif'  => 'image/tiff',
				'tiff' => 'image/tiff',
				'xls'  => 'application/vnd.ms-excel',
				'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'zip'  => 'multipart/x-zip'
			];
		}
	}

	//-------------------------------------------------------------------------------------------- is
	/**
	 * Returns true if the argument file type is an equivalent of this file type
	 *
	 * @param $type string|Type if string, can be either a file extension an extension type
	 *              ('type' alone, complete 'type/subtype', or 'subtype' alone)
	 *              Special types 'x' for compressed and 'vnd' for vendor types are accepted too
	 * @return boolean
	 */
	public function is($type)
	{
		if ($type instanceof Type) {
			return $this->equals($type);
		}
		elseif (strpos(SL, $type)) {
			return ($type === strval($this));
		}
		else {
			self::initExtensionsTypes();
			if (isset(self::$extensions_types[$type])) {
				return (self::$extensions_types[$type] === strval($this));
			}
			else {
				return ($this->type === $type) || ($this->subtype === $type)
					|| (($type === 'x') && (strpos($this->subtype, 'x-') === 0))
					|| (($type === 'vnd') && (strpos($this->subtype, 'vnd.') === 0));
			}
		}
	}

}
