<?php
namespace ITRocks\Framework\View\Html\Dom;

/**
 * A DOM SVG element
 */
class Svg extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $width  integer|null
	 * @param $height integer|null
	 */
	public function __construct(int $width = null, int $height = null)
	{
		parent::__construct('svg');
		if (isset($height)) {
			$this->setAttribute('height', $height);
		}
		if (isset($width)) {
			$this->setAttribute('width', $width);
		}
	}

}
