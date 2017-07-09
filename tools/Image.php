<?php
namespace ITRocks\Framework\Tools;
use ITRocks\Framework\Dao\File;

/**
 * Image tools class
 */
class Image
{

	//--------------------------------------------------------------------------------------- $height
	/**
	 * @var integer
	 */
	public $height;

	//------------------------------------------------------------------------------------- $resource
	/**
	 * @var resource
	 */
	public $resource;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * The image type : one of the IMAGETYPE_XXX constants
	 *
	 * @var integer
	 */
	public $type;

	//---------------------------------------------------------------------------------------- $width
	/**
	 * @var integer
	 */
	public $width;

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
	 * @param $width    integer the image width (mandatory)
	 * @param $height   integer the image height (mandatory)
	 * @param $resource resource the image resource. If not set, an empty image will be created
	 * @param $type     integer one of the IMAGETYPE_XXX constants, for automatic save
	 */
	public function __construct($width = null, $height = null, $resource = null, $type = null)
	{
		if (isset($height)) $this->height = $height;
		if (isset($type))   $this->type   = $type;
		if (isset($width))  $this->width  = $width;
		$this->resource = isset($resource)
			? $resource
			: imagecreatetruecolor($this->width, $this->height);
	}

	//---------------------------------------------------------------------------------------- asFile
	/**
	 * @param $file_name string
	 * @return File
	 */
	public function asFile($file_name = null)
	{
		if (!isset($file_name)) {
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
	protected function createBackgroundColor()
	{
		return in_array($this->type, [ IMAGETYPE_GIF, IMAGETYPE_PNG ])
			? imagecolorallocatealpha($this->resource, 0, 0, 0, 127)
			: imagecolorallocate($this->resource, 255, 255, 255);
	}

	//-------------------------------------------------------------------------------- createFromFile
	/**
	 * @param $filename string
	 * @return Image
	 */
	public static function createFromFile($filename)
	{
		return self::createFromString(file_get_contents($filename));
	}

	//------------------------------------------------------------------------------ createFromString
	/**
	 * @param $image string
	 * @return Image
	 */
	public static function createFromString($image)
	{
		$size = getimagesizefromstring($image);
		return new Image($size[0], $size[1], imagecreatefromstring($image), $size[2]);
	}

	//--------------------------------------------------------------------------------------- display
	/**
	 * Display an image
	 */
	public function display()
	{
		$this->save(null);
	}

	//--------------------------------------------------------------------------------- fileExtension
	/**
	 * @return string
	 */
	public function fileExtension()
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
	 * @param $color integer If not set, a transparent / white color is created to fill the image
	 */
	public function fillImage($color = null)
	{
		if (!isset($color)) {
			$color = $this->createBackgroundColor();
		}
		imagefilledrectangle($this->resource, 0, 0, $this->width - 1, $this->height - 1, $color);
	}

	//---------------------------------------------------------------------------- newImageKeepsAlpha
	/**
	 * @param $width  integer
	 * @param $height integer
	 * @return Image
	 */
	protected function newImageKeepsAlpha($width = null, $height = null)
	{
		if (!$height) $height = $this->height;
		if (!$width)  $width = $this->width;

		$image = new Image($width, $height, null, $this->type);

		if ($this->type == IMAGETYPE_PNG) {
			imagecolortransparent(
				$image->resource, imagecolorallocatealpha($image->resource, 0, 0, 0, 127)
			);
		}
		if (in_array($this->type, [ IMAGETYPE_GIF, IMAGETYPE_PNG ])) {
			imagealphablending($image->resource, false);
			imagesavealpha($image->resource, true);
		}
		return $image;
	}

	//---------------------------------------------------------------------------------------- resize
	/**
	 * Gets a resized version of the image
	 *
	 * @param $width      integer the width of the new image. null for automatic
	 * @param $height     integer the height of the new image. null for automatic
	 * @param $keep_ratio boolean keep image ratio (margins are added if image ratio changes)
	 * @return Image
	 */
	public function resize($width = null, $height = null, $keep_ratio = true)
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
				$dy = ceil(($height - $dh) / 2);
			}
			// destination is wider than source : left and right margins
			elseif ($destination_ratio > $source_ratio) {
				$dw = round($source_ratio * $height);
				$dx = ceil(($width - $dw) / 2);
			}
		}
		$destination = $this->newImageKeepsAlpha($width, $height);
		$this->fillImage();
		imagecopyresampled(
			$destination->resource, $this->resource, $dx, $dy, 0, 0, $dw, $dh, $this->width, $this->height
		);
		return $destination;
	}

	//---------------------------------------------------------------------------------------- rotate
	/**
	 * Gets a rotated version of the image
	 *
	 * @param $angle float Rotation angle in degrees
	 * @return Image
	 */
	public function rotate($angle)
	{
		while ($angle > 359.99) $angle -= 360;
		while ($angle < 0)      $angle += 360;
		if (!$angle) {
			$destination = clone $this;
		}
		elseif ($angle == 180) {
			$destination           = $this->newImageKeepsAlpha();
			$destination->resource = imagerotate($this->resource, $angle, 0);
		}
		elseif (in_array($angle, [90, 270])) {
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
	 * @param $filename string if null, the image is displayed instead of being saved
	 * @param $type     integer Image type is one of the IMAGETYPE_XXX image types, or current if null
	 * @param $quality  integer Image quality (percent)
	 * @return Image
	 */
	public function save($filename, $type = null, $quality = null)
	{
		if (!isset($type))    $type = $this->type;
		if (!isset($quality)) $quality = 80;

		switch ($type) {
			case IMAGETYPE_BMP:
				image2wbmp($this->resource, $filename);
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
	 * @param $width          integer the thumbnail image file width. null for automatic
	 * @param $height         integer the thumbnail image file height. null for automatic
	 * @param $type           integer IMAGETYPE_XXX image type constant
	 * @param $quality        integer
	 * @return Image
	 */
	public static function stringToThumbnailFile(
		$image, $thumbnail_file, $width = null, $height = null, $type = null, $quality = null
	) {
		return self::createFromString($image)->resize(
			$width, $height)->save($thumbnail_file, $type, $quality
		);
	}

}
