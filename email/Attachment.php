<?php
namespace ITRocks\Framework\Email;

use ITRocks\Framework\Dao\File;

/**
 * Email attachments are attached or embedded (ie images) files, including multiple versions
 * (HTML / plain) of the mail
 */
class Attachment extends File
{

	//------------------------------------------------------------------------------------- $embedded
	/**
	 * @var boolean
	 */
	public $embedded = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name     string
	 * @param $content  string binary data
	 * @param $embedded boolean
	 */
	public function __construct($name = null, $content = null, $embedded = null)
	{
		parent::__construct(isset($content) ? null : $name);
		if (isset($name) && isset($content)) $this->name     = $name;
		if (isset($content))                 $this->content  = $content;
		if (isset($embedded))                $this->embedded = $embedded;
	}

}
