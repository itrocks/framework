<?php
namespace ITRocks\Framework\Locale;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Sql;
use ITRocks\Framework\Feature\List_\Search_Parameters_Parser\Wildcard;

/**
 * Translations give the programmer translations features, and store them into cache
 *
 * TODO : translations maintainer : only one text per context, and only one translation per context
 */
class Translator
{

	//------------------------------------------------------------- MAX_WILDCARD_REVERSE_TRANSLATIONS
	const MAX_WILDCARD_REVERSE_TRANSLATIONS = 100;

	//------------------------------------------------------------- TOO_MANY_RESULTS_MATCH_YOUR_INPUT
	const TOO_MANY_RESULTS_MATCH_YOUR_INPUT = 'too many results match your input';

	//---------------------------------------------------------------------------------------- $cache
	/**
	 * @var array[] string[][] string $translation[string $text][string $context]
	 */
	protected $cache = [];

	//------------------------------------------------------------------------------------- $language
	/**
	 * @var string
	 */
	public $language;

	//--------------------------------------------------------------------------------- $last_context
	/**
	 * The context chosen by the last call to chooseTranslation()
	 *
	 * @var string
	 */
	public $last_context;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $language string
	 */
	public function __construct($language = null)
	{
		if (isset($language)) {
			$this->language = $language;
		}
	}

	//----------------------------------------------------------------------------------- applyPlural
	/**
	 * @param $translations string[]
	 * @param $translation  string
	 * @param $context      string
	 * @return boolean true if plural has been applied
	 */
	protected function applyPlural(array &$translations, &$translation, &$context)
	{
		$plural = (strpos($context, '*') !== false);
		if ($plural) {
			if (isset($translations['*'])) {
				$translation = $translations['*'];
			}
			$filter_translations = false;
			foreach (array_keys($translations) as $translation_context) {
				if (strpos($translation_context, '*') !== false) {
					$filter_translations = true;
					break;
				}
			}
			if ($filter_translations) {
				$filtered_translations = [];
				foreach ($translations as $translation_context => $translation_text) {
					if (strpos($translation_context, '*') !== false) {
						$filtered_translations[str_replace('*', '', $translation_context)] = $translation_text;
					}
				}
				$translations = $filtered_translations;
			}
			$context = str_replace('*', '', $context);
		}
		return $plural;
	}

	//----------------------------------------------------------------------------- chooseTranslation
	/**
	 * Chooses the translation which context matches the most acutely the given context
	 *
	 * Translations context can be class, interface, trait names.
	 * $context argument will be a business class name.
	 *
	 * @example
	 * $translations = [
	 *   ''                                        => 'default user translation',
	 *   ITRocks\Framework\User::class             => 'user translation',
	 *   ITRocks\Framework\User\Account::class     => 'account user translation',
	 *   ITRocks\Framework\Traits\Has_Email::class => 'has email translation'
	 * ]
	 * Where class Use uses trait Account and Account uses trait Has_Email.
	 * $context = ITRocks\Framework\User::class            => returns 'user translation'
	 * $context = ITRocks\Framework\Email\Recipient::class => returns 'has email translation'
	 * $context = ITRocks\Framework\Anything_Else::class   => returns 'default user translation'
	 * @param $translations string[] All translations of the same word : [$context => $translation]
	 * @param $context      string The context we want to translate from
	 * @return string The chosen translation
	 */
	private function chooseTranslation(array $translations, $context)
	{
		$translation        = '';
		$this->last_context = '';
		if (isset($translations['']) && $translations['']) {
			$translation = $translations[''];
			unset($translations['']);
		}
		if ($context) {
			$this->applyPlural($translations, $translation, $context);
			foreach ($translations as $translation_context => $contextual_translation) {
				if (
					$contextual_translation
					&& (($context === $translation_context) || isA($context, $translation_context))
				) {
					$context            = $translation_context;
					$translation        = $contextual_translation;
					$this->last_context = $context;
				}
			}
		}
		return $translation;
	}

	//---------------------------------------------------------------------------- defaultTranslation
	/**
	 * @param $text string
	 * @return string
	 */
	private function defaultTranslation($text)
	{
		return endsWith($text, AT) ? strUri(rtrim($text, AT)) : str_replace('_', SP, $text);
	}

	//----------------------------------------------------------------------------------- deleteEmpty
	/**
	 * Delete empty translations from storage
	 */
	public function deleteEmpty()
	{
		$dao = Dao::current();
		Dao::begin();
		if ($dao instanceof Sql\Link) {
			/** @optimization */
			$dao->query(strReplace(
				['translations' => $dao->storeNameOf(Translation::class)],
				"DELETE FROM `translations` WHERE translation = ''"
			));
		}
		else {
			foreach (Dao::search(['translation' => ''], Translation::class) as $translation) {
				Dao::delete($translation);
			}
		}
		Dao::commit();
	}

