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
	public string $rules;

	//----------------------------------------------------------------------------------- $trait_name
	/**
	 * The name of the used trait
	 *
	 * @var string
	 */
	public string $trait_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $trait_name string|null
	 * @param $rules      string|null
	 */
	public function __construct(string $trait_name = null, string $rules = null)
	{
		if (isset($trait_name)) {
			$this->trait_name = $trait_name;
		}
		if (isset($rules)) {
			$this->rules = $rules;
		}
	}

}
