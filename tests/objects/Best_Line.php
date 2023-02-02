<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;

/**
 * Test line class
 */
#[Store('test_best_lines')]
class Best_Line
{

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * @var Order_Line[]
	 */
	public array $lines;

}
