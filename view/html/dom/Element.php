<?php
namespace ITRocks\Framework\View\Html\Dom;

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

	//----------------------------------------------------------------------------------- $attributes
	/**
	 * Available attributes
	 *
	 * @var Attribute[] key is the attribute name
	 */
	private $attributes = [];

	//----------------------------------------------------------------------------------- $build_mode
	/**
	 * In AUTO mode, check content to format as list or table, in RAW strictly build content
	 *
	 * @var boolean
	 */
	private $build_mode = self::BUILD_MODE_AUTO;

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @var string|string[]|array array for string[][] for build_mode AUTO
	 */
	private $content;

	//-------------------------------------------------------------------------------------- $end_tag
	/**
	 * @var boolean
	 */
	protected $end_tag;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	private $name;

	//--------------------------------------------------------------------------------------- $styles
	/**
	 * @var string[]
	 */
	private $styles = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name    string
	 * @param $end_tag boolean
	 */
	public function __construct($name = null, $end_tag = true)
	{
		if (isset($name)) $this->name = $name;
		$this->end_tag = $end_tag;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		if ($this->styles) {
			ksort($this->styles);
			$this->setAttribute('style', join(';' . SP, $this->styles));
		}
		if ($this->attributes) {
			$class = $this->getAttribute('class');
			if ($class && strpos($class->value, SP)) {
				$classes = explode(SP, $class->value);
				sort($classes);
				$class->value = join(SP, $classes);
			}
			ksort($this->attributes);
		}
		$content = $this->getContent();
		return '<' . $this->name . ($this->attributes ? (SP . join(SP, $this->attributes)) : '') . '>'
		. (($this->end_tag || isset($content)) ? ($content . '</' . $this->name . '>') : '');
	}

	//-------------------------------------------------------------------------------------- addClass
	/**
	 * @param $class_name string
	 * @return Attribute
	 */
	public function addClass($class_name)
	{
		$class = $this->getAttribute('class');
		if (!isset($class)) {
			return $this->setAttribute('class', $class_name);
		}
		elseif (strpos(SP . $class->value . SP, SP . $class_name . SP) === false) {
			$class->value .= SP . $class_name;
		}
		return $class;
	}

	//---------------------------------------------------------------------------------- getAttribute
	/**
	 * @param $name string
	 * @return Attribute
	 */
	public function getAttribute($name)
	{
		return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
	}

	//--------------------------------------------------------------------------------- getAttributes
	/**
	 * @return Attribute[]
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	//------------------------------------------------------------------------------------ getContent
	/**
	 * @return string
	 */
	public function getContent()
	{
		if (is_array($this->content)) {
			if ($this->content) {
				if ($this->build_mode == self::BUILD_MODE_RAW) {
					$content = $this->getContentAsRaw();
				}
				// else <=> if $this->build_mode == self::BUILD_MODE_AUTO
				else {
					$element = reset($this->content);
					if (is_array($element)) {
						$content = $this->getContentAsTable();
					}
					else {
						$content = $this->getContentAsList();
					}
				}
			}
			else {
				$content = '';
			}
			return $content;
		}
		return $this->content;
	}

	//------------------------------------------------------------------------------ getContentAsList
	/**
	 * @return Unordered
	 */
	private function getContentAsList()
	{
		$list = new Unordered();
		foreach ($this->content as $item) {
			$list->addItem($item);
		}
		return $list;
	}

	//------------------------------------------------------------------------------- getContentAsRaw
	/**
	 * @return string
	 */
	private function getContentAsRaw()
	{
		$content = $this->parseArray($this->content);
		return $content;
	}

	//----------------------------------------------------------------------------- getContentAsTable
	/**
	 * @return Table
	 */
	private function getContentAsTable()
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
	/**
	 * @param $name string
	 * @return Attribute
	 */
	public function getData($name)
	{
		return $this->getAttribute('data-' . $name);
	}

	//------------------------------------------------------------------------------------ parseArray
	/**
	 * @param $array array
	 * @return string
	 */
	private function parseArray(array $array)
	{
		$content = '';
		foreach ($array as $item) {
			if (is_array($item)) {
				$content .= $this->parseArray($item);
			}
			elseif ($item instanceof Element) {
				$saved_mode       = $item->build_mode;
				$item->build_mode = $this->build_mode;
				$content         .= (string)$item;
				$item->build_mode = $saved_mode;
			}
			else {
				$content .= (string)$item;
			}
		}
		return $content;
	}

	//------------------------------------------------------------------------------- removeAttribute
	/**
	 * @param $name string
	 */
	public function removeAttribute($name)
	{
		if (isset($this->attributes[$name])) {
			unset($this->attributes[$name]);
		}
	}

	//------------------------------------------------------------------------------------ removeData
	/**
	 * @param $name string
	 */
	public function removeData($name)
	{
		if (isset($this->attributes['data-' . $name])) {
			unset($this->attributes['data-' . $name]);
		}
	}

	//---------------------------------------------------------------------------------- setAttribute
	/**
	 * Sets a value for an HTML attribute.
	 *
	 * Beware boolean attributes :
	 * - if value is false or equivalent (eg null), the attribute will not appear in HTML !
	 * - call this with true to get boolean attributes visible
	 *
	 * This is why true is the default for $value
	 *
	 * @param $name  string
	 * @param $value boolean|integer|string
	 * @return Attribute
	 */
	public function setAttribute($name, $value = true)
	{
		if ($name === 'name') {
			// this is because PHP does not like '.' into names of GET/POST vars
			$value = str_replace(DOT, '>', $value);
		}
		return $this->setAttributeNode(new Attribute($name, $value));
	}

	//------------------------------------------------------------------------------ setAttributeNode
	/**
	 * @param $attribute Attribute
	 * @return Attribute
	 */
	public function setAttributeNode(Attribute $attribute)
	{
		return $this->attributes[$attribute->name] = $attribute;
	}

	//---------------------------------------------------------------------------------- setBuildMode
	/**
	 * @param $build_mode string
	 */
	public function setBuildMode($build_mode)
	{
		$this->build_mode = $build_mode;
	}

	//------------------------------------------------------------------------------------ setContent
	/**
	 * @param $content string|string[]|mixed[] mixed[] means string[][] for build_mode AUTO
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}

	//--------------------------------------------------------------------------------------- setData
	/**
	 * @param $name  string
	 * @param $value boolean|integer|string
	 * @return Attribute
	 */
	public function setData($name, $value = true)
	{
		return $this->setAttributeNode(new Attribute('data-' . $name, $value));
	}

	//-------------------------------------------------------------------------------------- setStyle
	/**
	 * @param $key   string
	 * @param $value string
	 */
	public function setStyle($key, $value)
	{
		$this->styles[$key] = new Style($key, $value);
	}

}
