<?php
namespace ITRocks\Framework\Export;

use FPDI;
use ITRocks\Framework\Export\PDF\Init;
use TCPDF;

/**
 * PDF export library
 *
 * Based on FPDI-TCPDF
 * As a FPDI limitation : when you instantiate this, you should declare the variable as PDF|TCPDF
 * to allow IDE to know all available methods and properties. See example below.
 *
 * @example
 * / ** @var $pdf PDF|TCPDF * /
 * $pdf = new PDF();
 */
class PDF extends FPDI
{
	use Init;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * The constructor prepare the document the same way if coming from FPDF or TCPDF : no header line
	 */
	public function __construct()
	{
		/** @noinspection PhpUndefinedMethodInspection exists into TCPDF */
		parent::__construct();
		/** @var $this PDF|TCPDF */
		$this->setPrintHeader(false);
		$this->setPrintFooter(false);
	}

}
