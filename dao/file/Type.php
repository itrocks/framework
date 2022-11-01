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
	private static array $extensions_types;

	//-------------------------------------------------------------------------------------- $subtype
	/**
	 * @var string
	 */
	private string $subtype;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @var string
	 */
	private string $type;

	//----------------------------------------------------------------------------- $types_extensions
	/**
	 * @var string[] key is the full text file type, value is the file extension
	 */
	private static array $types_extensions;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $type string         The main type string, or 'type/subtype' if $subtype is null
	 * @param $subtype string|null If set, the subtype string ($type must be the main type alone)
	 */
	public function __construct(string $type, string $subtype = null)
	{
		if (isset($subtype)) {
			$this->type    = $type;
			$this->subtype = $subtype;
		}
		else {
			[$this->type, $this->subtype] = explode(SL, $type);
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->type . SL . $this->subtype;
	}

	//--------------------------------------------------------------------------- contentToTypeString
	/**
	 * Try to compute and return the mime-type of the content
	 *
	 * @param $content string
	 * @return ?string
	 * @todo NORMAL check that finfo mime-type list is compatible with self::$extensions_types
	 */
	public static function contentToTypeString(string $content) : ?string
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
	public function equals(Type $file_type) : bool
	{
		return ($file_type->type === $this->type) && ($file_type->subtype === $this->subtype);
	}

	//--------------------------------------------------------------------- fileExtensionToTypeString
	/**
	 * @param $file_extension string
	 * @return ?string
	 */
	public static function fileExtensionToTypeString(string $file_extension) : ?string
	{
		$file_extension = strtolower($file_extension);
		self::initExtensionsTypes();
		return self::$extensions_types[$file_extension] ?? null;
	}

	//-------------------------------------------------------------------------- fileNameToTypeString
	public static function fileNameToTypeString(string $file_name) : ?string
	{
		return static::fileExtensionToTypeString(rLastParse($file_name, DOT));
	}

	//--------------------------------------------------------------------------- initExtensionsTypes
	/**
	 * Init the $extensions_types static property (if not already done)
	 */
	private static function initExtensionsTypes()
	{
		if (isset(self::$extensions_types)) {
			return;
		}
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
			'json' => 'application/json',
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

	//-------------------------------------------------------------------------------------------- is
	/**
	 * Returns true if the argument file type is an equivalent of this file type
	 *
	 * @param $type string|Type if string, can be either a file extension an extension type
	 *              ('type' alone, complete 'type/subtype', or 'subtype' alone)
	 *              Special types 'x' for compressed and 'vnd' for vendor types are accepted too
	 * @return boolean
	 */
	public function is(string|Type $type) : bool
	{
		if ($type instanceof Type) {
			return $this->equals($type);
		}
		if (str_contains(SL, $type)) {
			return ($type === strval($this));
		}
		self::initExtensionsTypes();
		if (isset(self::$extensions_types[$type])) {
			return (self::$extensions_types[$type] === strval($this));
		}
		return ($this->type === $type) || ($this->subtype === $type)
			|| (($type === 'x') && str_starts_with($this->subtype, 'x-'))
			|| (($type === 'vnd') && str_starts_with($this->subtype, 'vnd.'));
	}

	//--------------------------------------------------------------------- typeStringToFileExtension
	/**
	 * @param $type string
	 * @return ?string
	 */
	public static function typeStringToFileExtension(string $type) : ?string
	{
		$type = strtolower($type);
		self::initExtensionsTypes();
		return self::$types_extensions[$type] ?? null;
	}

}
