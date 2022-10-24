<?php
namespace ITRocks\Framework\Html;

/**
 * HTML Parser
 */
class Parser
{

	//-------------------------------------------------------------------- Tag positionning constants
	const AFTER  = 'after';
	const BEFORE = 'before';

	//--------------------------------------------------------------------------------------- $buffer
	/**
	 * The page html source
	 *
	 * @var string
	 */
	public $buffer;

	//----------------------------------------------------------------------------------------- $head
	/**
	 * Saved <head> element html source
	 *
	 * @var string
	 */
	public $head;

	//----------------------------------------------------------------------------------- $links_from
	/**
	 * Replaced texts for html, Location and Set-Cookies headers
	 *
	 * @var string[]
	 */
	private $links_from = [
		'http://{site_url}//',
		'http://{site_url}/',
		' domain={site_domain};',
		' path=/',
		'/{site_path}'
	];

	//------------------------------------------------------------------------------------- $links_to
	/**
	 * Replacement texts for html, Location and Set-Cookies headers
	 *
	 * @var string[]
	 */
	private $links_to = [
		'/',
		'/',
		'',
		' path=/{proxy_path}',
		'/{proxy_path}/{site_path}'
	];

	//----------------------------------------------------------------------------------- $proxy_path
	/**
	 * Path of the replacement page into the proxy server
	 *
	 * @example 'tsm'
	 * @var string
	 */
	public $proxy_path;

	//-------------------------------------------------------------------------------------- $scripts
	/**
	 * Saved <script> elements html source
	 *
	 * @var string[]
	 */
	public $scripts;

	//---------------------------------------------------------------------------------- $site_domain
	/**
	 * The original site domain name
	 *
	 * @example 'automotoboutic.com'
	 * @var string
	 */
	public $site_domain;

	//------------------------------------------------------------------------------------ $site_path
	/**
	 * The original site page path
	 *
	 * @example tapis-auto-sur-mesure-
	 * @var string
	 */
	public $site_path;

	//------------------------------------------------------------------------------------- $site_url
	/**
	 * The original site base URL
	 *
	 * @example 'www.automotoboutic.com'
	 * @var string
	 */
	public $site_url;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $html_buffer string
	 */
	public function __construct($html_buffer = '')
	{
		$this->buffer = $html_buffer;
	}

	//--------------------------------------------------------------------------------- addAttributes
	/**
	 * Adds attributes to all elements matching $selector into the buffer
	 *
	 * @param $selector   string the selector for elements
	 * @param $attributes string[] added attributes $attribute_value = string[$attribute_name]
	 */
	public function addAttributes($selector, array $attributes)
	{
		$i = 0;
		while (($i = $this->selectorPos($selector, $i)) !== false) {
			$i = strpos($this->buffer, '>', $i);
			foreach ($attributes as $attribute_name => $attribute_value) {
				$this->buffer = substr($this->buffer, 0, $i) . SP
					. $attribute_name . '=' . Q . $attribute_value . Q
					. substr($this->buffer, $i);
			}
		}
	}

	//------------------------------------------------------------------------------------ closingTag
	/**
	 * Search the next closing tag
	 *
	 * @param $tag string the name of the opened tag
	 * @param $i   integer the position of the opened '<tag>' into buffer
	 * @param $at  string 'after' for the position after, 'before' for start position of the closing
	 *             tag
	 * @return integer the position of the closed '</tag>' into buffer
	 */
	public function closingTag($tag, $i, $at = self::AFTER)
	{
		$j = strpos($this->buffer, '>', $i) + 1;
		if (in_array($tag, ['img', 'input', 'meta'])) {
			return ($at === self::AFTER) ? $j : $i;
		}
		// skip identical tags, recursively, until they are all closed
		$skip = 1;
		do {
			$j2 = $this->tagPos($tag, $j);
			$j = strpos($this->buffer, '</' . $tag . '>', $j);
			if (($at === self::AFTER) && ($j !== false)) {
				$j += strlen($tag) + 3;
			}
			if (($j2 !== false) && ($j2 < $j)) {
				$j = strpos($this->buffer, '>', $j2);
				$skip ++;
			}
			elseif ($skip) {
				$skip --;
				if ($skip && ($at === self::BEFORE) && ($j !== false)) {
					$j = strpos($this->buffer, '>', $j);
				}
			}
		} while ($skip && ($j !== false));
		if ($j === false) {
			trigger_error('Fatal error : could not found closing ' . $tag, E_USER_ERROR);
		}
		return $j;
	}

