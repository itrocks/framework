<?php
namespace ITRocks\Framework\Email;

use Mail_mime;

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
		return isset($this->html_images) ? $this->html_images : (
			isset($this->_html_images) ? $this->_html_images :
			trigger_error('Mail_mime::html_image not found', E_USER_ERROR)
		);
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
