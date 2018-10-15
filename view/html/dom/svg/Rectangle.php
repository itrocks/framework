<?php
namespace ITRocks\Framework\View\Html\Dom\Svg;

use ITRocks\Framework\View\Html\Dom\Element;

/**
 * A DOM SVG rectangle (rect) element
 */
class Rectangle extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $x      float
	 * @param $y      float
	 * @param $width  float
	 * @param $height float
	 */
	public function __construct($x = null, $y = null, $width = null, $height = null)
	{
		parent::__construct('rect');

		if (isset($x))      $this->setAttribute('x',      $x);
		if (isset($y))      $this->setAttribute('y',      $y);
		if (isset($width))  $this->setAttribute('width',  $width);
		if (isset($height)) $this->setAttribute('height', $height);
	}

}
