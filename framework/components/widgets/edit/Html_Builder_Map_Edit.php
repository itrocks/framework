<?php
namespace SAF\Framework;

/**
 * Takes a map of objects and build a HTML edit subform containing their data
 */
class Html_Builder_Map_Edit extends Html_Builder_Map
{

	//------------------------------------------------------------------------------------- buildBody
	/**
	 * @return Html_Table_Body
	 */
	protected function buildBody()
	{
		$body = parent::buildBody();
		$row = $this->buildRow(Builder::create($this->class_name));
		$row->addClass('new');
		$body->addRow($row);
		return $body;
	}

	//------------------------------------------------------------------------------------- buildCell
	/**
	 * @param $object object
	 * @return Html_Table_Standard_Cell
	 */
	protected function buildCell($object)
	{
		$property = $this->property;
		$value = $object;
		$input = (new Html_Builder_Type_Edit(
			'', $property->getType()->getElementType(), $value, $property->name
		))->build();
		return new Html_Table_Standard_Cell($input);
	}

	//------------------------------------------------------------------------------------- buildHead
	/**
	 * @return string
	 */
	protected function buildHead()
	{
		$head = parent::buildHead();
		foreach ($head->rows as $row) {
			$row->addCell(new Html_Table_Standard_Cell(''));
		}
		return $head;
	}

	//-------------------------------------------------------------------------------------- buildRow
	/**
	 * @param $object object
	 * @return Html_Table_Row
	 */
	protected function buildRow($object)
	{
		$row = parent::buildRow($object);
		$cell = new Html_Table_Standard_Cell('-');
		$cell->setAttribute('title', '|remove line|');
		$cell->addClass('minus');
		$row->addCell($cell);
		return $row;
	}

}
