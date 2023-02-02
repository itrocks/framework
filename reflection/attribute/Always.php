<?php
namespace ITRocks\Framework\Reflection\Attribute;

use Attribute;

/**
 * Designates an Attribute that is always set.
 * When you call getAttributes($name) or getAttribute($name), if none of this attribute is set,
 * a default instance will be automatically instantiated.
 * getAttributes() without $name will not return Always attributes
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Always
{

}