	//--------------------------------------------------------------------------------------- reverse
	/**
	 * Reverse translator : changes a translated text into an original text
	 *
	 * - If $translation contains wildcards, this may return multiple reverse translations : one per
	 *   matching text. The return value will be a string[] only in this case. If there is only one
	 *   reverse translation, a single string will be still returned.
	 * - For non-wildcards $translation, a single string will be returned.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $translation           string the translation to search for (can contain wildcards)
	 * @param $context               string if empty, use the actual context set by enterContext()
	 * @param $context_property_path string ie 'property_name.sub_property', accepts (and ignore) '*'
	 * @param $limit_to              string[] if set, limit texts to these results (when wildcards)
	 * @param $allow_multiple        boolean allow multiple reverse translations for one text
	 * @return string|string[]
	 */
	public function reverse(
		$translation, $context = '', $context_property_path = '', array $limit_to = null,
		$allow_multiple = false
	) {
		if (Wildcard::containsWildcards($translation)) {
			$translation = str_replace(['?', '*'], ['_', '%'], $translation);
			return $this->reverseWithWildcards($translation, $context, $context_property_path, $limit_to);
		}
		if (!trim($translation) || is_numeric($translation)) {
			return $translation;
		}
		elseif (strpos($translation, DOT) !== false) {
			$text = [];
			foreach (explode(DOT, $translation) as $sentence) {
				$text[] = $this->reverse($sentence, $context, $context_property_path);
			}
			return join(DOT, $text);
		}
		$search['language']    = $this->language;
		$search['translation'] = strtolower($translation);
		$search['context']     = $context;
		if (isset($limit_to)) {
			$search['text'] = Func::in($limit_to);
		}
		$texts = Dao::search($search, Translation::class);
		foreach ($texts as $text) if ($text->translation === $translation) break;
		while (isset($search['context']) && $search['context'] && !isset($text)) {
			$position          = strrpos($search['context'], DOT);
			$search['context'] = $position ? substr($search['context'], 0, $position) : '';
			$texts             = Dao::search($search, Translation::class);
			foreach ($texts as $text) if ($text->translation === $translation) break;
		}
		if (!isset($text) && strpos($translation, ', ')) {
			$text_parts = [];
			foreach (explode(', ', $translation) as $translation_part) {
				$text_parts[] = $this->reverse($translation_part, $context, $context_property_path);
			}
			/** @noinspection PhpUnhandledExceptionInspection constant */
			$text = Builder::create(Translation::class,
				[join(', ', $text_parts), $this->language, $context, $translation]
			);
		}
		if ($allow_multiple && isset($text)) {
			$results = [];
			foreach ($texts as $text) {
				if ($text->translation === $translation) {
					$results[] = strIsCapitals($translation[0])
						? (strIsCapitals($translation) ? strtoupper($text->text) : ucfirsta($text->text))
						: $text->text;
				}
			}
			if (count($results) > 1) {
				return $results;
			}
		}
		$text = isset($text) ? $text->text : $translation;
		return strIsCapitals(substr($translation, 0, 1))
			? (strIsCapitals($translation) ? strtoupper($text) : ucfirsta($text))
			: $text;
	}

	//-------------------------------------------------------------------------- reverseWithWildcards
	/**
	 * Reverse translator with wildcards : changes a translated text with wildcards into several
	 * available original texts.
	 *
	 * - If only one text matches, returns a single string
	 * - Il multiple text match, returns a string[]
	 *
	 * @example '%fermé%' => ['close', 'closed']
	 * @example 'o?i' => 'yes'
	 * @param $translation           string the translation to search for (with wildcards)
	 * @param $context               string if empty, use the actual context set by enterContext()
	 * @param $context_property_path string ie 'property_name.sub_property', accepts (and ignore) '*'
	 * @param $limit_to              string[] if set, limit texts to these results
	 * @return string|string[]
	 */
	protected function reverseWithWildcards(
		$translation, $context, $context_property_path, array $limit_to = null
	) {
		$limit  = static::MAX_WILDCARD_REVERSE_TRANSLATIONS + 1;
		$search = ['translation' => $translation];
		if (isset($limit_to)) {
			$search['text'] = Func::in($limit_to);
		}
		$texts = [];
		/** @var $translations Translation[] */
		$translations = Dao::search(
			$search, Translation::class, [Dao::groupBy('text'), Dao::limit($limit)]
		);
		// security strengthen : do not get any value if a user types something like '%a%'
		if (count($translations) >= $limit) {
			return static::TOO_MANY_RESULTS_MATCH_YOUR_INPUT;
		}
		foreach ($translations as $found_translation) {
			// disable infinite recursion caused by translation-has-wildcards (limitation, but security)
			if (!Wildcard::containsWildcards($found_translation->translation)) {
				$more_texts = $this->reverse(
					$found_translation->translation, $context, $context_property_path, null, true
				);
				if (is_array($more_texts)) {
					$texts = array_merge($texts, $more_texts);
				}
				else {
					$texts[] = $more_texts;
				}
			}
		}
		return (count($texts) > 1) ? $texts : reset($texts);
	}

