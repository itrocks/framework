<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Reflection\Type;

define('IMAGETYPE_EPS', 102);
define('IMAGETYPE_SVG', 101);

/**
 * Image tools class
 */
class Image
{

	//--------------------------------------------------------------------------------------- $height
	/**
	 * @var integer
	 */
	public int $height = 0;

	//------------------------------------------------------------------------------------- $resource
	/**
	 * @var resource
	 */
	public mixed $resource;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * The image type : one of the IMAGETYPE_XXX constants
	 *
	 * @var integer
	 */
	public int $type;

	//---------------------------------------------------------------------------------------- $width
	/**
	 * @var integer
	 */
	public int $width = 0;

	//--------------------------------------------------------------------------------------- __clone
	/**
	 * Ensure that cloning an image clones its resource to : this makes a copy of the image
	 */
	public function __clone()
	{
		$destination = $this->newImageKeepsAlpha();
		imagecopy($destination->resource, $this->resource, 0, 0, 0, 0, $this->width, $this->height);
		$this->resource = $destination->resource;
	}

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs an image
	 *
	 * @param $width    integer|null the image width (mandatory)
	 * @param $height   integer|null the image height (mandatory)
	 * @param $resource resource|null the image resource. If not set, an empty image will be created
	 * @param $type     integer|null one of the IMAGETYPE_XXX constants, for automatic save
	 */
	public function __construct(
		int $width = null, int $height = null, mixed $resource = null, int $type = null
	) {
		if (isset($height)) $this->height = $height;
		if (isset($type))   $this->type   = $type;
		if (isset($width))  $this->width  = $width;
		$this->resource = $resource ?? imagecreatetruecolor($this->width, $this->height);
	}

	//---------------------------------------------------------------------------------------- asFile
	/**
	 * @param $file_name string
	 * @return File
	 */
	public function asFile(string $file_name = '') : File
	{
		if (!$file_name) {
			$file_name = uniqid() . DOT . $this->fileExtension();
		}
		$this->save('/tmp/' . $file_name);
		return new File('/tmp/' . $file_name);
	}

	//------------------------------------------------------------------------- createBackgroundColor
	/**
	 * Creates a default background color : transparent for a GIF / PNG image, white for other types
	 *
	 * @return integer
	 */
	public function createBackgroundColor() : int
	{
		return $this->hasTransparency()
			? imagecolorallocatealpha($this->resource, 0, 0, 0, 127)
			: imagecolorallocate($this->resource, 255, 255, 255);
	}

	//-------------------------------------------------------------------------------- createFromFile
	/**
	 * @param $file File|string
	 * @return static
	 */
	public static function createFromFile(File|string $file) : static
	{
		return static::createFromString(file_get_contents(
			($file instanceof File) ? $file->temporary_file_name : $file
		));
	}

	//------------------------------------------------------------------------------ createFromString
	/**
	 * @param $image string
	 * @return static
	 */
	public static function createFromString(string $image) : static
	{
		if (!str_contains($image, '<svg')) {
			$size = getimagesizefromstring($image);
			return new static($size[0], $size[1], imagecreatefromstring($image), $size[2]);
		}
		$xml = simplexml_load_string($image);
		$attributes = $xml->attributes();
		if (isset($attributes->height) && isset($attributes->width)) {
			$height = intval(strval($attributes->height));
			$width  = intval(strval($attributes->width));
		}
		elseif (
			isset($attributes->viewBox)
			&& ($view_box = explode(SP, preg_replace('/[\s+]/', SP, $attributes->viewBox)))
			&& (count($view_box) >= 4)
		) {
			$height = $view_box[3];
			$width  = $view_box[2];
		}
		else {
			$height = 150;
			$width  = 300;
		}
		return new static($width, $height, null, IMAGETYPE_SVG);
	}

	//--------------------------------------------------------------------------------------- display
	/**
	 * Display an image
	 */
	public function display() : void
	{
		$this->save();
	}

