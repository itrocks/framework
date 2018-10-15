<?php
namespace ITRocks\Framework\View\Html\Dom\Svg;

use phpseclib\File\ASN1\Element;

/**
 * A DOM SVG group (g) element
 */
class Group extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Group constructor
	 */
	public function __construct()
	{
		parent::__construct('g');
	}

}
