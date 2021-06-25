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
	 * @param $header_cells Table\Header_Cell[]
	 * @param $body_rows    Table\Standard_Cell[][]
	 * @param $footer_rows  Table\Standard_Cell[][]
	 */
	public function __construct(array $header_cells, array $body_rows, array $footer_rows = [])
	{
		$this->table = new Table();
		$this->table->addClass('static-table');
		$this->table->head   = $this->buildHeader($header_cells);
		$this->table->body   = $this->buildBody($body_rows);
		$this->table->footer = $this->buildFooter($footer_rows);
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
	 * @param $body_rows Table\Standard_Cell[][]
	 * @return Table\Body
	 */
	protected function buildBody(array $body_rows) : Table\Body
	{
		$body = new Table\Body();
		foreach ($body_rows as $body_row) {
			$row = new Table\Row();
			foreach ($body_row as $row_cell) {
				$row->addCell($row_cell);
			}
			$row->addCell(new Table\Standard_Cell(''));
			$body->addRow($row);
		}
		return $body;
	}

	//----------------------------------------------------------------------------------- buildFooter
	/**
	 * @param $footer_rows Table\Standard_Cell[][]
	 * @return string
	 */
	protected function buildFooter(array $footer_rows) : string
	{
		$footer = new Table\Footer();
		foreach ($footer_rows as $footer_row) {
			$row = new Table\Row();
			foreach ($footer_row as $row_cell) {
				$row->addCell($row_cell);
			}
			$row->addCell(new Table\Standard_Cell(''));
			$footer->addRow($row);
		}
		return $footer;
	}

	//----------------------------------------------------------------------------------- buildHeader
	/**
	 * @param $header_values Table\Header_Cell[]
	 * @return Table\Head
	 */
	protected function buildHeader(array $header_values) : Table\Head
	{
		$header_row = new Table\Row();
		foreach ($header_values as $header_cell) {
			$header_row->addCell($header_cell);
		}
		$header_row->addCell(new Table\Header_Cell(''));

		$header = new Table\Head();
		$header->addRow($header_row);
		return $header;
	}

}
