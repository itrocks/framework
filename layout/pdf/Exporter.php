<?php
namespace ITRocks\Framework\Layout\PDF;

use ITRocks\Framework\Export\PDF;
use ITRocks\Framework\Layout\Structure\Draw\Rectangle;
use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Has_Structure;
use TCPDF;

/**
 * Export structure to PDF
 */
class Exporter
{
	use Has_Structure;

	//------------------------------------------------------------------------------------------ $pdf
	/**
	 * @var PDF|TCPDF
	 */
	protected $pdf;

	//----------------------------------------------------------------------------------- appendToPdf
	/**
	 * @param $pdf PDF|TCPDF
	 */
	public function appendToPdf(PDF $pdf)
	{
		$this->pdf  = $pdf;
		$margins    = $pdf->getMargins();
		$page_break = $pdf->getAutoPageBreak();
		$pdf->SetMargins(10, 10);
		$pdf->SetAutoPageBreak(false);
		$this->printPages();
		$pdf->SetMargins($margins['left'], $margins['top'], $margins['right']);
		$pdf->SetAutoPageBreak($page_break);
	}

	//---------------------------------------------------------------------------------- printElement
	/**
	 * @param $element Element
	 */
	protected function printElement(Element $element)
	{
		if ($element instanceof Text) {
			$position = $element->top;
			foreach (explode(LF, $element->text) as $text) {
				$this->pdf->Text($element->left, $position, $text);
				$position += $element->font_size;
			}
		}
		elseif ($element instanceof Rectangle) {
			$this->pdf->Rect($element->left, $element->top, $element->width, $element->height);
		}
	}

	//------------------------------------------------------------------------------------ printGroup
	/**
	 * @param $group Group
	 */
	protected function printGroup(Group $group)
	{
		foreach ($group->iterations as $iteration) {
			foreach ($iteration->elements as $element) {
				if ($element instanceof Group) {
					$this->printGroup($element);
				}
				else {
					$this->printElement($element);
				}
			}
		}
	}

	//------------------------------------------------------------------------------------ printPages
	protected function printPages()
	{
		foreach ($this->structure->pages as $page) {
			$this->pdf->AddPage();
			foreach ($page->elements as $element) {
				$this->printElement($element);
			}
			foreach ($page->groups as $group) {
				$this->printGroup($group);
			}
		}
	}

}
