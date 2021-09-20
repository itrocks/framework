<?php
namespace ITRocks\Framework\Layout\PDF;

use ITRocks\Framework\Feature\Export\PDF;
use ITRocks\Framework\Layout\Output;
use ITRocks\Framework\Layout\Structure\Draw;
use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Field\Image;
use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Has_Structure;
use ITRocks\Framework\Layout\Structure\Page;
use ITRocks\Framework\Tools;
use TCPDF;

/**
 * Export structure to PDF
 */
class Exporter implements Output
{
	use Has_Structure;

	//---------------------------------------------------------------------------- $current_font_size
	/**
	 * @var float
	 */
	public $current_font_size;

	//------------------------------------------------------------------------------------------ $pdf
	/**
	 * @var PDF|TCPDF
	 */
	public $pdf;

	//----------------------------------------------------------------------------------- appendToPdf
	/**
	 * Append final structure containing positioned data into PDF
	 *
	 * Values of $pdf and $structure must have been set before calling this
	 */
	public function appendToPdf()
	{
		$pdf          = $this->pdf;
		$cell_padding = $pdf->getCellPaddings();
		$margins      = $pdf->getMargins();
		$page_break   = $pdf->getAutoPageBreak();

		$pdf->SetCellPadding(0);
		$pdf->SetMargins(10, 10);
		$pdf->SetAutoPageBreak(false);
		$this->pages();

		$pdf->setCellPaddings(
			$cell_padding['L'], $cell_padding['T'], $cell_padding['R'], $cell_padding['B']
		);
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

		if ($element instanceof Image) {
			$file  = $element->file;
			$image = Tools\Image::createFromString($file->content);
			[$left, $top, $width, $height] = $image->resizeData($element->width, $element->height);
			$left += $element->left;
			$top  += $element->top;
			if (endsWith(strtolower($file->name), '.eps')) {
				$pdf->ImageEps($file->temporary_file_name, $left, $top, $width, $height);
			}
			elseif (endsWith(strtolower($file->name), '.svg')) {
				$pdf->ImageSVG($file->temporary_file_name, $left, $top, $width, $height);
			}
			else {
				$pdf->Image($file->temporary_file_name, $left, $top, $width, $height);
			}
		}

		elseif ($element instanceof Text) {
			$position = $element->top;
			foreach (explode(LF, $element->text) as $text) {
				$align = ucfirst(substr($element->text_align, 0, 1)) ?: '';
				if ($element->font_size !== $this->current_font_size) {
					$pdf->SetFontSize($pdf->millimetersToPoints($element->font_size));
					$this->current_font_size = $element->font_size;
				}
				if (
					$element->color
					&& ($element->color !== '000000')
					&& ($element->color !== 'rgb(0, 0, 0)')
				) {
					$color = explode(',', mParse($element->color, '(', ')'));
					$pdf->SetTextColor(trim($color[0]), trim($color[1]), trim($color[2]));
				}
				if ($element->font_weight) {
					$pdf->SetFont($pdf->getFontFamily(), 'B');
				}
				$pdf->SetXY($element->left, $position);
				$pdf->Cell($element->width, $element->font_size, $text, 0, 0, $align);
				$position += $element->font_size;
				if ($element->color && ($element->color !== '#000000')) {
					$pdf->SetTextColor();
				}
				if ($element->font_weight) {
					$pdf->SetFont($pdf->getFontFamily());
				}
			}
		}

		elseif ($element instanceof Draw\Horizontal_Line) {
			$this->pdf->Line(
				$element->left, $element->top, $element->left + $element->width, $element->top
			);
		}
		elseif ($element instanceof Draw\Rectangle) {
			$this->pdf->Rect($element->left, $element->top, $element->width, $element->height);
		}
		elseif ($element instanceof Draw\Vertical_Line) {
			$this->pdf->Line(
				$element->left, $element->top, $element->left, $element->top + $element->height
			);
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

	//------------------------------------------------------------------------------------- textWidth
	/**
	 * Get text width calculated by the output generator
	 *
	 * Value of $pdf must have been set before calling this
	 * Apply a ratio to the calculated width, to fix a width error into PDF libraries
	 *
	 * @param $text  string the text
	 * @param $font  string the font name
	 * @param $style string the font style
	 * @param $size  float  the font size, in millimeters
	 * @return float
	 */
	public function textWidth($text, $font = '', $style = null, $size = null)
	{
		$pdf = $this->pdf;
		if ($size && ($this->current_font_size !== $size)) {
			$pdf->SetFontSize($pdf->millimetersToPoints($size));
			$this->current_font_size = $size;
		}
		return $pdf->GetStringWidth($text, $font, $style);
	}

}
