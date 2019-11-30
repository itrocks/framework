<?php
namespace ITRocks\Framework\Tools\Wiki;

use Netcarver\Textile\Parser;

/**
 * Textile review B-Appli style, in order to be more ergonomic
 *
 * Replace textile's '_' by '/' for italic
 * Replace textile's '+' by '_' for underlined
 */
class Textile extends Parser
{

	//-------------------------------------------------------------------------------------- $in_code
	/**
	 * true when parsing inside <code>...</code>
	 *
	 * @var boolean
	 */
	protected $in_code = false;

	//----------------------------------------------------------------------------------- $span_depth
	/**
	 * This overrides the Textile::$span_depth property, which declaration has been forgotten
	 *
	 * @var integer
	 */
	public $span_depth;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * This override replace '_' by '/' for em and '+' by '_' for ins
	 * Default is html5
	 *
	 * @param $doc_type string The output document type, either 'xhtml' or 'html5'
	 */
	public function __construct($doc_type = 'html5')
	{
		parent::__construct($doc_type);

		$this->span_tags = [
			'*'  => 'strong',
			'**' => 'b',
			'??' => 'cite',
			'/'  => 'em',
			'//' => 'i',
			'-'  => 'del',
			'%'  => 'span',
			'_'  => 'ins',
			'~'  => 'sub',
			'^'  => 'sup',
		];
	}

	//------------------------------------------------------------------------------------------ code
	/**
	 * Declare we are parsing into code
	 *
	 * @param $text string The input
	 * @return string Processed text
	 */
	protected function code($text)
	{
		$this->in_code = true;
		$result        = parent::code($text);
		$this->in_code = false;
		return $result;
	}

	//------------------------------------------------------------------------------------ encodeHTML
	/**
	 * When parsing into code, do not encode HTML
	 *
	 * @param $string string The string to encode
	 * @param $quotes boolean Encode quotes
	 * @return string Encoded string
	 */
	protected function encodeHTML($string, $quotes = true)
	{
		return $this->in_code ? $string : parent::encodeHTML($string, $quotes);
	}

	//---------------------------------------------------------------------------------- fTextileList
	/**
	 * Constructs a HTML list from a Textile list structure.
	 *
	 * This method is used by Parser::textileLists() to process
	 * found list structures.
	 *
	 * @param  $m array
	 * @return string HTML list
	 */
	protected function fTextileList($m)
	{
		// Ignores "Trying to access array offset on value of type bool in /home/baptiste/PhpStorm/itrocks/itrocks-wiki/vendor/netcarver/textile/src/Netcarver/Textile/Parser.php on line 3089"
		// They test $prev['ml'] but $prev equals false, the first time.
		$error_reporting = error_reporting(E_ALL & ~ E_NOTICE);
		$result = parent::fTextileList($m);
		error_reporting($error_reporting);
		return $result;
	}

	//----------------------------------------------------------------------------------------- parse
	/**
	 * @param $text string
	 * @return string
	 */
	public function parse($text)
	{
		$text   = strReplace(['@@@' => '@&at;', '@@' => '&at;'], $text);
		$result = parent::parse($text);
		return strReplace(['&at;' => '@', '&amp;at;' => '@', '|' => '||'], $result);
	}

	//----------------------------------------------------------------------------------------- spans
	/**
	 * This override replaces '/' by '`' as REGEX separator
	 * Replaces textile spans with their equivalent HTML inline tags.
	 *
	 * @param  $text string The textile document to perform the replacements in
	 * @return string The textile document with spans replaced by their HTML inline equivalents
	 */
	protected function spans($text)
	{
		$span_tags = array_keys($this->span_tags);
		/** @noinspection SpellCheckingInspection copy-paste from netcarver/textile */
		$pnct      = '.,"\'?!;:‹›«»„“”‚‘’';
		$this->span_depth ++;

		if ($this->span_depth <= $this->max_span_depth) {
			foreach ($span_tags as $tag) {
				$content_tag = 'content';
				$tag         = preg_quote($tag);
				$text        = (string)preg_replace_callback(
					"`
					(?P<before>^|(?<=[\s>$pnct\(])|[{[])
					(?P<tag>$tag)(?!$tag)
					(?P<atts>{$this->cls})
					(?!$tag)
					(?::(?P<cite>\S+[^$tag]{$this->regex_snippets['space']}))?
					(?P<$content_tag>[^{$this->regex_snippets['space']}$tag]+|\S.*?[^\s$tag\n])
					(?P<end>[$pnct]*)
					$tag
					(?P<after>$|[\[\]}<]|(?=[$pnct]{1,2}[^0-9]|\s|\)))
					`x" . $this->regex_snippets['mod'],
					[$this, 'fSpan'],
					$text
				);
			}
		}
		$this->span_depth --;
		return $text;
	}

}
