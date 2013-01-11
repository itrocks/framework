<?php
namespace SAF\Framework;
use AopJoinpoint;

require_once "framework/core/toolbox/Aop.php";

abstract class Html_Session implements Plugin
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
		if (!$joinpoint->getObject()->getParameter("is_included")) {
			$content = $joinpoint->getReturnedValue();
			// $_POST
			$content = str_replace(
				"</form>",
				"<input type=\"hidden\" name=\"" . session_name() . "\" value=\"" . session_id() . "\">"
					. "</form>",
				$content
			);
			// $_GET
			$links = array("action=", "href=", "location=");
			$quotes = array("'", '"');
			foreach ($links as $link) {
				foreach ($quotes as $quote) {
					$i = 0;
					while (($i = strpos($content, $link . $quote, $i)) !== false) {
						$i += strlen($link) + 1;
						$j = strpos($content, $quote, $i);
						$sep = strpos(substr($content, $i, $j - $i), "?") ? "&" : "?";
						$add = $sep . session_name() . "=" . session_id();
						$content = substr($content, 0, $j) . $add . substr($content, $j);
						$i += strlen($add) + 1;
					}
				}
			}
			// done
			$joinpoint->setReturnedValue($content);
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * always add session id at end of html documents parsing
	 */
	public static function register()
	{
		ini_set("session.use_cookies", false);
		ini_set("session.use_only_cookies", false);
		Aop::add("after",
			__NAMESPACE__ . "\\Html_Template->parse()",
			array(__CLASS__, "postSessionId")
		);
	}

}
