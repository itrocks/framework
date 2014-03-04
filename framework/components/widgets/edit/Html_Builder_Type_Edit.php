<?php
namespace SAF\Framework;

use DateTime;

/**
 * Builds a standard form input matching a given data type and value
 */
class Html_Builder_Type_Edit
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//-------------------------------------------------------------------------------------- $preprop
	/**
	 * @var string
	 */
	public $preprop;

	//------------------------------------------------------------------------------------- $readonly
	/**
	 * @var boolean
	 */
	public $readonly = false;

	//------------------------------------------------------------------------------------- $template
	/**
	 * @var Html_Edit_Template
	 */
	public $template;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @var Type
	 */
	protected $type;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var string
	 */
	protected $value;

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param $name    string
	 * @param $type    Type
	 * @param $value   mixed
	 * @param $preprop string
	 */
	public function __construct($name = null, Type $type = null, $value = null, $preprop = null)
	{
		if (isset($name))    $this->name = $name;
		if (isset($type))    $this->type = $type;
		if (isset($value))   $this->value = $value;
		if (isset($preprop)) $this->preprop = $preprop;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return string
	 */
	public function build()
	{
		$type = $this->type;
		if (!isset($type)) {
			return $this->buildId();
		}
		else {
			switch ($type->asString()) {
				case 'boolean':  return $this->buildBoolean();
				case 'float':    return $this->buildFloat();
				case 'integer':  return $this->buildInteger();
				case 'string':   return $this->buildString();
				case 'string[]': return $this->buildString();
			}
			if ($type->isClass()) {
				$class_name = $type->asString();
				if (is_a($class_name, DateTime::class, true)) {
					return $this->buildDateTime();
				}
				elseif (is_a($class_name, File::class, true)) {
					return $this->buildFile();
				}
				else {
					return $this->buildObject();
				}
			}
		}
		return $this->value;
	}

	//---------------------------------------------------------------------------------- buildBoolean
	/**
	 * @return Dom_Element
	 */
	protected function buildBoolean()
	{
		$input = new Html_Input($this->getFieldName());
		$input->setAttribute('type', 'checkbox');
		$input->setAttribute('value', 1);
		if ($this->value) {
			$input->setAttribute('checked');
		}
		if ($this->readonly) {
			$input->setAttribute('readonly');
		}
		return $input;
	}

	//--------------------------------------------------------------------------------- buildDateTime
	/**
	 * @return Dom_Element
	 */
	protected function buildDateTime()
	{
		$input = new Html_Input($this->getFieldName(), $this->value);
		$input->setAttribute('autocomplete', 'off');
		if ($this->readonly) {
			$input->setAttribute('readonly');
		}
		$input->addClass('datetime');
		return $input;
	}

	//------------------------------------------------------------------------------------- buildFile
	/**
	 * @return Html_Span
	 */
	protected function buildFile()
	{
		$file = new Html_Input($this->getFieldName());
		$file->setAttribute('type', 'file');
		$file->addClass('file');
		if ($this->readonly) {
			$file->setAttribute('readonly');
		}
		if ($this->value instanceof File) {
			$span = $this->buildFileAnchor($this->value);
		}
		else {
			$span = '';
		}
		return $file . $span;
	}

	//------------------------------------------------------------------------------- buildFileAnchor
	/**
	 * @param $file File
	 * @return Html_Anchor
	 */
	protected function buildFileAnchor(File $file)
	{
		/** @var $session_files Session_Files */
		$session_files = Session::current()->get(Session_Files::class, true);
		$session_files->files[] = $file;
		$image = ($file->getType()->is('image'))
			? new Html_Image('/Session_File/output/' . $file->name . '?size=22')
			: '';
		$anchor = new Html_Anchor(
			'/Session_File/image/' . $file->name,
			$image . new Html_Span($file->name)
		);
		if ($file->getType()->is('image')) {
			$anchor->setAttribute('target', '#_blank');
			//$anchor->addClass('popup');
		}
		return $anchor;
	}

	//------------------------------------------------------------------------------------ buildFloat
	/**
	 * @return Dom_Element
	 */
	protected function buildFloat()
	{
		$input = new Html_Input($this->getFieldName(), $this->value);
		if ($this->readonly) {
			$input->setAttribute('readonly');
		}
		$input->addClass('float');
		$input->addClass('autowidth');
		return $input;
	}

	//--------------------------------------------------------------------------------------- buildId
	/**
	 * @return Dom_Element
	 */
	protected function buildId()
	{
		$input = new Html_Input($this->getFieldName(), $this->value);
		$input->setAttribute('type', 'hidden');
		$input->addClass('id');
		return $input;
	}

	//---------------------------------------------------------------------------------- buildInteger
	/**
	 * @return Dom_Element
	 */
	protected function buildInteger()
	{
		$input = new Html_Input($this->getFieldName(), $this->value);
		if ($this->readonly) {
			$input->setAttribute('readonly');
		}
		$input->addClass('integer');
		$input->addClass('autowidth');
		return $input;
	}

	//----------------------------------------------------------------------------------- buildObject
	/**
	 * @param $conditions string[] the key is the name of the condition, the value is the name of the
	 *   value that enables the condition
	 * @param $filters string[] the key is the name of the filter, the value is the name of the form
	 *   element containing its value
	 * @return string
	 */
	protected function buildObject($conditions = null, $filters = null)
	{
		$class_name = $this->type->asString();
		// visible input
		$input = new Html_Input(null, strval($this->value));
		$input->setAttribute('autocomplete', 'off');
		$input->setAttribute(
			'data-combo-class', Namespaces::shortClassName(Names::classToSet($class_name))
		);
		if (!$this->readonly) {
			if ($filters) {
				$html_filters = array();
				$old_name = $this->name;
				foreach ($filters as $filter_name => $filter_value) {
					$this->name = $filter_value;
					$name = $this->getFieldName('', false);
					$html_filters[] = $filter_name . '=' . $name;
				}
				$this->name = $old_name;
				$input->setAttribute('data-combo-filters', join(',', $html_filters));
			}
			if ($conditions) {
				$html_conditions = array();
				$old_name = $this->name;
				foreach ($conditions as $condition_name => $condition_value) {
					$this->name = $condition_name;
					$name = $this->getFieldName('', false);
					$html_conditions[] = $name . '=' . $condition_value;
				}
				$this->name = $old_name;
				$input->setAttribute('data-conditions', join(';', $html_conditions));
			}
			$input->addClass('autowidth');
			$input->addClass('combo');
			// id input
			$id_input = new Html_Input(
				$this->getFieldName('id_'), Dao::getObjectIdentifier($this->value)
			);
			$id_input->setAttribute('type', 'hidden');
			$id_input->addClass('id');
			// 'add' anchor
			if (is_object($this->value)) {
				$fill_combo = isset($this->template)
					? array(
						'fill_combo' => $this->template->getFormId() . '.' . $this->getFieldName('id_', false)
					)
					: '';
				$edit = new Html_Anchor(
					View::current()->link(get_class($this->value), 'new', null, $fill_combo), 'edit'
				);
				$edit->addClass('edit');
				$edit->setAttribute('target', '#_blank');
				$edit->setAttribute('title', '|Edit ¦' . Names::classToDisplay($class_name) . '¦|');
			}
			else {
				$edit = '';
			}
			// 'more' button
			$more = new Html_Button('more');
			$more->addClass('more');
			$more->setAttribute('tabindex', -1);
			return $id_input . $input . $more . $edit;
		}
		return $input;
	}

	//----------------------------------------------------------------------------------- buildString
	/**
	 * @param $multiline boolean
	 * @param $values    string[]
	 * @return Dom_Element
	 */
	protected function buildString($multiline = false, $values = null)
	{
		if ($multiline) {
			$input = new Html_Textarea($this->getFieldName(), $this->value);
			$input->addClass('autowidth');
			$input->addClass('autoheight');
		}
		elseif (isset($values) && $values) {
			if (!isset($values[''])) {
				$values = array('' => '') + $values;
			}
			$input = new Html_Select($this->getFieldName(), $values, $this->value);
		}
		else {
			$input = new Html_Input($this->getFieldName(), $this->value);
			$input->setAttribute('autocomplete', 'off');
			$input->addClass('autowidth');
		}
		if ($this->readonly) {
			$input->setAttribute('readonly');
		}
		return $input;
	}

	//---------------------------------------------------------------------------------- getFieldName
	/**
	 * @param $prefix            string
	 * @param $counter_increment boolean
	 * @return string
	 */
	public function getFieldName($prefix = '', $counter_increment = true)
	{
		$field_name = $this->name;
		if (empty($field_name) && $this->preprop) {
			$prefix = '';
		}
		if (!isset($this->preprop)) {
			$field_name = $prefix . $field_name;
		}
		elseif (substr($this->preprop, -2) == '[]') {
			$field_name = substr($this->preprop, 0, -2) . '[' . $prefix . $field_name . ']';
			$count = $this->nextCounter($field_name, $counter_increment);
			$field_name .= '[' . $count . ']';
		}
		else {
			$field_name = $this->preprop . '[' . $prefix . $field_name . ']';
		}
		return $field_name;
	}

	//----------------------------------------------------------------------------------- nextCounter
	/**
	 * Returns next counter for field name into current form context
	 *
	 * @param $field_name string
	 * @param $increment  boolean
	 * @return integer
	 */
	public function nextCounter($field_name, $increment = true)
	{
		$form = $this->template->getFormId();
		$counter = isset($this->template->cache['counter'])
			? $this->template->cache['counter'] : array();
		if (!isset($counter[$form])) {
			$counter[$form] = array();
		}
		$count = isset($counter[$form][$field_name]) ? $counter[$form][$field_name] + $increment : 0;
		$counter[$form][$field_name] = $count;
		$this->template->cache['counter'] = $counter;
		return $count;
	}

	//----------------------------------------------------------------------------------- setTemplate
	/**
	 * @param $template Html_Edit_Template
	 * @return Html_Builder_Type_Edit
	 */
	public function setTemplate(Html_Edit_Template $template)
	{
		$this->template = $template;
		return $this;
	}

}
