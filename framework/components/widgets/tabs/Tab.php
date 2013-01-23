<?php
namespace SAF\Framework;

class Tab
{

	//---------------------------------------------------------------------------------------- $title
	/**
	 * @var string
	 */
	public $title;

	//-------------------------------------------------------------------------------------- $content
	/**
	 * For content
	 *
	 * @var mixed
	 */
	public $content;

	//-------------------------------------------------------------------------------------- $columns
	/**
	 * For content grouped by column
	 *
	 * @var mixed[]
	 */
	public $columns = array();

	//------------------------------------------------------------------------------------- $includes
	/**
	 * For contained tabs
	 *
	 * @var Tab[]
	 */
	public $includes = array();

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param string $title
	 * @param mixed $content
	 */
	public function __construct($title = null, $content = null)
	{
		if (isset($title)) {
			$this->title = $title;
		}
		if (isset($content)) {
			$this->content = $content;
		}
	}

	//----------------------------------------------------------------------------------------- __get
	public function __get($key)
	{
		return $this->includes[$key];
	}

	//--------------------------------------------------------------------------------------- __isset
	public function __isset($key)
	{
		return isset($this->includes[$key]);
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->title;
	}

	//--------------------------------------------------------------------------------------- __unset
	public function __unset($key)
	{
		unset($this->includes[$key]);
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Add content to the tab (eg content is an array, new elements are added)
	 *
	 * @param mixed $content
	 * @return Tab
	 */
	public function add($content)
	{
		if (is_array($this->content) && is_array($content)) {
			$this->content = array_merge($this->content, $content);
		}
		else {
			$this->content .= $content;
		}
		return $this;
	}

	//-------------------------------------------------------------------------------------------- id
	/**
	 * Return a calculated id for the tab, calculated from its title
	 */
	public function id()
	{
		return Names::displayToProperty($this->title);
	}

	//--------------------------------------------------------------------------------------- getList
	/**
	 * Return included tabs, but not those which identifier begins with "_"
	 *
	 * @return Tab[]
	 */
	public function included()
	{
		$list = array();
		foreach ($this->includes as $key => $tab) {
			if (substr($key, 0, 1) != "_") {
				$list[$key] = $tab;
			}
		}
		return $list;
	}

}
