<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests\Conditions;

use ITRocks\Framework\User;

/**
 * Conditions trait : common to Conditions and Conditions_Collection
 */
trait Conditions_Trait
{

	//-------------------------------------------------------------------------------------- $boolean
	public bool $boolean = false;

	//-------------------------------------------------------------------- $boolean_false_conditioned
	/**
	 * @conditions boolean=false
	 */
	public string $boolean_false_conditioned;

	//--------------------------------------------------------------------- $boolean_true_conditioned
	/**
	 * @conditions boolean=true
	 */
	public string $boolean_true_conditioned;

	//--------------------------------------------------------------------------- $conditioned_object
	/**
	 * @conditions boolean=true, enum=value1
	 */
	public ?User $conditioned_object;

	//----------------------------------------------------------------------------------------- $enum
	/**
	 * @values value1, value2
	 */
	public string $enum = 'value1';

	//--------------------------------------------------------------------------- $enum_conditioned_1
	/**
	 * @conditions enum=value1
	 */
	public string $enum_conditioned_1;

	//--------------------------------------------------------------------------- $enum_conditioned_2
	/**
	 * @conditions enum=value2
	 */
	public string $enum_conditioned_2;

}
