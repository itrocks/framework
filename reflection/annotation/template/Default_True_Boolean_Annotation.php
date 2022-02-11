<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

/**
 * Boolean annotation which default value is true
 */
class Default_True_Boolean_Annotation extends Boolean_Annotation
{

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @override
	 * @var boolean
	 */
	public array|bool|null|string $value = true;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Register value as boolean
	 *
	 * If a boolean annotation has no value or is not 'false' or zero, annotation's value will be true
	 *
	 * @param $value ?string
	 */
	public function __construct(?string $value)
	{
		parent::__construct($value);
		if (is_null($value)) {
			$this->value = true;
		}
	}

}
