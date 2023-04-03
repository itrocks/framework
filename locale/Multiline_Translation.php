<?php
namespace ITRocks\Framework\Locale;

use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Property\Multiline;

/**
 * Adds multiple lines and big string capabilities to Translation
 *
 * @override text        @max_length 200000
 * @override translation @max_length 200000
 */
#[Extend(Translation::class)]
#[Override('text',        new Multiline)]
#[Override('translation', new Multiline)]
trait Multiline_Translation
{

}
