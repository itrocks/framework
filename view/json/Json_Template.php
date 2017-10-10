<?php
namespace ITRocks\Framework\View\Json;

/**
 * Interface for all json templates
 */
abstract class Json_Template
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	protected $class_name;

	//--------------------------------------------------------------------------------- $feature_name
	/**
	 * @var string
	 */
	protected $feature_name;

	//---------------------------------------------------------------------------------------- $files
	/**
	 * @var array[]
	 */
	protected $files;

	//----------------------------------------------------------------------------------------- $form
	/**
	 * @var array
	 */
	protected $form;

	//----------------------------------------------------------------------------------- $parameters
	/**
	 * @var array
	 */
	protected $parameters;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Json_Template constructor.
	 *
	 * @param $class_name      string
	 * @param $feature_name    string
	 * @param $files           array[]
	 * @param $form            array
	 * @param $parameters      array
	 * @param $view            Default_View
	 */
	public function __construct(
		array $parameters, array $form, array $files, $class_name, $feature_name
	) {
		$this->parameters   = $parameters;
		$this->form         = $form;
		$this->files        = $files;
		$this->class_name   = $class_name;
		$this->feature_name = $feature_name;
	}

	//---------------------------------------------------------------------------------------- render
	/**
	 * Default rendering for a business object to json.
	 *
	 * @return string
	 */
	abstract public function render();

}
