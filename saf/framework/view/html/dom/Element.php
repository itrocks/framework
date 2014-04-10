<?php
namespace SAF\Framework\View\Html\Dom;

/**
 * A DOM element class
 */
abstract class Element
{

	//----------------------------------------------------------------------------------- $attributes
	/**
	 * Available attributes
	 *
	 * @var Attribute[] key is the attribute name
	 */
	private $attributes = [];

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @var string
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
		elseif (strpos(SP . $class->value . SP, $class_name) === false) {
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
		return $this->content;
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
	 * @param $name  string
	 * @param $value string
	 * @return Attribute
	 */
	public function setAttribute($name, $value = null)
	{
		if ($name == 'name') {
			// this is because PHP does not like '.' into names of GET/POST vars
			$value = str_replace(DOT, '>', $value);
		}
		return $this->setAttributeNode(new Attribute($name, $value));
	}

	//---------------------------------------------------------------------------------- setAttribute
	/**
	 * @param $attr Attribute
	 * @return Attribute
	 */
	public function setAttributeNode(Attribute $attr)
	{
		return $this->attributes[$attr->name] = $attr;
	}

	//------------------------------------------------------------------------------------ setContent
	/**
	 * @param $content string
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}

	//--------------------------------------------------------------------------------------- setData
	/**
	 * @param $name  string
	 * @param $value string
	 * @return Attribute
	 */
	public function setData($name, $value = null)
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

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		if ($this->styles) {
			$this->setAttribute('style', join(';' . SP, $this->styles));
		}
		$content = $this->getContent();
		return '<' . $this->name . ($this->attributes ? (SP . join(SP, $this->attributes)) : '') . '>'
			. (($this->end_tag || isset($content)) ? ($content . '</' . $this->name . '>') : '');
	}

}
