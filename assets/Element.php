<?php
namespace ITRocks\Framework\Assets;

use DOMDocument;
use DOMElement;
use ITRocks\Framework\Tools\Paths;

/**
 * Assets element
 */
class Element
{

	//----------------------------------------------------------------------------------------- REGEX
	const REGEX = '(//link | //script | //comment())';

	//-------------------------------------------------------------------------------------- $element
	/**
	 * @var DOMElement
	 */
	public DOMElement $element;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * @getter
	 * @setter
	 * @var string
	 */
	public string $path = '';

	//------------------------------------------------------------------------------- $path_attribute
	/**
	 * Attribute name of path location (used for path getter/setter)
	 *
	 * @exemple 'src'
	 * @see Element::__construct
	 * @var string
	 */
	private string $path_attribute;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Element constructor
	 *
	 * @param $element DOMElement
	 * @param $path    string
	 */
	public function __construct(DOMElement $element, string $path)
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
	public function __toString() : string
	{
		$document = new DOMDocument();
		$clone    = $this->element->cloneNode(true);
		$document->appendChild($document->importNode($clone, true));
		return $document->saveHTML();
	}

	//--------------------------------------------------------------------------------------- getPath
	/**
	 * @return string
	 */
	public function getPath() : string
	{
		return $this->element->getAttribute($this->path_attribute);
	}

	//--------------------------------------------------------------------------------------- setPath
	/**
	 * @noinspection PhpUnused @setter
	 * @param $path string
	 */
	public function setPath(string $path)
	{
		$this->element->setAttribute($this->path_attribute, $path);
	}

	//-------------------------------------------------------------------------------- toRelativePath
	/**
	 * Relative path for a usage in cache/compiled
	 *
	 * @see Template_Compiler::getCompiledPath
	 */
	public function toRelativePath()
	{
		$this->path = DD . SL . DD . SL . Paths::getRelativeFileName($this->path);
	}

}
