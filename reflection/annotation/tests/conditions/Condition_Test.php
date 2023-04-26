<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests\Conditions;

use ITRocks\Framework\Feature\Edit\Html_Builder_Property;
use ITRocks\Framework\Reflection\Annotation\Tests\Conditions;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tests\Test;

/**
 * Tests for @conditions annotation
 *
 * @example
 * http://localhost/itrocks/ITRocks/Framework/Tests/run/ITRocks/Framework/Reflection/Annotation/Tests/Conditions/Condition_Test
 */
class Condition_Test extends Test
{

	//------------------------------------------------------------------------------------------ norm
	/**
	 * Normalize text
	 *
	 * @param $text string
	 * @return string[]
	 */
	private function norm(string $text) : array
	{
		$result = [];
		foreach (explode('>', $text) as $line) {
			if (
				(str_contains($line, '<input') || str_contains($line, '<select'))
				&& str_contains($line, SP . 'name=' . DQ)
			) {
				$result[] = mParse($line, SP . 'name=' . DQ, DQ);
			}
		}
		return $result;
	}

	//---------------------------------------------------------------------------- testConditionsForm
	/**
	 * Tests the Conditions
	 */
	public function testConditionsForm() : void
	{
		$object = new Conditions();
		$line1  = new Conditions_Collection();
		$line2  = new Conditions_Collection();
		$line2->boolean = true;
		$object->lines  = [$line1, $line2];
		$builder = new Html_Builder_Property(
			new Reflection_Property(Conditions::class, 'lines'), $object->lines
		);
		$assume = <<<EOT
lines[id][0]
lines[boolean][0]
lines[boolean_false_conditioned][0]
lines[boolean_true_conditioned][0]
lines[id_conditioned_object][0]
lines[enum][0]
lines[enum_conditioned_1][0]
lines[enum_conditioned_2][0]
lines[id][1]
lines[boolean][1]
lines[boolean_false_conditioned][1]
lines[boolean_true_conditioned][1]
lines[id_conditioned_object][1]
lines[enum][1]
lines[enum_conditioned_1][1]
lines[enum_conditioned_2][1]
lines[id][2]
lines[boolean][2]
lines[boolean_false_conditioned][2]
lines[boolean_true_conditioned][2]
lines[id_conditioned_object][2]
lines[enum][2]
lines[enum_conditioned_1][2]
lines[enum_conditioned_2][2]
EOT;
		self::assertEquals(explode(LF, $assume), $this->norm($builder->build()));
	}

}
