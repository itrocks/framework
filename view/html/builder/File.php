<?php
namespace ITRocks\Framework\View\Html\Builder;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\File\Session_File;
use ITRocks\Framework\Dao\File\Session_File\Files;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Session;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Dom\Anchor;
use ITRocks\Framework\View\Html\Dom\Image;
use ITRocks\Framework\View\Html\Dom\Span;

/**
 * Takes a value that stores a file content and builds HTML code using their data
 */
class File
{

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var Dao\File
	 */
	protected $file;

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property|null
	 */
	protected $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param Dao\File            $file
	 * @param Reflection_Property $property
	 */
	public function __construct(Dao\File $file, Reflection_Property $property = null)
	{
		$this->file     = $file;
		$this->property = $property;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return string
	 */
	public function build()
	{
		return $this->buildFileAnchor($this->file);
	}

	//------------------------------------------------------------------------------- buildFileAnchor
	/**
	 * @param $file Dao\File
	 * @return Anchor
	 */
	protected function buildFileAnchor(Dao\File $file)
	{
		/** @var $session_files Files */
		$session_files          = Session::current()->get(Files::class, true);
		$session_files->files[] = $file;
		$image = ($file->getType()->is('image'))
			? new Image(View::link(Session_File::class, Feature::F_OUTPUT, [$file->name, 22]))
			: '';
		$feature = $image ? 'image' : Feature::F_OUTPUT;

		$anchor = new Anchor(
			View::link(Session_File::class, $feature, [$file->name]),
			$image . new Span($file->name)
		);

		if ($image) {
			$anchor->setAttribute('target', Target::BLANK);
		}

		return $anchor;
	}

	//------------------------------------------------------------------------------------ buildImage
	/**
	 * Build a file image HTML element
	 *
	 * @param $width  integer
	 * @param $height integer
	 * @return Image
	 */
	public function buildImage($width = null, $height = null)
	{
		/** @var $session_files Files */
		$session_files          = Session::current()->get(Files::class, true);
		$session_files->files[] = $this->file;
		$image_arguments        = [];
		$image_parameters       = [$this->file->name];
		if ($width && ($height === $width)) {
			$image_parameters[] = $width;
		}
		else {
			if ($width) {
				$image_arguments['width'] = $width;
			}
			if ($height) {
				$image_arguments['height'] = $height;
			}
		}
		$image = new Image(View::link(Session_File::class, Feature::F_OUTPUT, $image_parameters, $image_arguments));
		return $image;
	}

}
