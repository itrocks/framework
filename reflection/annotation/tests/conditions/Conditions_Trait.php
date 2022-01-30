<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests\Conditions;

use ITRocks\Framework\User;

/**
 * Conditions trait : common to Conditions and Conditions_Collection
 */
trait Conditions_Trait
{

	//-------------------------------------------------------------------------------------- $boolean
	/**
	 * @var boolean
	 */
	public bool $boolean;

	//-------------------------------------------------------------------- $boolean_false_conditioned
	/**
	 * @conditions boolean=false
	 * @var string
	 */
	public $boolean_false_conditioned;

	//--------------------------------------------------------------------- $boolean_true_conditioned
	/**
	 * @conditions boolean=true
	 * @var string
	 */
	public $boolean_true_conditioned;

	//--------------------------------------------------------------------------- $conditioned_object
	/**
	 * @conditions boolean=true, enum=value1
	 * @link Object
	 * @var User
	 */
	public $conditioned_object;

	//----------------------------------------------------------------------------------------- $enum
	/**
	 * @values value1, value2
	 * @var string
	 */
	public $enum = 'value1';

	//--------------------------------------------------------------------------- $enum_conditioned_1
	/**
	 * @conditions enum=value1
	 * @var string
	 */
	public $enum_conditioned_1;

	//--------------------------------------------------------------------------- $enum_conditioned_2
	/**
	 * @conditions enum=value2
	 * @var string
	 */
	public $enum_conditioned_2;

}
