<?php
namespace ITRocks\Framework\Locale;

use ITRocks\Framework\Reflection\Attribute\Class_\Extend;

/**
 * Adds multiple lines and big string capabilities to Translation
 *
 * @override text        @max_length 200000 @multiline
 * @override translation @max_length 200000 @multiline
 */
#[Extend(Translation::class)]
trait Multiline_Translation
{

}
