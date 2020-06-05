<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Field\Text;
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
	protected $rename;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration string[]
	 */
	public function __construct($configuration = null)
	{
		if (isset($configuration)) {
			$this->rename = $configuration;
		}
	}

	//------------------------------------------------------------------------------ identicalColumns
	/**
	 * @input $group->iterations, $properties, $set, $unset
	 * @output $set, $unset
	 * @param $object Empty_Columns_Remover
	 */
	public function identicalColumns(Empty_Columns_Remover $object)
	{
		if (!$object->unset) {
			return;
		}
		reset($object->set);
		$columns      = array_slice(array_keys($object->set), 1);
		$first_column = key($object->set);
		$different    = [$first_column => true];
		$set_count    = count($object->set);
		foreach ($object->group->iterations as $iteration) {
			/** @var $previous_element Text */
			$previous_column = $first_column;
			foreach ($columns as $column) {
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
		$object->unset = array_diff_key($object->properties, $object->set);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
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
	 * @param $object Empty_Columns_Remover
	 */
	public function renameHeaders(Empty_Columns_Remover $object)
	{
		foreach ($object->headers as $header) {
			if (isset($this->rename[$header->text])) {
				$header->text = $this->rename[$header->text];
			}
		}
	}

}