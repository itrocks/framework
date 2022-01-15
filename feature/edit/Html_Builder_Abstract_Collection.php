<?php
namespace ITRocks\Framework\Feature\Edit;

use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Builder\Abstract_Collection;
use ITRocks\Framework\View\Html\Template;

/**
 * Takes a collection of objects and build a HTML edit sub-form containing their data.
 *
 * This is for objects with multiple classes, all extending the same abstract class.
 */
class Html_Builder_Abstract_Collection extends Abstract_Collection
{

	//------------------------------------------------------------------------------------- $pre_path
	/**
	 * @var string
	 */
	public string $pre_path;

	//------------------------------------------------------------------------------------- $template
	/**
	 * @var ?Html_Template
	 */
	public ?Html_Template $template = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property   Reflection_Property
	 * @param $collection array
	 * @param $pre_path   string
	 */
	public function __construct(
		Reflection_Property $property, array $collection, string $pre_path = ''
	) {
		parent::__construct($property, $collection);
		$this->pre_path = $pre_path;
	}

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
		$result = '';
		foreach ($this->collection as $object) {
			$property_prefix = $this->property->pathAsField()
				. '[' . $this->template->nextCounter($this->property->path) . ']';
			$parameters = [
				$object,
				Parameter::IS_INCLUDED       => true,
				Parameter::PROPERTIES_PREFIX => $property_prefix,
				Template::TEMPLATE_NAMESPACE => __NAMESPACE__,
				Template::TEMPLATE           => 'object'
			];
			$result .= View::run($parameters, [], [], get_class($object), 'output');
		}
		return $result;
	}

	//----------------------------------------------------------------------------------- setTemplate
	/**
	 * @param $template Html_Template
	 * @return static
	 */
	public function setTemplate(Html_Template $template)
	{
		$this->template = $template;
		return $this;
	}

}
