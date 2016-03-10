<?php
namespace SAF\Framework\Widget\Edit;

use SAF\Framework\Builder;
use SAF\Framework\View\Html\Builder\Abstract_Collection;
use SAF\Framework\View\Html\Dom\Table;

/**
 * Takes a collection of objects and build a HTML edit sub-form containing their data.
 *
 * This is for objects with multiple classes, all extending the same abstract class.
 */
class Html_Builder_Abstract_Collection extends Abstract_Collection
{

	//-------------------------------------------------------------------------------------- $preprop
	/**
	 * @var string
	 */
	public $preprop = null;

	//------------------------------------------------------------------------------------- $template
	/**
	 * @var Html_Template
	 */
	private $template = null;

	//----------------------------------------------------------------------------------------- build
	/**
	 * TODO remove this patch will crash AOP because AOP on parent method does not work
	 * + AOP should create a build_() method that calls parent::build()
	 * + AOP should complete parameters like Table to give full path as they may not be in use clause
	 *
	 * @return string
	 */
	public function build()
	{
		return parent::build();
	}

	//----------------------------------------------------------------------------------- setTemplate
	/**
	 * @param $template Html_Template
	 * @return Html_Builder_Type
	 */
	public function setTemplate(Html_Template $template)
	{
		$this->template = $template;
		return $this;
	}

}
