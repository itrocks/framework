<?php
namespace SAF\Framework;

use SAF\Plugins;

/**
 * This plugin limits the count of elements a Collection or a Map can display on an edit form
 */
class Html_Edit_Multiple_Limiter implements Plugins\Registerable
{

	//---------------------------------------------------------------------------------------- $count
	/**
	 * @var Dao_Count_Option
	 */
	private $count;

	//---------------------------------------------------------------------------------- $in_multiple
	/**
	 * @values '', 'search', 'build'
	 * @var string
	 */
	private $in_multiple = '';

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property_Value
	 */
	private $property;

	//----------------------------------------------------------------- afterHtmlBuilderMultipleBuild
	/**
	 * @param $result Html_Table
	 */
	public function afterHtmlBuilderMultipleBuild(Html_Table $result)
	{
		if ($this->in_multiple == 'build') {
			$table = $result;
			$length = count($table->body->rows) - 1;
			if ($this->count->count > $length) {
				// vertical scrollbar
				$vertical_scroll_bar = new Html_Table_Standard_Cell();
				$vertical_scroll_bar->addClass('vertical');
				$vertical_scroll_bar->addClass('scrollbar');
				$vertical_scroll_bar->setAttribute('rowspan', 1000000);
				$vertical_scroll_bar->setData('start', 0);
				$vertical_scroll_bar->setData('length', $length);
				$vertical_scroll_bar->setData('total', $this->count->count);
				$link = '/Html_Edit_Multiple/output/'
					. Namespaces::shortClassName($this->property->getDeclaringClass())
					. SL . Dao::getObjectIdentifier($this->property->getObject())
					. SL . $this->property->name
					. SL . '?move=';
				$up       = new Html_Anchor($link . 'up');   $up->addClass('up');
				$position = new Html_Anchor($link . 1);      $position->addClass('position');
				$down     = new Html_Anchor($link . 'down'); $down->addClass('down');
				$vertical_scroll_bar->setContent($up . $position . $down);
				// add vertical scrollbar cells to multiple (collection or map) table
				$table->head->rows[0]->addCell(new Html_Table_Header_Cell(), 0);
				$table->body->rows[0]->addCell($vertical_scroll_bar, 0);
			}
			$this->in_multiple = '';
		}
	}

	//------------------------------------------------------------- beforeHtmlEditTemplateParseMethod
	/**
	 * Activate plugin before HTML method parsing of a Reflection_Property_Value named 'value'
	 *
	 * @param $object        object
	 * @param $property_name string
	 */
	public function beforeHtmlEditTemplateParseMethod($object, $property_name)
	{
		/** @noinspection PhpUndefinedMethodInspection */
		if (
			($object instanceof Reflection_Property_Value)
			&& ($property_name === 'value')
			&& ($object->getAnnotation('link')->isMultiple())
		) {
			$this->in_multiple = 'search';
			$this->property = $object;
		}
		else {
			$this->in_multiple = '';
		}
	}

	//--------------------------------------------------------------------------- beforeMysqlLinkRead
	/**
	 * If plugin is activated, limits result count of Mysql_Link::search()
	 *
	 * This results on an incomplete object, but the object is used for editing form only so we don't
	 * care.
	 *
	 * @param $options Dao_Option|Dao_Option[] some options for advanced search
	 */
	public function beforeMysqlLinkSearch(&$options)
	{
		if ($this->in_multiple === 'search') {
			if (is_object($options)) {
				$options = [$options];
			}
			$options[] = Dao::limit(10);
			$options[] = $this->count = new Dao_Count_Option();
			$this->in_multiple = 'build';
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 *
	 * @param $register Plugins\Register
	 */
	public function register(Plugins\Register $register)
	{
		$aop = $register->aop;
		$aop->beforeMethod(
			[Html_Edit_Template::class, 'parseMethod'],
			[$this, 'beforeHtmlEditTemplateParseMethod']
		);
		$aop->beforeMethod(
			[Mysql_Link::class, 'search'],
			[$this, 'beforeMysqlLinkSearch']
		);
		$aop->afterMethod(
			[Html_Builder_Collection::class, 'build'],
			[$this, 'afterHtmlBuilderMultipleBuild']
		);
		$aop->afterMethod(
			[Html_Builder_Map::class, 'build'],
			[$this, 'afterHtmlBuilderMultipleBuild']
		);
	}

}
