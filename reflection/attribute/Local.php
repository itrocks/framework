<?php
namespace ITRocks\Framework\Reflection\Attribute;

use Attribute;

/**
 * All attributes are inheritable by default:
 * on Has_Attributes::getAttributes call, inherited targets will be scanned too.
 * Local attributes are non-inheritable attributes: inherited targets will not be scanned.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
#[Local]
class Local
{

}
