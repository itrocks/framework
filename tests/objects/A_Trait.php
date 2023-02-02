<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Extend;

/**
 * A trait here to extend another trait, himself extending a class
 */
#[Extend(Has_Counter::class)]
trait A_Trait
{

}
