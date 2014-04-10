<?php
namespace SAF\Framework\Email;

/**
 * Email attachments are attached or embedded (ie images) files, including multiple versions
 * (HTML / plain) of the mail
 *
 * @package SAF\Framework
 */
class Attachment
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @var string
	 * @max_length 10000000
	 */
	public $content;

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
		if (isset($name))     $this->name = $name;
		if (isset($content))  $this->content = $content;
		if (isset($embedded)) $this->embedded = $embedded;
	}

}
