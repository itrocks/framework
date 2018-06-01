<?php
namespace ITRocks\Framework\Assets;

use DOMDocument;
use DOMElement;
use ITRocks\Framework\Tools\Paths;

/**
 * Class Asset_Element
 */
class Element
{

	//----------------------------------------------------------------------------------------- REGEX
	const REGEX = '(//link | //script | //comment())';

	//-------------------------------------------------------------------------------------- $element
	/**
	 * @var DOMElement
	 */
	public $element;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * @getter
	 * @setter
	 * @var string
	 */
	public $path;

	//------------------------------------------------------------------------------- $path_attribute
	/**
	 * Attribute name of path location (used for path getter/setter)
	 *
	 * @var string
	 */
	private $path_attribute;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Element constructor.
	 *
	 * @param $element DOMElement
	 * @param $path    string
	 */
	public function __construct($element, $path)
	{
		$this->element = $element;
		switch ($this->element->nodeName) {
			case 'script':
				$this->path_attribute = 'src';
				break;
			case 'link' :
				$this->path_attribute = 'href';
				break;
		}

		$this->path = realpath($path . SL . $this->path);
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		$doc   = new DOMDocument();
		$clone = $this->element->cloneNode(true);
		$doc->appendChild($doc->importNode($clone, true));
		return $doc->saveHTML();
	}

	//--------------------------------------------------------------------------------------- getPath
	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->element->getAttribute($this->path_attribute);
	}

	//--------------------------------------------------------------------------------------- setPath
	/**
	 * @param $path string
	 */
	public function setPath($path)
	{
		$this->element->setAttribute($this->path_attribute, $path);
	}

	//-------------------------------------------------------------------------------- toRelativePath
	/**
	 * Relative path for a usage in cache/compiled
	 *
	 * @see Template_Compiled::getCompiledPath
	 */
	public function toRelativePath()
	{
		$this->path = DD . SL . DD . SL . Paths::getRelativeFileName($this->path);
	}

}
