<?php
namespace ITRocks\Framework\Reflection\Attribute;

use Attribute;

/**
 * Designates inheritable attributes.
 * If the attribute is repeatable, or if it has no instance at current class value,
 * it will be searched into traits, then interfaces, then parent class, and so on.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Inheritable
{

}
