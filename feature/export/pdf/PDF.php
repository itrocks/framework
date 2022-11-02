<?php
namespace ITRocks\Framework\Feature\Export;

use Exception;
use ITRocks\Framework\Feature\Export\PDF\Init;
use setasign\Fpdi\Tcpdf\Fpdi;
use TCPDF;
use TCPDF_STATIC;

// TODO LOW This is for a warning in php 7.3+. Remove it when tcpdf is compatible
$error_reporting = error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED);

// A patch because composer does not want to compile fpdi-pdf-parser's autoloader
if (file_exists(__DIR__ . '/../../../../../vendor/setasign/fpdi-pdf-parser/src')) {
	/** @noinspection PhpIncludeInspection fpdi-pdf-parser : file_exists */
	require_once __DIR__ . '/../../../../../vendor/setasign/fpdi-pdf-parser/src/autoload.php';
}

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
class PDF extends Fpdi
{
	use Init;

	//------------------------------------------------------------------- MILLIMETERS_TO_POINTS_RATIO
	const MILLIMETERS_TO_POINTS_RATIO = 2.5;

	//------------------------------------------------------------------------------ $last_cell_max_y
	/**
	 * After a MultiCell / writeHTMLCell, you may know until which y position the writing went, and
	 * calculate the rendered cell height.
	 *
	 * @var float
	 */
	public float $last_cell_max_y = .0;

	//-------------------------------------------------------------------------------------------- Ln
	/**
	 * @inheritdoc
	 */
	public function Ln($h = '', $cell = false)
	{
		parent::Ln($h, $cell);
		if ($this->y > $this->last_cell_max_y) {
			$this->last_cell_max_y = $this->y;
		}
	}

	//------------------------------------------------------------------------------------- MultiCell
	/**
	 * @inheritdoc
	 */
	public function MultiCell(
		$w, $h, $txt, $border = 0, $align = 'J', $fill = false, $ln = 1, $x = '', $y = '',
		$reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'T',
		$fitcell = false
	) : int
	{
		$this->last_cell_max_y = $y ?: $this->y;
		return parent::MultiCell(
			$w, $h, $txt, $border, $align, $fill, $ln, $x, $y, $reseth, $stretch, $ishtml, $autopadding,
			$maxh, $valign, $fitcell
		);
	}

	//---------------------------------------------------------------------------------------- Output
	/**
	 * Overrides TCPDF::Output
	 *
	 * - Disable replacement of spaces by underscores for $name
	 *
	 * Must be exactly the same as TCPDF::Output, only itrocks-commented parts have been replaced
	 *
	 * @param $name string
	 * @param $dest string
	 * @return string
	 * @throws Exception
	 */
	public function Output($name = 'doc.pdf', $dest = 'I') : string
	{
		//Output PDF to some destination
		//Finish document if necessary
		if ($this->state < 3) {
			$this->Close();
		}
		//Normalize parameters
		if (is_bool($dest)) {
			$dest = $dest ? 'D' : 'F';
		}
		$dest = strtoupper($dest);
		// itrocks : here is the disabled code
		/*
		if ($dest[0] !== 'F') {
			$name = preg_replace('/[\s]+/', '_', $name);
			$name = preg_replace('/[^a-zA-Z0-9_\.-]/', '', $name);
		}
		*/
		if ($this->sign) {
			// *** apply digital signature to the document ***
			// get the document content
			$pdfdoc = $this->getBuffer();
			// remove last newline
			$pdfdoc = substr($pdfdoc, 0, -1);
			// remove filler space
			$byterange_string_len = strlen(TCPDF_STATIC::$byterange_string);
			// define the ByteRange
			$byte_range = array();
			$byte_range[1] = strpos($pdfdoc, TCPDF_STATIC::$byterange_string) + $byterange_string_len + 10;
			$byte_range[2] = $byte_range[1] + $this->signature_max_length + 2;
			$byte_range[3] = strlen($pdfdoc) - $byte_range[2];
			$pdfdoc = substr($pdfdoc, 0, $byte_range[1]).substr($pdfdoc, $byte_range[2]);
			// replace the ByteRange
			$byterange = sprintf('/ByteRange[0 %u %u %u]', $byte_range[1], $byte_range[2], $byte_range[3]);
			$byterange .= str_repeat(' ', ($byterange_string_len - strlen($byterange)));
			$pdfdoc = str_replace(TCPDF_STATIC::$byterange_string, $byterange, $pdfdoc);
			// write the document to a temporary folder
			$tempdoc = TCPDF_STATIC::getObjFilename('doc', $this->file_id);
			$f = TCPDF_STATIC::fopenLocal($tempdoc, 'wb');
			if (!$f) {
				$this->Error('Unable to create temporary file: '.$tempdoc);
			}
			$pdfdoc_length = strlen($pdfdoc);
			fwrite($f, $pdfdoc, $pdfdoc_length);
			fclose($f);
			// get digital signature via openssl library
			$tempsign = TCPDF_STATIC::getObjFilename('sig', $this->file_id);
			if (empty($this->signature_data['extracerts'])) {
				openssl_pkcs7_sign($tempdoc, $tempsign, $this->signature_data['signcert'], array($this->signature_data['privkey'], $this->signature_data['password']), array(), PKCS7_BINARY | PKCS7_DETACHED);
			}
			else {
				openssl_pkcs7_sign($tempdoc, $tempsign, $this->signature_data['signcert'], array($this->signature_data['privkey'], $this->signature_data['password']), array(), PKCS7_BINARY | PKCS7_DETACHED, $this->signature_data['extracerts']);
			}
			// read signature
			$signature = file_get_contents($tempsign);
			// extract signature
			$signature = substr($signature, $pdfdoc_length);
			$signature = substr($signature, strpos($signature, "%%EOF\n\n------") + 13);
			$tmparr = explode("\n\n", $signature);
			$signature = $tmparr[1];
			// decode signature
			$signature = base64_decode(trim($signature));
			// add TSA timestamp to signature
			$signature = $this->applyTSA($signature);
			// convert signature to hex
			$signature = current(unpack('H*', $signature));
			$signature = str_pad($signature, $this->signature_max_length, '0');
			// Add signature to the document
			$this->buffer = substr($pdfdoc, 0, $byte_range[1]).'<'.$signature.'>'.substr($pdfdoc, $byte_range[1]);
			$this->bufferlen = strlen($this->buffer);
		}
		switch($dest) {
			case 'I':
				// Send PDF to the standard output
				if (ob_get_contents()) {
					$this->Error('Some data has already been output, can\'t send PDF file');
				}
				if (php_sapi_name() === 'cli') {
					echo $this->getBuffer();
					break;
				}
				// send output to a browser
				header('Content-Type: application/pdf');
				if (headers_sent()) {
					$this->Error('Some data has already been output to browser, can\'t send PDF file');
				}
				header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
				//header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
				header('Pragma: public');
				header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
				header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
				header('Content-Disposition: inline; filename="'.basename($name).'"');
				TCPDF_STATIC::sendOutputData($this->getBuffer(), $this->bufferlen);
				break;
			case 'D':
				// download PDF as file
				$this->sendHeadersToBrowser($name);
				TCPDF_STATIC::sendOutputData($this->getBuffer(), $this->bufferlen);
				break;
			case 'F':
			case 'FI':
			case 'FD':
				// save PDF to a local file
				$f = TCPDF_STATIC::fopenLocal($name, 'wb');
				if (!$f) {
					$this->Error('Unable to create output file: '.$name);
				}
				fwrite($f, $this->getBuffer(), $this->bufferlen);
				fclose($f);
				if ($dest === 'FI') {
					// send headers to browser
					header('Content-Type: application/pdf');
					header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
					//header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
					header('Pragma: public');
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
					header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
					header('Content-Disposition: inline; filename="'.basename($name).'"');
					TCPDF_STATIC::sendOutputData(file_get_contents($name), filesize($name));
				}
				elseif ($dest === 'FD') {
					// send headers to browser
					$this->sendHeadersToBrowser($name);
					TCPDF_STATIC::sendOutputData(file_get_contents($name), filesize($name));
				}
				break;
			case 'E':
				// return PDF as base64 mime multi-part email attachment (RFC 2045)
				$retval = 'Content-Type: application/pdf;'."\r\n";
				$retval .= ' name="'.$name.'"'."\r\n";
				$retval .= 'Content-Transfer-Encoding: base64'."\r\n";
				$retval .= 'Content-Disposition: attachment;'."\r\n";
				$retval .= ' filename="'.$name.'"'."\r\n\r\n";
				$retval .= chunk_split(base64_encode($this->getBuffer()));
				return $retval;
			case 'S':
				// returns PDF as a string
				return $this->getBuffer();
			default:
				$this->Error('Incorrect output destination: '.$dest);
		}
		return '';
	}

