<?php
namespace ITRocks\Framework\Configuration\File\Source;

/**
 * Class use clause
 */
class Class_Use
{

	//---------------------------------------------------------------------------------------- $rules
	/**
	 * Use rules
	 *
	 * @var string
	 */
	public $rules;

	//----------------------------------------------------------------------------------- $trait_name
	/**
	 * The name of the used trait
	 *
	 * @var string
	 */
	public $trait_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $trait_name string
	 * @param $rules      string
	 */
	public function __construct($trait_name = null ,$rules = null)
	{
		if (isset($trait_name)) {
			$this->trait_name = $trait_name;
		}
		if (isset($rules)) {
			$this->rules = $rules;
		}
	}

}
