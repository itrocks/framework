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
	 * @var mixed
	 */
	public $content;

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

}
