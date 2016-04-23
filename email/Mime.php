<?php
namespace SAF\Framework\Email;

use Mail_mime;

if (!@include_once(__DIR__ . '/../../../vendor/pear/Mail/mime.php')) {
	@include_once '/usr/share/php/Mail/mime.php';
}

/**
 * Mime object manager
 *
 * Extension to PEAR Mail_mime : allow manipulation of html images
 *
 * Compatibility with two versions of PEAR\Mail_mime :
 * - the one which uses $this->_html_images (before 06/2015)
 * - the other one with $this->html_images (since 06/2015)
 */
class Mime extends Mail_mime
{

	//--------------------------------------------------------------------------------- getHtmlImages
	/**
	 * Gets the html images structure array
	 * If you modify the array, don't forget to use setHtmlImages() to update it into the mail
	 *
	 * @return array
	 */
	public function getHtmlImages()
	{
		return isset($this->_html_images) ? $this->_html_images : $this->html_images;
	}

	//--------------------------------------------------------------------------------- setHtmlImages
	/**
	 * Replace html images with this modified html images array
	 *
	 * @param $html_images array
	 */
	public function setHtmlImages($html_images)
	{
		if (isset($this->_html_images)) {
			$this->_html_images = $html_images;
		}
		if (isset($this->html_images)) {
			$this->html_images = $html_images;
		}
	}

}