	//--------------------------------------------------------------------------------- fileExtension
	/**
	 * @return string
	 */
	public function fileExtension() : string
	{
		switch ($this->type) {
			case IMAGETYPE_BMP:  return 'bmp';
			case IMAGETYPE_GIF:  return 'gif';
			case IMAGETYPE_ICO:  return 'ico';
			case IMAGETYPE_IFF:  return 'iff';
			case IMAGETYPE_JPEG: return 'jpg';
			case IMAGETYPE_PNG:  return 'png';
			case IMAGETYPE_PSD:  return 'psd';
		}
		return 'unknown';
	}

	//------------------------------------------------------------------------------------- fillImage
	/**
	 * Fills the image with a given color
	 *
	 * @param $color integer|null If not set, a transparent / white color is created to fill the image
	 */
	public function fillImage(int $color = null) : void
	{
		if (!isset($color)) {
			$color = $this->createBackgroundColor();
		}
		imagefilledrectangle($this->resource, 0, 0, $this->width - 1, $this->height - 1, $color);
	}

	//------------------------------------------------------------------------------- hasTransparency
	/**
	 * Returns true if image may contain transparency
	 * Based on image type : it is not guaranteed that transparent color is used into the image,
	 * it just can.
	 *
	 * @return boolean
	 */
	public function hasTransparency() : bool
	{
		return in_array($this->type, [IMAGETYPE_GIF, IMAGETYPE_ICO, IMAGETYPE_PNG, IMAGETYPE_PSD]);
	}

	//---------------------------------------------------------------------------- newImageKeepsAlpha
	/**
	 * @param $width  integer|null
	 * @param $height integer|null
	 * @return static
	 */
	public function newImageKeepsAlpha(int $width = null, int $height = null) : static
	{
		if (!$height) $height = $this->height;
		if (!$width)  $width  = $this->width;

		$image = new static($width, $height, null, $this->type);

		if ($this->hasTransparency()) {
			imagecolortransparent(
				$image->resource, imagecolorallocatealpha($image->resource, 0, 0, 0, 127)
			);
			imagealphablending($image->resource, false);
			imagesavealpha($image->resource, true);
		}
		return $image;
	}

	//----------------------------------------------------------------------------------------- paste
	/**
	 * Paste an image into another, at a given position
	 *
	 * You can optionally crop a part of the source image
	 *
	 * @param $source_image  Image
	 * @param $left          integer
	 * @param $top           integer
	 * @param $source_left   integer
	 * @param $source_top    integer
	 * @param $source_width  integer
	 * @param $source_height integer
	 */
	public function paste(
		Image $source_image, int $left = 0, int $top = 0, int $source_left = 0, int $source_top = 0,
		int $source_width = 0, int $source_height = 0
	) : void
	{
		if (!$source_width) {
			$source_width = $source_image->width - $source_left;
		}
		if (!$source_height) {
			$source_height = $source_image->height - $source_top;
		}
		imagecopy(
			$this->resource, $source_image->resource, $left, $top, $source_left, $source_top,
			$source_width, $source_height
		);
		// copy transparency
		if (
			$this->hasTransparency()
			&& $source_image->hasTransparency()
			&& ($transparency = imagecolortransparent($source_image->resource))
		) {
			for ($y = 0; $y < $source_height; $y++) {
				for ($x = 0; $x < $source_width; $x++) {
					if (imagecolorat($source_image->resource, $x, $y) === $transparency) {
						imagesetpixel($this->resource, $left + $x, $top + $y, 127 << 24);
					}
				}
			}
		}
	}

	//---------------------------------------------------------------------------------------- resize
	/**
	 * Gets a resized version of the image
	 *
	 * @param $width      integer|null the width of the new image. null for automatic
	 * @param $height     integer|null the height of the new image. null for automatic
	 * @param $keep_ratio boolean keep image ratio (margins are added if image ratio changes)
	 * @return static
	 */
	public function resize(int $width = null, int $height = null, bool $keep_ratio = true) : static
	{
		[$dx, $dy, $dw, $dh] = $this->resizeData($width, $height, $keep_ratio);
		$destination = $this->newImageKeepsAlpha($width, $height);
		$destination->fillImage();
		imagecopyresampled(
			$destination->resource, $this->resource, $dx, $dy, 0, 0, $dw, $dh, $this->width, $this->height
		);
		return $destination;
	}

