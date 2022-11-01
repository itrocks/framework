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
	public bool $embedded = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name     string|null
	 * @param $content  string|null binary data
	 * @param $embedded boolean|null
	 */
	public function __construct(string $name = null, string $content = null, bool $embedded = null)
	{
		parent::__construct(isset($content) ? null : $name);
		if (isset($name) && isset($content)) $this->name     = $name;
		if (isset($content))                 $this->content  = $content;
		if (isset($embedded))                $this->embedded = $embedded;
	}

}
