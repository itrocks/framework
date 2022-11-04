<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Field\Property;
use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;

/**
 * @feature Automatically remove identical columns from prints
 * @feature_include Empty_Columns_Remover
 */
class Identical_Columns_Remover implements Configurable, Registerable
{

	//--------------------------------------------------------------------------------------- $rename
	/**
	 * When a column is removed because of identical value, the column that is still present header
	 * will be reworded as this
	 *
	 * @var string[] [$removed caption => $replacement caption]
	 */
	protected array $rename;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration string[]
	 */
	public function __construct($configuration = null)
	{
		if (isset($configuration)) {
			foreach ($configuration as $key => $value) {
				$this->rename[strtoupper($key)] = $value;
			}
		}
	}

	//------------------------------------------------------------------------------ identicalColumns
	/**
	 * @input  $set, $unset
	 * @output $set, $unset
	 * @param $object     Empty_Columns_Remover
	 * @param $group      Group
	 * @param $properties Element[]|Property[]
	 */
	public function identicalColumns(Empty_Columns_Remover $object, Group $group, array $properties)
	{
		if (!$group->iterations || !$object->unset) {
			return;
		}
		reset($object->set);
		$columns        = array_slice(array_keys($object->set), 1);
		$first_column   = key($object->set);
		$different      = [$first_column => true];
		$elements_count = count(reset($group->iterations)->elements);
		$set_count      = count($object->set);
		$headers        = $object->headers($group, $properties);
		$headers        = reset($headers);

		foreach ($group->iterations as $iteration) {
			if (count($iteration->elements) < $elements_count) {
				continue;
			}
			/** @var $previous_element Text */
			$previous_column = $first_column;
			foreach ($columns as $column) {
				if (!isset($this->rename[strtoupper($headers[$column]->text)])) {
					$different[$column] = true;
					$previous_column    = $column;
					continue;
				}
				if (isset($different[$column])) {
					$previous_column = $column;
					continue;
				}
				/** @var $element Text */
				$element = $iteration->elements[$column];
				/** @var $previous_element Text */
				$previous_element = $iteration->elements[$previous_column];
				if (!strcmp($element->text, $previous_element->text)) {
					$previous_column = $column;
					continue;
				}
				$different[$column] = true;
				if (count($different) === $set_count) {
					return;
				}
				$previous_column = $column;
			}
		}
		$object->set   = array_intersect_key($different, $object->set);
		$object->unset = array_diff_key($properties, $object->set);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$register->aop->afterMethod(
			[Empty_Columns_Remover::class, 'emptyColumns'], [$this, 'identicalColumns']
		);
		$register->aop->afterMethod(
			[Empty_Columns_Remover::class, 'headers'], [$this, 'renameHeaders']
		);
	}

	//--------------------------------------------------------------------------------- renameHeaders
	/**
	 * @input $headers
	 * @param $alter  boolean
	 * @param $result array Text[][]
	 */
	public function renameHeaders(bool $alter, array $result)
	{
		if (!$alter) {
			return;
		}
		foreach ($result as $headers) {
			$replacements = [];
			foreach ($headers as $header) {
				if (isset($this->rename[$header->text])) {
					$replacements[$this->rename[$header->text]]
						= ($replacements[$this->rename[$header->text]] ?? 0) + 1;
				}
			}
			foreach ($headers as $header) {
				if (
					isset($this->rename[$header->text])
					&& ($replacements[$this->rename[$header->text]] === 1)
				) {
					$header->text = $this->rename[$header->text];
				}
			}
		}
	}

}
