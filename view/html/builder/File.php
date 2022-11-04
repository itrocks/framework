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
	protected Dao\File $file;

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var ?Reflection_Property
	 */
	protected ?Reflection_Property $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $file     Dao\File
	 * @param $property Reflection_Property|null
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
	public function build() : string
	{
		return $this->buildFileAnchor($this->file);
	}

	//------------------------------------------------------------------------------- buildFileAnchor
	/**
	 * @param $file Dao\File
	 * @return Anchor
	 */
	protected function buildFileAnchor(Dao\File $file) : Anchor
	{
		$image = $file->getType()->is('image')
			? new Image($file->link(Feature::F_OUTPUT, 22))
			: '';
		if ($image) {
			$image->setAttribute('height', 22);
		}
		$feature = $image ? 'image' : Feature::F_OUTPUT;

		$file_name = $file->name ?: '';
		if (str_contains($file_name, '|')) {
			$file_name = str_replace('|', '&#124;', $file_name);
		}
		$anchor = new Anchor($file->link($feature), $image . new Span($file_name));

		if ($image) {
			$anchor->setAttribute('target', Target::BLANK);
		}

		return $anchor;
	}

	//------------------------------------------------------------------------------------ buildImage
	/**
	 * Builds a file image HTML element
	 *
	 * @param $width  integer|null
	 * @param $height integer|null
	 * @return Image
	 */
	public function buildImage(int $width = null, int $height = null) : Image
	{
		$file       = clone $this->file;
		$file->name = uniqid() . '.' . rLastParse($this->file->name, DOT);
		/** @var $session_files Files */
		$session_files          = Session::current()->get(Files::class, true);
		$session_files->files[] = $file;
		$image_arguments        = [];
		$image_parameters       = [$file->name];
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
		return new Image(View::link(
			Session_File::class, Feature::F_OUTPUT, $image_parameters, $image_arguments
		));
	}

}