	//----------------------------------------------------------------------------------- __construct
	/**
	 * The constructor prepare the document the same way if coming from FPDF or TCPDF : no header line
	 */
	public function __construct()
	{
		parent::__construct();
		/** @var $this PDF|TCPDF */
		$this->setPrintHeader(false);
		$this->setPrintFooter(false);
	}

	//--------------------------------------------------------------------------- millimetersToPoints
	/**
	 * @param $millimeters float
	 * @return float
	 */
	public function millimetersToPoints(float $millimeters) : float
	{
		return static::MILLIMETERS_TO_POINTS_RATIO * $millimeters;
	}

	//----------------------------------------------------------------------------- downloadPDFAsFile
	/**
	 * @param $name string
	 * @throws Exception
	 */
	protected function sendHeadersToBrowser(string $name)
	{
		if (ob_get_contents()) {
			$this->Error('Some data has already been output, can\'t send PDF file');
		}
		header('Content-Description: File Transfer');
		if (headers_sent()) {
			$this->Error('Some data has already been output to browser, can\'t send PDF file');
		}
		header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
		//header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
		header('Pragma: public');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		// force download dialog
		if (!str_contains(php_sapi_name(), 'cgi')) {
			header('Content-Type: application/force-download');
			header('Content-Type: application/octet-stream', false);
			header('Content-Type: application/download', false);
			header('Content-Type: application/pdf', false);
		}
		else {
			header('Content-Type: application/pdf');
		}
		// use the Content-Disposition header to supply a recommended filename
		header('Content-Disposition: attachment; filename="' . basename($name) . '"');
		header('Content-Transfer-Encoding: binary');
	}

	//--------------------------------------------------------------------------------------- toColor
	/**
	 * @param $color string
	 * @return integer[] [$red, $green, $blue]
	 */
	public static function toColor(string $color) : array
	{
		if (str_starts_with($color, '#')) {
			if (strlen($color) === 4) {
				$color = '#' . $color[1] . $color[1] . $color[2] . $color[2] . $color[3] . $color[3];
			}
			if (strlen($color) === 7) {
				$color = hexdec(substr($color, 1));
				$color = [
					($color & 0xff0000) >> 16,
					($color & 0x00ff00) >> 8,
					($color & 0x0000ff)
				];
			}
		}
		return $color;
	}

}

error_reporting($error_reporting);
