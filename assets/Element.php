<?php
namespace ITRocks\Framework\Assets;

use DOMDocument;
use DOMElement;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Reflection\Attribute\Property\Setter;
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
	#[Getter('getPath'), Setter('setPath')]
	public string $path = '';

	//------------------------------------------------------------------------------- $path_attribute
	/**
	 * Attribute name of path location (used for path getter/setter)
	 *
	 * @example 'src'
	 * @see Element::__construct
	 */
	private string $path_attribute;

	//----------------------------------------------------------------------------------- __construct
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
	public function __toString() : string
	{
		$document = new DOMDocument();
		$clone    = $this->element->cloneNode(true);
		$document->appendChild($document->importNode($clone, true));
		return $document->saveHTML();
	}

	//--------------------------------------------------------------------------------------- getPath
	/**
	 * @noinspection PhpUnused #Getter
	 */
	public function getPath() : string
	{
		return $this->element->getAttribute($this->path_attribute);
	}

	//--------------------------------------------------------------------------------------- setPath
	/**
	 * @noinspection PhpUnused #Setter
	 */
	public function setPath(string $path) : void
	{
		$this->element->setAttribute($this->path_attribute, $path);
	}

	//-------------------------------------------------------------------------------- toRelativePath
	/**
	 * Relative path for a usage in cache/compiled
	 *
	 * @see Template_Compiler::getCompiledPath
	 */
	public function toRelativePath() : void
	{
		$this->path = DD . SL . DD . SL . Paths::getRelativeFileName($this->path);
	}

}
