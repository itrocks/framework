<?php
namespace ITRocks\Framework\View\Logger;

use ITRocks\Framework;
use ITRocks\Framework\Feature\Validate\Property\Max_Length;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Reflection\Attribute\Property\Multiline;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\No_Escape;
use ITRocks\Framework\View\Logger;

/**
 * Logger entry trait to view output into ITRocks\Framework\Logger\Entry
 */
trait Entry
{

	//--------------------------------------------------------------------------------------- $output
	/** @user_getter userGetOutput */
	#[Getter('getOutput'), Max_Length(100000000), Multiline, Store(false)]
	public string|No_Escape $output;

	//----------------------------------------------------------------------------- deactivateScripts
	/** @noinspection HtmlRequiredTitleElement Don't care */
	protected function deactivateScripts(string $output) : string
	{
		return str_ireplace(
			[
				'<script', '</script>',
				'<link',   '</link>',
				'<head>',  '</head>',
				'auto-redirect',
				'auto-refresh'
			],
			[
				'&lt;script',        '&lt/script>',
				'&lt;link',          '&lt/link&gt;',
				'<pre>&lt;head&gt;', '&lt;/head></pre>',
				'',
				''
			],
			$output
		);
	}

	//------------------------------------------------------------------------------------- getOutput
	protected function getOutput() : string
	{
		/** @var $logger Logger */
		$logger = Session::current()->plugins->get(Logger::class);
		/** @var $this Framework\Logger\Entry|Entry */
		return $logger ? $logger->readFileContent($this) : '';
	}

	//---------------------------------------------------------------------------------------- iFrame
	protected function iFrame(string $output) : string
	{
		return '<iframe data-from="entry-output"></iframe><div id="entry-output">' . $output . '</div>';
	}

	//--------------------------------------------------------------------------------- userGetOutput
	/** @noinspection PhpUnused @user_getter */
	public function userGetOutput() : No_Escape
	{
		return new No_Escape(
			$this->iFrame($this->deactivateScripts($this->getOutput())),
			No_Escape::TYPE_STRING
		);
	}

}