	//------------------------------------------------------------------------------------ resizeData
	/**
	 * Calculate data for resize (without resizing)
	 *
	 * @param $width      integer|null the width of the new image. null for automatic
	 * @param $height     integer|null the height of the new image. null for automatic
	 * @param $keep_ratio boolean keep image ratio (margins are added if image ratio changes)
	 * @return integer[] [$left, $top, $width, $height]
	 */
	public function resizeData(int $width = null, int $height = null, bool $keep_ratio = true) : array
	{
		$source_ratio = $this->width / $this->height;
		if (is_null($width) && is_numeric($height)) {
			$width = round($source_ratio * $height);
		}
		elseif (is_null($height) && is_numeric($width)) {
			$height = round(1 / $source_ratio * $width);
		}
		elseif (is_null($width) && is_null($height)) {
			$width = $height = 140;
		}
		$destination_ratio = $width / $height;
		$dh = $height;
		$dw = $width;
		$dx = $dy = 0;
		if ($keep_ratio) {
			// source is wider than destination : top and bottom margins
			if ($destination_ratio < $source_ratio) {
				$dh = round(1 / $source_ratio * $width);
				$dy = floor(($height - $dh) / 2);
			}
			// destination is wider than source : left and right margins
			elseif ($destination_ratio > $source_ratio) {
				$dw = round($source_ratio * $height);
				$dx = floor(($width - $dw) / 2);
			}
		}
		return [$dx, $dy, $dw, $dh];
	}

	//---------------------------------------------------------------------------------------- rotate
	/**
	 * Gets a rotated version of the image
	 *
	 * @param $angle float Rotation angle in degrees
	 * @return static
	 */
	public function rotate(float $angle) : static
	{
		$angle = $angle % 360.0;
		if (!$angle) {
			$destination = clone $this;
		}
		elseif (Type::floatEqual($angle, 180.0)) {
			$destination           = $this->newImageKeepsAlpha();
			$destination->resource = imagerotate($this->resource, $angle, 0);
		}
		elseif (Type::floatIn($angle, [90.0, 270.0])) {
			$destination           = $this->newImageKeepsAlpha($this->height, $this->width);
			$destination->resource = imagerotate($this->resource, $angle, 0);
		}
		else {
			// TODO should create a bigger image with transparent background to disable pixels lost
			$destination = $this->newImageKeepsAlpha();
			$destination->fillImage();
			$destination->resource = imagerotate($this->resource, $angle, 0);
		}
		return $destination;
	}

	//------------------------------------------------------------------------------------------ save
	/**
	 * @param $filename string|null  If null, the image is displayed instead of being saved
	 * @param $type     integer|null Image type is one of the IMAGETYPE_XXX image types,
	 *                               or current if null
	 * @param $quality  integer|null Image quality (percent)
	 * @return $this
	 */
	public function save(string $filename = null, int $type = null, int $quality = null) : static
	{
		if (!isset($type))    $type    = $this->type;
		if (!isset($quality)) $quality = 80;

		switch ($type) {
			case IMAGETYPE_BMP:
				imagebmp($this->resource, $filename);
				break;
			case IMAGETYPE_GIF:
				imagegif($this->resource, $filename);
				break;
			case IMAGETYPE_PNG:
				imagepng($this->resource, $filename, round((100 - $quality) / 10), PNG_ALL_FILTERS);
				break;
			default:
				imagejpeg($this->resource, $filename, $quality);
				break;
		}
		return $this;
	}

	//------------------------------------------------------------------------- stringToThumbnailFile
	/**
	 * Transforms an image (binary data) into a thumbnail image file
	 *
	 * @param $image          string binary data of the original image
	 * @param $thumbnail_file string the thumbnail image file name
	 * @param $width          integer|null the thumbnail image file width. null for automatic
	 * @param $height         integer|null the thumbnail image file height. null for automatic
	 * @param $type           integer|null IMAGETYPE_XXX image type constant
	 * @param $quality        integer|null
	 * @return static
	 */
	public static function stringToThumbnailFile(
		string $image, string $thumbnail_file, int $width = null, int $height = null, int $type = null,
		int $quality = null
	) : static
	{
		return static::createFromString($image)->resize(
			$width, $height)->save($thumbnail_file, $type, $quality
		);
	}

}
