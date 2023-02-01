<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Extends_;

/**
 * A trait here to extend another trait, himself extending a class
 */
#[Extends_(Has_Counter::class)]
trait A_Trait
{

}