	//------------------------------------------------------------------------- separatedTranslations
	/**
	 * @param $text      string
	 * @param $separator string
	 * @param $context   string
	 * @return string
	 */
	private function separatedTranslations($text, $separator, $context)
	{
		preg_match_all('/(?<before>\s*)' . preg_quote($separator). '(?<after>\s*)/', $text, $spaces);
		$sentences            = explode($separator, $text);
		$sentence_number      = 0;
		$last_sentence_number = count($sentences) - 1;
		$translation          = '';
		foreach ($sentences as $sentence) {
			if ($sentence_number) {
				$translation .= $spaces['after'][$sentence_number - 1];
			}
			$translation .= $this->translate(trim($sentence), $context);
			if ($sentence_number < $last_sentence_number) {
				$translation .= $spaces['before'][$sentence_number] . $separator;
			}
			$sentence_number ++;
		}
		return $translation;
	}

	//-------------------------------------------------------------------------------- setTranslation
	/**
	 * Force a translation into the cache
	 *
	 * Future calls to translate() will use this instead of reading translation from the
	 * main data link.
	 *
	 * @param $text        string
	 * @param $translation string
	 * @param $context     string
	 */
	public function setTranslation($text, $translation, $context = '')
	{
		$this->cache[strtolower($text)][$context] = $translation;
	}

	//----------------------------------------------------------------------- storeDefaultTranslation
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $text string
	 * @return string
	 */
	private function storeDefaultTranslation($text)
	{
		/** @noinspection PhpUnhandledExceptionInspection constant */
		$translation = Builder::create(
			Translation::class,
			[strtolower(str_replace('_', SP, rtrim($text, AT))), $this->language]
		);
		Dao::write($translation);
		return $this->defaultTranslation($text);
	}

	//------------------------------------------------------------------------------------- translate
	/**
	 * Translates a text using current language and an optional given context
	 *
	 * @param $text    string|string[]
	 * @param $context string
	 * @return string|string[]
	 */
	public function translate($text, $context = '')
	{
		if (is_array($text)) {
			$translations = [];
			foreach ($text as $key => $text_entry) {
				$translations[$key] = $this->translate($text_entry, $context);
			}
			return $translations;
		}
		// no text : no translation
		if (!trim($text) || is_numeric($text)) {
			$translation = $text;
		}
		else {
			// different texts separated by dots : translate each part between dots
			if (strpos($text, DOT) !== false) {
				$translation = $this->separatedTranslations($text, DOT, $context);
			}
			else {
				$lower_text = strtolower($text);
				// return cached contextual translation
				if (isset($this->cache[$lower_text][$context])) {
					$translation = $this->cache[$lower_text][$context];
				}
				else {
					// $translations string[] $translation[$context]
					if (!isset($this->cache[$lower_text])) {
						$this->cache[$lower_text] = $this->translations($text);
					}
					$translations = $this->cache[$lower_text];
					// no translation found and separated by commas : translate each part between commas
					if (!$translations && (strpos($text, ', ') !== false)) {
						return $this->separatedTranslations($text, ', ', $context);
					}
					// no translation found : store original text to cache and database, then return it
					if (!$translations) {
						$translations['']
							= $this->cache[$lower_text]['']
							= $this->storeDefaultTranslation($text);
					}
					$translation = $this->chooseTranslation($translations, $context)
						?: $this->defaultTranslation($text);
					// store text for context to cache
					$this->cache[$lower_text][$context] = $translation;
				}
			}
			$translation = strIsCapitals(substr($text, 0, 1))
				? (strIsCapitals($text) ? strtoupper($text) : ucfirsta($translation))
				: $translation;
		}
		return $translation;
	}

	//---------------------------------------------------------------------------------- translations
	/**
	 * @param $text    string
	 * @param $objects boolean if true, will return Translation objects instead of texts
	 * @return string[]|Translation[] $translation[$context]|Translation[]
	 */
	public function translations($text, $objects = false)
	{
		if (endsWith($text, AT)) {
			$str_uri = true;
			$text    = rtrim($text, AT);
		}
		$translations = Dao::search(
			['language' => $this->language, 'text' => ($text === '%') ? Func::equal('%') : $text],
			Translation::class,
			$objects ? [] : [Dao::key('context')]
		);
		if (!$objects) {
			foreach ($translations as $context => $translation) {
				$translated_text        = $translation->translation ?: $this->defaultTranslation($text);
				$translations[$context] = isset($str_uri) ? strUri($translated_text) : $translated_text;
			}
		}
		return $translations;
	}

}