	//-------------------------------------------------------------------------------- constructLinks
	/**
	 * Constructs search-and-replace string using proxy and original site information
	 *
	 * @param $proxy_path  string
	 * @param $site_domain string
	 * @param $site_url    string
	 * @param $site_path   string
	 */
	public function constructLinks(
		$proxy_path = null, $site_domain = null, $site_url = null, $site_path = null
	) {
		if (isset($proxy_path))  $this->proxy_path  = $proxy_path;
		if (isset($site_domain)) $this->site_domain = $site_domain;
		if (isset($site_url))    $this->site_url    = $site_url;
		if (isset($site_path))   $this->site_path   = $site_path;
		foreach ($this->links_from as $key => $value) {
			$this->links_from[$key] = str_replace(
				['{proxy_path}', '{site_url}', '{site_domain}', '{site_path}'],
				[$this->proxy_path, $this->site_url, $this->site_domain, $this->site_path],
				$value
			);
		}
		foreach ($this->links_to as $key => $value) {
			$this->links_to[$key] = str_replace(
				['{proxy_path}', '{site_url}', '{site_domain}', '{site_path}'],
				[$this->proxy_path, $this->site_url, $this->site_domain, $this->site_path],
				$value
			);
		}
	}

	//-------------------------------------------------------------------------------------- contains
	/**
	 * Returns true if the buffer contains given selector
	 *
	 * @param $selector string
	 * @return boolean
	 */
	public function contains($selector)
	{
		return ($this->selectorPos($selector) !== false);
	}

	//------------------------------------------------------------------------------------------- cut
	/**
	 * Cuts a selected element into and keep only its html source into buffer
	 *
	 * @example '.mainColumn.loadSocialScript' will keep the source code of this div only in buffer
	 *          and remove everything before '<div' and after '</div>'
	 * @example 'div.mainColumn.loadSocialScript:content' does the same without keeping the '<div'
	 *          and the '</div>'
	 * @param $selector  string
	 */
	public function cut($selector)
	{
		$parts = $this->selectorParts($selector);
		$content_only = isset($parts[':']['content']);
		$tag = $this->partsTag($parts);
		$i = $this->selectorPos($selector);
		if ($content_only) {
			$i = strpos($this->buffer, '>', $i) + 1;
		}
		$j = $this->closingTag($tag, $i, $content_only ? self::BEFORE : self::AFTER);
		$this->buffer = substr($this->buffer, $i, $j - $i);
	}

	//------------------------------------------------------------------------------------- cutModule
	/**
	 * Cuts a Prestashop module and keep only its html source into buffer :
	 * only HTML code between <!-- MODULE {module} --> and <!-- /MODULE {module} --> is kept
	 *
	 * If you need to get them back, call saveHeader() and saveScripts() before cutting html source
	 *
	 * @example 'Custom products'
	 * @param $module string
	 */
	public function cutModule($module)
	{
		$i = strpos($this->buffer, '<!-- MODULE ' . $module . ' -->') + strlen($module) + 16;
		$j = strpos($this->buffer, '<!-- /MODULE ' . $module . ' -->');
		$this->buffer = substr($this->buffer, $i, $j - $i);
	}

