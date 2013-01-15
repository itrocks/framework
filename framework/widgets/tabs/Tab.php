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
	 * @var multitype:mixed
	 */
	public $columns = array();

	//----------------------------------------------------------------------------------------- $tabs
	/**
	 * For contained tabs
	 *
	 * @var multitype:Tab
	 */
	public $tabs = array();

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

}
