<?php
namespace SAF\Framework\Export;

use FPDI;
use SAF\Framework\Export\PDF\Init;
use TCPDF;

require_once __DIR__ . '/../../../../vendor/tcpdf/tcpdf.php';
require_once __DIR__ . '/../../../../vendor/fpdi/fpdi_bridge.php';
require_once __DIR__ . '/../../../../vendor/fpdi/fpdf_tpl.php';
require_once __DIR__ . '/../../../../vendor/fpdi/fpdi.php';

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
 * @var PDF|TCPDF
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
		/** @var $this TCPDF */
		$this->setPrintHeader(false);
		$this->setPrintFooter(false);
	}

}
