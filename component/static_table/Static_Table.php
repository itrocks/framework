<?php
namespace ITRocks\Framework\Component;

use ITRocks\Framework\View\Html\Dom\Table;

/**
 * Class Static_Table
 */
class Static_Table
{

	//-------------------------------------------------------------------------------- COMPONENT_NAME
	const COMPONENT_NAME = 'static_table';

	//---------------------------------------------------------------------------------------- $table
	/**
	 * @var Table
	 */
	public $table;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Static_Table constructor.
	 *
	 * @param $header_values array
	 * @param $body_rows     array
	 */
	public function __construct(array $header_values, array $body_rows)
	{
		$this->table = new Table();
		$this->table->addClass('static-table');

		$this->table->head = $this->buildHeader($header_values);
		$this->table->body = $this->buildBody($body_rows);
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return strval($this->table);
	}

	//------------------------------------------------------------------------------------- buildBody
	/**
	 * @param $body_rows string[][]
	 * @return Table\Body
	 */
	protected function buildBody(array $body_rows) : Table\Body
	{
		$body = new Table\Body();
		foreach ($body_rows as $body_row) {
			$row = new Table\Row();
			foreach ($body_row as $row_value) {
				$row->addCell(new Table\Standard_Cell($row_value));
			}
			$row->addCell(new Table\Standard_Cell(''));
			$body->addRow($row);
		}
		return $body;
	}

	//----------------------------------------------------------------------------------- buildHeader
	/**
	 * @param $header_values string[]
	 * @return Table\Head
	 */
	protected function buildHeader(array $header_values) : Table\Head
	{
		$header_row = new Table\Row();
		foreach ($header_values as $header_value) {
			$header_row->addCell(new Table\Header_Cell($header_value));
		}
		$header_row->addCell(new Table\Header_Cell(''));

		$header = new Table\Head();
		$header->addRow($header_row);
		return $header;
	}

}
