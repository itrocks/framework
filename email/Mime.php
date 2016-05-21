<?php
namespace SAF\Framework\Email;

use Mail_mime;

include_once __DIR__ . '/../../../vendor/pear/mail_mime-decode/Mail/mimePart.php';
include_once __DIR__ . '/../../../vendor/pear/mail_mime-decode/Mail/mime.php';

/**
 * Mime object manager
 *
 * Extension to PEAR Mail_mime : allow manipulation of html images
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
		return $this->_html_images;
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
