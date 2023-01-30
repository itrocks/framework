<?php
namespace ITRocks\Framework\Reflection\Attribute;

use Attribute;

/**
 * Inheritable attribute : On Has_Attributes::getAttributes call, inherited attributes will be
 * scanned too.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class Inheritable
{

}
