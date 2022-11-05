<?php
namespace ITRocks\Framework\Feature\Export\PDF;

use ITRocks\Framework\Feature\Export\PDF;
use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Initialisation features
 *
 * @extends PDF
 */
trait Init
{

	//------------------------------------------------------------------------------------------ init
	/**
	 * Call this to prepare a standard pdf document and directly begin with $this->AddPage()
	 */
	public function init() : void
	{
		/** @var $this Fpdi|PDF|static */
		$this->Open();
		$this->SetFont(Font::COURIER);
		$this->SetMargins(10, 10, 10);
		$this->SetAutoPageBreak(true, 10);
	}

}
