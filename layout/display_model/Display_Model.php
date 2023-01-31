<?php
namespace ITRocks\Framework\Layout;

use ITRocks\Framework\Layout\Display_Model\Page;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;

/**
 * Display layout model
 *
 * @override pages @var Page[]
 * @property Page[] pages
 */
#[Store('display_models')]
class Display_Model extends Model
{

}
