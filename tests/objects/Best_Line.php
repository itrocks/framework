<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Store_Name;

/**
 * Test line class
 */
#[Store_Name('test_best_lines')]
class Best_Line
{

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * @link Map
	 * @var Order_Line[]
	 */
	public array $lines;

}
