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
	protected string $class_name;

	//--------------------------------------------------------------------------------- $feature_name
	/**
	 * @var string
	 */
	protected string $feature_name;

	//---------------------------------------------------------------------------------------- $files
	/**
	 * @var array[]
	 */
	protected array $files;

	//----------------------------------------------------------------------------------------- $form
	/**
	 * @var array
	 */
	protected array $form;

	//----------------------------------------------------------------------------------- $parameters
	/**
	 * @var array
	 */
	protected array $parameters;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Json_Template constructor.
	 *
	 * @param $parameters   array
	 * @param $form         array
	 * @param $files        array[]
	 * @param $class_name   string
	 * @param $feature_name string
	 */
	public function __construct(
		array $parameters, array $form, array $files, string $class_name, string $feature_name
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
	abstract public function render() : string;

}
