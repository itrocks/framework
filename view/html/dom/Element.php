<?php
namespace ITRocks\Framework\View\Html\Dom;

use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\View\Html\Dom\List_\Unordered;

/**
 * A DOM element class
 */
abstract class Element
{

	//------------------------------------------------------------------------------- BUILD_MODE_AUTO
	const BUILD_MODE_AUTO = 'auto';

	//-------------------------------------------------------------------------------- BUILD_MODE_RAW
	const BUILD_MODE_RAW = 'raw';

	//--------------------------------------------------------------------------------------- $append
	/** @var Element[] */
	public array $append = [];

	//----------------------------------------------------------------------------------- $attributes
	/**
	 * Available attributes
	 *
	 * @var Attribute[] key is the attribute name
	 */
	private array $attributes = [];

	//----------------------------------------------------------------------------------- $build_mode
	/** In AUTO mode, check content to format as list or table, in RAW strictly build content */
	#[Values(self::class)]
	private string $build_mode = self::BUILD_MODE_AUTO;

	//-------------------------------------------------------------------------------------- $content
	/** @var string|string[]|array|null array for string[][] for build_mode AUTO */
	private array|string|null $content = null;

	//-------------------------------------------------------------------------------------- $end_tag
	protected bool $end_tag;

	//----------------------------------------------------------------------------------------- $name
	private string $name = '';

	//-------------------------------------------------------------------------------------- $prepend
	/** @var Element[] */
	public array $prepend = [];

	//--------------------------------------------------------------------------------------- $styles
	/** @var string[] */
	private array $styles = [];

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $name = null, bool $end_tag = true)
	{
		if (isset($name)) $this->name = $name;
		$this->end_tag = $end_tag;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		if ($this->styles) {
			ksort($this->styles);
			$this->setAttribute('style', join(';' . SP, $this->styles));
		}
		if ($this->attributes) {
			$class = $this->getAttribute('class');
			if ($class && str_contains($class->value, SP)) {
				$classes = explode(SP, $class->value);
				sort($classes);
				$class->value = join(SP, $classes);
			}
			ksort($this->attributes);
		}
		$content = $this->getContent();
		return
			join('', $this->prepend)
			. '<' . $this->name . ($this->attributes ? (SP . join(SP, $this->attributes)) : '') . '>'
			. (($this->end_tag || isset($content)) ? ($content . '</' . $this->name . '>') : '')
			. join('', $this->append);
	}

	//-------------------------------------------------------------------------------------- addClass
	public function addClass(string $class_name) : Attribute
	{
		$class = $this->getAttribute('class');
		if (!isset($class)) {
			return $this->setAttribute('class', $class_name);
		}
		elseif (!str_contains(SP . $class->value . SP, SP . $class_name . SP)) {
			$class->value .= SP . $class_name;
		}
		return $class;
	}

	//---------------------------------------------------------------------------------- getAttribute
	public function getAttribute(string $name) : ?Attribute
	{
		return $this->attributes[$name] ?? null;
	}

	//--------------------------------------------------------------------------------- getAttributes
	/** @return Attribute[] */
	public function getAttributes() : array
	{
		return $this->attributes;
	}

	//------------------------------------------------------------------------------------ getContent
	public function getContent() : ?string
	{
		if (!is_array($this->content)) {
			return $this->content;
		}
		if (!$this->content) {
			return '';
		}
		if ($this->build_mode === self::BUILD_MODE_RAW) {
			return $this->getContentAsRaw();
		}
		// else <=> if $this->build_mode === self::BUILD_MODE_AUTO
		$element = reset($this->content);
		return is_array($element)
			? $this->getContentAsTable()
			: $this->getContentAsList();
	}

	//------------------------------------------------------------------------------ getContentAsList
	private function getContentAsList() : Unordered
	{
		$list = new Unordered();
		foreach ($this->content as $item) {
			$list->addItem($item);
		}
		return $list;
	}

	//------------------------------------------------------------------------------- getContentAsRaw
	private function getContentAsRaw() : string
	{
		return $this->parseArray($this->content);
	}

	//----------------------------------------------------------------------------- getContentAsTable
	private function getContentAsTable() : Table
	{
		$table = new Table();
		$table->body = new Table\Body();
		foreach ($this->content as $content_row) {
			$row = new Table\Row();
			foreach ($content_row as $cell_content) {
				$row->addCell(new Table\Standard_Cell($cell_content));
			}
			$table->body->addRow($row);
		}
		return $table;
	}

	//--------------------------------------------------------------------------------------- getData
	public function getData(string $name) : ?Attribute
	{
		return $this->getAttribute('data-' . $name);
	}

	//------------------------------------------------------------------------------------ parseArray
	private function parseArray(array $array) : string
	{
		$content = '';
		foreach ($array as $item) {
			if (is_array($item)) {
				$content .= $this->parseArray($item);
			}
			elseif ($item instanceof Element) {
				$saved_mode       = $item->build_mode;
				$item->build_mode = $this->build_mode;
				$content         .= $item;
				$item->build_mode = $saved_mode;
			}
			else {
				$content .= $item;
			}
		}
		return $content;
	}

	//------------------------------------------------------------------------------- removeAttribute
	public function removeAttribute(string $name) : ?string
	{
		if (!isset($this->attributes[$name])) {
			return null;
		}
		$value = $this->attributes[$name];
		unset($this->attributes[$name]);
		return $value;
	}

	//------------------------------------------------------------------------------------ removeData
	public function removeData(string $name) : ?string
	{
		if (!isset($this->attributes['data-' . $name])) {
			return null;
		}
		$value = $this->attributes['data-' . $name]->value;
		unset($this->attributes['data-' . $name]);
		return $value;
	}

	//---------------------------------------------------------------------------------- setAttribute
	/**
	 * Sets a value for an HTML attribute.
	 *
	 * Beware boolean attributes :
	 * - if value is false or equivalent (e.g. null), the attribute will not appear in HTML !
	 * - call this with true to get boolean attributes visible
	 *
	 * This is why true is the default for $value
	 */
	public function setAttribute(string $name, bool|int|string $value = true) : Attribute
	{
		if ($name === 'name') {
			// this is because PHP does not like '.' into names of GET/POST vars
			$value = str_replace(DOT, '>', $value);
		}
		return $this->setAttributeNode(new Attribute($name, $value));
	}

	//------------------------------------------------------------------------------ setAttributeNode
	public function setAttributeNode(Attribute $attribute) : Attribute
	{
		return $this->attributes[$attribute->name] = $attribute;
	}

	//---------------------------------------------------------------------------------- setBuildMode
	public function setBuildMode(string $build_mode) : void
	{
		$this->build_mode = $build_mode;
	}

	//------------------------------------------------------------------------------------ setContent
	/** @param $content array|string|string[]|null mixed[] means string[][] for build_mode AUTO */
	public function setContent(array|string|null $content) : void
	{
		$this->content = $content;
	}

	//--------------------------------------------------------------------------------------- setData
	public function setData(string $name, bool|int|string|null $value = true) : Attribute
	{
		return $this->setAttributeNode(new Attribute('data-' . $name, $value));
	}

	//-------------------------------------------------------------------------------------- setStyle
	public function setStyle(string $key, string $value) : void
	{
		$this->styles[$key] = new Style($key, $value);
	}

}
