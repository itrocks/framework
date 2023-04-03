<?php
namespace ITRocks\Framework\Locale;

use ITRocks\Framework\Feature\Validate\Property\Max_Length;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Property\Multiline;

/**
 * Adds multiple lines and big string capabilities to Translation
 */
#[Extend(Translation::class)]
#[Override('text',        new Max_Length('200000'), new Multiline)]
#[Override('translation', new Max_Length('200000'), new Multiline)]
trait Multiline_Translation
{

}