	//-------------------------------------------------------------------------- debugDisplayFullInfo
	/**
	 * Displays debugging information
	 */
	public function debugDisplayFullInfo()
	{
		echo '<pre>' . htmlentities($this->buffer) . '</pre>';
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Gets something from the buffer
	 * Each argument is a selector to search the information from, starting from the previous selector
	 * if the selector contains no ':...', the full element and content html code will be returned
	 * if the selector contains ':content', the element content html will be returned
	 * if the selector contains ':attributeName', the element attribute value will be returned
	 *
	 * @example 'string.title=Configuration :', 'li:alt'
	 * @param $string string
	 * @return string|null
	 */
	public function get($string)
	{
		$pos      = 0;
		$selector = null;
		foreach (func_get_args() as $selector) {
			$pos = is_numeric($selector) ? $selector : $this->selectorPos($selector, $pos);
			if ($pos === false) {
				return null;
			}
		}
		if (!isset($selector)) {
			return null;
		}
		$parts = $this->selectorParts($selector);
		$end   = $this->closingTag($this->partsTag($parts), $pos, self::BEFORE);
		if (isset($parts[':']['content'])) {
			$pos = strpos($this->buffer, '>', $pos) + 1;
		}
		elseif ($parts[':']) {
			$attribute = reset($parts[':']);
			$end       = strpos($this->buffer, '>', $pos);
			$p1        = strpos($this->buffer, $attribute . '=' . Q, $pos);
			$p2        = strpos($this->buffer, $attribute . '=' . DQ, $pos);
			if (($p1 !== false) && ($p1 < $end)) {
				$pos = $p1 + strlen($attribute) + 2;
				$end = strpos($this->buffer, Q, $pos);
			}
			elseif (($p2 !== false) && ($p2 < $end)) {
				$pos = $p2 + strlen($attribute) + 2;
				$end = strpos($this->buffer, Q, $pos);
			}
		}
		else {
			$end = strpos($this->buffer, '>', $end) + 1;
		}
		return substr($this->buffer, $pos, $end - $pos);
	}

	//-------------------------------------------------------------------------------- headersToProxy
	/**
	 * Change Location and Set-Cookie headers to refer to the proxy instead of the original web site
	 *
	 * @param $headers string[]
	 */
	public function headersToProxy(array &$headers)
	{
		foreach ($headers as $key => $value) {
			if (substr($value, 0, 10) === 'Location: ') {
				$headers[$key] = str_replace($this->links_from, $this->links_to, $value);
			}
			elseif (substr($value, 0, 12) === 'Set-Cookie: ') {
				$headers[$key] = str_replace($this->links_from, $this->links_to, $value);
			}
		}
	}

	//------------------------------------------------------------------------------------------ into
	/**
	 * Stores html source buffer into some elements
	 *
	 * @example into('html', 'body', 'div#content') to add some elements around a cut buffer
	 */
	public function into()
	{
		$page_begin = [];
		$page_end   = [];
		foreach (func_get_args() as $arg) {
			$parts = $this->selectorParts($arg);
			$tag = $this->partsTag($parts);
			array_push($page_begin, $this->partsToHtml($parts));
			if ($tag == 'html') {
				array_push($page_begin, $this->head);
			}
			array_unshift($page_end, '</' . $tag . '>');
			if ($tag == 'body') {
				array_unshift($page_end, join(LF, $this->scripts));
			}
		}
		$this->buffer = join(LF, $page_begin) . LF . $this->buffer . LF . join(LF, $page_end);
	}

	//---------------------------------------------------------------------------------- linksToProxy
	/**
	 * Changes links contained into the page to refer to the proxy instead of the original web site
	 */
	public function linksToProxy()
	{
		$this->buffer = str_replace($this->links_from, $this->links_to, $this->buffer);
	}

	//----------------------------------------------------------------------------------------- merge
	/**
	 * Merge html code at a buffer element end
	 *
	 * @example merge('head', '<head><title>Hello</title><head>' will add '<title>Hello</title>' to
	 *          the buffer head.
	 * @param $selector    string the element selector where the merged html will be appended into
	 * @param $merged_html string the merged html
	 */
	public function merge($selector, $merged_html)
	{
		$tag = $this->partsTag($this->selectorParts($selector));
		// remove merged html before / after element
		if (
			(
				(($i = strpos($merged_html, '<' . $tag . '>')) !== false)
				|| (($i = strpos($merged_html, '<' . $tag . SP)) !== false)
			)
			&& ($j = strrpos($merged_html, '</' . $tag . '>', $i)) !== false
		) {
			$i = strpos($merged_html, '>', $i) + 1;
			$merged_html = substr($merged_html, $i, $j - $i);
		}
		// append merged html at the end of buffer selected elements contents
		$i = 0;
		while (($i = $this->selectorPos($selector, $i)) !== false) {
			$i = $this->closingTag($tag, $i, self::BEFORE);
			$this->buffer = substr($this->buffer, 0, $i) . $merged_html . substr($this->buffer, $i);
		}
	}

	//-------------------------------------------------------------------------------------- partsTag
	/**
	 * Returns element name from parts. 'div' will be the default if there is no element part.
	 *
	 * @param $parts array string['#'|'.'|':'][integer]
	 * @return string
	 */
	private function partsTag(array $parts)
	{
		return isset($parts['<']) ? reset($parts['<']) : 'div';
	}

	//----------------------------------------------------------------------------------- partsToHtml
	/**
	 * @param $parts array string['#'|'.'|':'][integer]
	 * @return string html code for parts
	 */
	private function partsToHtml(array $parts)
	{
		$attributes = ['#' => 'id', DOT => 'class'];
		$html = '<' . $this->partsTag($parts);
		foreach ($parts as $what => $list) if (isset($attributes[$what])) {
			$html .= SP . $attributes[$what] . '=' . DQ . join(SP, $list) . DQ;
		}
		$html .= '>';
		return $html;
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove some elements from the html page source buffer
	 * Each parameter is as selector for elements to be removed from buffer.
	 *
	 * TODO unnamed element should be free instead of div
	 *
	 * @example remove('div.classed', '#id')
	 * @param $selectors       string|string[]
	 * @param $until_selectors string|string[]
	 * @return integer index of the last removed element
	 */
	public function remove($selectors, $until_selectors = null)
	{
		if (!is_array($selectors)) {
			$selectors = [$selectors];
		}
		if (isset($until_selectors) && !is_array($until_selectors)) {
			$until_selectors = [$until_selectors];
		}
		$last_i = false;
		foreach ($selectors as $k => $selector) {
			$parts = $this->selectorParts($selector);
			$tag = $this->partsTag($parts);
			$i = 0;
			while (($i = $this->selectorPos($selector, $i)) !== false) {
				$j = isset($until_selectors)
					? $this->selectorPos($until_selectors[$k], $i + 1)
					: $this->closingTag($tag, $i);
				// remove element from html source
				if (isset($parts[':']) && in_array('content', $parts[':'])) {
					$i = strpos($this->buffer, '>', $i) + 1;
					$j -= strlen($tag) + 3;
				}
				$this->buffer = substr($this->buffer, 0, $i) . substr($this->buffer, $j);
				$last_i = $i;
			}
		}
		return $last_i;
	}

	//--------------------------------------------------------------------------------------- replace
	/**
	 * Replace an element by a new content
	 *
	 * @param $selector       string The element selector may contain ':content' to replace the element
	 *                        content and not the element itself.
	 * @param $replacement    string replacement html string
	 * @param $until_selector string force end of $selector to the beginning of this other selector
	 * @return integer index of the replaced element
	 */
	public function replace($selector, $replacement, $until_selector = null)
	{
		$i = $this->remove($selector, $until_selector);
		if ($i !== false) {
			$this->buffer = substr($this->buffer, 0, $i) . $replacement . substr($this->buffer, $i);
		}
		return $i;
	}

	//------------------------------------------------------------------------------------ saveHeader
	/**
	 * Saves the page <head> element in order to get it back after <html>
	 *
	 * This should be called before any cut*() call
	 * To get the header back, call into('html')
	 */
	public function saveHeader()
	{
		$i = strpos($this->buffer, '<head');
		$j = strpos($this->buffer, '</head>') + 7;
		$this->head = substr($this->buffer, $i, $j - $i);
	}

	//----------------------------------------------------------------------------------- saveScripts
	/**
	 * Saves the page <script> elements in order to get them back before </body>
	 *
	 * This should be called before any cut*() call
	 * To get the scripts back, call into('body[.some_classes#or_id]')
	 */
	public function saveScripts()
	{
		$this->scripts = [];
		$i = strpos($this->buffer, '</head>');
		while (($i = strpos($this->buffer, '<script', $i)) !== false) {
			$j = strpos($this->buffer, '</script>', $i) + 9;
			$this->scripts[] = substr($this->buffer, $i, $j - $i);
			$i = $j;
		}
	}

	//--------------------------------------------------------------------------------- selectorParts
	/**
	 * Change css-like element selector into a selector parts array
	 *
	 * @example 'div.class#name:content' will become
	 *   ['<' => ['div'], '.' => ['class'], '#' => ['name'], ':' => ['content']]
	 * @param $selector string
	 * @return array string[][]
	 */
	private function selectorParts($selector)
	{
		$parts = [];
		$length = strlen($selector);
		$what = '<';
		$begin = 0;
		$ignore = false;
		for ($pos = 0; $pos < $length; $pos++) {
			if (str_contains('#.:[]', $selector[$pos])) {
				if ($pos) {
					$content = substr($selector, $begin, $pos - $begin);
					if ($what === '[') {
						if ($selector[$pos] == ']') {
							[$key, $value] = explode('=', $content);
							if (
								(($value[0] == Q) && ($value[strlen($value) - 1] == Q))
								|| (($value[0] == DQ) && ($value[strlen($value) - 1] == DQ))
							) {
								$value = substr($value, 1, -1);
							}
							$parts[$what][$key] = $value;
						}
						else {
							$ignore = true;
						}
					}
					elseif ($what !== ']') {
						$parts[$what][$content] = $content;
					}
				}
				if ($ignore) {
					$ignore = false;
				}
				else {
					$begin = $pos + 1;
					$what = $selector[$pos];
				}
			}
		}
		if ($what !== ']') {
			$content = substr($selector, $begin, $pos - $begin);
			$parts[$what][$content] = $content;
		}
		return $parts;
	}

	//----------------------------------------------------------------------------------- selectorPos
	/**
	 * Search next position of the selector into buffer
	 *
	 * @param $selector string ie 'div#id.class1.class2'
	 * @param $i        integer starting position for search
	 * @return integer|boolean false if not found
	 */
	public function selectorPos($selector, $i = 0)
	{
		$found = true;
		$attributes = ['#' => 'id', DOT => 'class', '[' => ''];
		$parts = $this->selectorParts($selector);
		$tag = $this->partsTag($parts);
		while (($i = $this->tagPos($tag, $i)) !== false) {
			// check attributes into <element ...>
			$j = strpos($this->buffer, '>', $i);
			$buffer = substr($this->buffer, $i, $j - $i + 1);
			// check attributes
			foreach ($parts as $what => $list) if (isset($attributes[$what])) {
				$found = true;
				$attr = ($what == '[') ? null : $attributes[$what];
				foreach ($list as $name => $part) {
					if (isset($attr)) $name = $attr;
					if ($name == 'content') {
						$ci = $j + 1;
						$cj = $this->closingTag($tag, $i, self::BEFORE);
						$content = substr($this->buffer, $ci, $cj - $ci);
						if ($content !== $part) {
							$found = false;
							break 2;
						}
					}
					elseif (
						(
							(($i2 = strpos($buffer, SP . $name . '=' . DQ))          === false)
							|| (($j2 = strpos($buffer, DQ, $i2 + strlen($name) + 3)) === false)
							|| !str_contains(substr($buffer, $i2, $j2 - $i2), $part)
						)
						&&
						(
							(($i2 = strpos($buffer, SP . $name . '=' . Q))          === false)
							|| (($j2 = strpos($buffer, Q, $i2 + strlen($name) + 3)) === false)
							|| !str_contains(substr($buffer, $i2, $j2 - $i2), $part)
						)
					) {
						$found = false;
						break 2;
					}
				}
			}
			// if there is no attribute to check or all attributes where found : returns found selector
			if ($found) {
				return $i;
			}
			$i = $j + 1;
		}
		return false;
	}

	//---------------------------------------------------------------------------------------- tagPos
	/**
	 * Search next position of the tag into buffer
	 *
	 * @param $tag string ie 'div'
	 * @param $i   integer starting position for search
	 * @return integer|boolean false if not found
	 */
	public function tagPos($tag, $i = 0)
	{
		$i1 = strpos($this->buffer, '<' . $tag . '>', $i);
		$i2 = strpos($this->buffer, '<' . $tag . SP, $i);
		if (($i1 === false) || (($i2 !== false) && ($i2 < $i1))) {
			$i1 = $i2;
		}
		return $i1;
	}

}
