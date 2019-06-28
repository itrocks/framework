<?php
namespace ITRocks\Framework\Dao\File;

use finfo;

/**
 * File type
 */
class Type
{

	//-------------------------------------------------------------------------------- is() constants
	const APPLICATION = 'application';
	const IMAGE       = 'image';

	//----------------------------------------------------------------------------- $extensions_types
	/**
	 * @var string[] key is the file extension, value is the full text file type
	 */
	private static $extensions_types;

	//-------------------------------------------------------------------------------------- $subtype
	/**
	 * @var string
	 */
	private $subtype;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @var string
	 */
	private $type;

	//----------------------------------------------------------------------------- $types_extensions
	/**
	 * @var string[] key is the full text file type, value is the file extension
	 */
	private static $types_extensions;

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

	//--------------------------------------------------------------------------- contentToTypeString
	/**
	 * Try to compute and return the mime-type of the content
	 *
	 * @param $content string
	 * @return string
	 * @todo NORMAL check that finfo mime-type list is compatible with self::$extensions_types
	 */
	public static function contentToTypeString($content)
	{
		return (new finfo(FILEINFO_MIME_TYPE))->buffer($content) ?: null;
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
			// BEWARE : for same values, the last key will give the extension got from type
			self::$extensions_types = [
				'bmp'  => 'image/bmp',
				'bz2'  => 'application/x-bz2',
				'csv'  => 'text/csv',
				'css'  => 'text/css',
				'doc'  => 'application/msword',
				'gif'  => 'image/gif',
				'gzip' => 'multipart/x-gzip',
				'gz'   => 'multipart/x-gzip', // default extension. So last ordered for same value
				'html' => 'text/html',
				'jpe'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpg'  => 'image/jpeg',       // default extension. So last ordered for same value
				'js'   => 'text/javascript',
				'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
				'pdf'  => 'application/pdf',
				'png'  => 'image/png',
				'svg'  => 'image/svg+xml',
				'tif'  => 'image/tiff',
				'tiff' => 'image/tiff',       // default extension. So last ordered for same value
				'txt'  => 'text/plain',
				'xls'  => 'application/vnd.ms-excel',
				'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'zip'  => 'multipart/x-zip'
			];

			self::$types_extensions = array_flip(self::$extensions_types);
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

	//--------------------------------------------------------------------- typeStringToFileExtension
	/**
	 * @param $type string
	 * @return string
	 */
	public static function typeStringToFileExtension($type)
	{
		$type = strtolower($type);
		self::initExtensionsTypes();
		return isset(self::$types_extensions[$type])
			? self::$types_extensions[$type]
			: null;
	}

}
