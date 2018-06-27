<?php
namespace ITRocks\Framework\Layout\PDF;

use ITRocks\Framework\Export\PDF;
use ITRocks\Framework\Layout\Structure\Draw\Rectangle;
use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Has_Structure;
use ITRocks\Framework\Layout\Structure\Page;
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
	 * Append final structure containing positioned data into PDF
	 *
	 * @param $pdf PDF|TCPDF
	 */
	public function appendToPdf(PDF $pdf)
	{
		$this->pdf  = $pdf;
		$margins    = $pdf->getMargins();
		$page_break = $pdf->getAutoPageBreak();
		$pdf->SetMargins(10, 10);
		$pdf->SetAutoPageBreak(false);
		$this->pages();
		$pdf->SetMargins($margins['left'], $margins['top'], $margins['right']);
		$pdf->SetAutoPageBreak($page_break);
	}

	//------------------------------------------------------------------------------------ background
	/**
	 * Draw page background
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $page Page
	 */
	protected function background(Page $page)
	{
		if ($page->background) {
			$pdf = $this->pdf;
			/** @noinspection PhpUnhandledExceptionInspection Will work */
			$pdf->setSourceFile($page->background->temporary_file_name);
			$import_page = $pdf->importPage(1);
			$pdf->useTemplate($import_page);
		}
	}

	//--------------------------------------------------------------------------------------- element
	/**
	 * Draw an element into current page
	 *
	 * @param $element Element
	 */
	protected function element(Element $element)
	{
		$pdf = $this->pdf;
		if ($element instanceof Text) {
			$position = $element->top;
			foreach (explode(LF, $element->text) as $text) {
				$align = ucfirst(substr($element->text_align, 0, 1)) ?: '';
				$pdf->SetFontSize($pdf->millimetersToPoints($element->font_size));
				$pdf->SetXY($element->left, $position);
				$pdf->Cell($element->width, $element->font_size, $text, 0, 0, $align, false, '', 0, false, 'T', 'M');
				$position += $element->font_size;
			}
		}
		elseif ($element instanceof Rectangle) {
			$this->pdf->Rect($element->left, $element->top, $element->width, $element->height);
		}
	}

	//----------------------------------------------------------------------------------------- group
	/**
	 * Draw a group elements into current page
	 *
	 * @param $group Group
	 */
	protected function group(Group $group)
	{
		foreach ($group->iterations as $iteration) {
			foreach ($iteration->elements as $element) {
				if ($element instanceof Group) {
					$this->group($element);
				}
				else {
					$this->element($element);
				}
			}
		}
	}

	//------------------------------------------------------------------------------------------ page
	/**
	 * Create a new page and draw page elements into it
	 *
	 * @param $page Page
	 */
	protected function page(Page $page)
	{
		$this->pdf->AddPage();
		$this->background($page);
		foreach ($page->elements as $element) {
			$this->element($element);
		}
		foreach ($page->groups as $group) {
			$this->group($group);
		}
	}

	//----------------------------------------------------------------------------------------- pages
	/**
	 * Add and draw structure pages to the current PDF
	 */
	protected function pages()
	{
		foreach ($this->structure->pages as $page) {
			$this->page($page);
		}
	}

}
