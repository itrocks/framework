<?php
namespace SAF\Framework;
use AopJoinpoint;

require_once "framework/classes/toolbox/Aop.php";

abstract class Html_Session
{

	//--------------------------------------------------------------------------------- postSessionId
	/**
	 * Send session ID as a POST var
	 *
	 * This is done at end of html templates parsing. 
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public static function postSessionId(AopJoinpoint $joinpoint)
	{
		$content = $joinpoint->getReturnedValue();
		// $_POST
		$content = str_replace(
			"</form>",
			"<input type=\"hidden\" name=\"" . session_name() . "\" value=\"" . session_id() . "\">"
				. "</form>",
			$content
		);
		// $_GET
		$i = 0;
		while (($i = strpos($content, 'href="', $i)) !== false) {
			$i += 6;
			$j = strpos($content, '"', $i);
			$link = substr($content, $i, $j - $i);
			$sep = strpos($link, "?") ? "&" : "?"; 
			$content = substr($content, 0, $j) . $sep
				. session_name() . "=" . session_id()
				. substr($content, $j);
		}
		// done
		$joinpoint->setReturnedValue($content);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * always add session id at end of html documents parsing
	 */
	public static function register()
	{
		aop_add_after(
			__NAMESPACE__ . "\\Html_Template->parse()",
			array(__CLASS__, "postSessionId")
		);
	}

}

Html_Session::register();
