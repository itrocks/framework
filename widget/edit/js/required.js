$('document').ready(function()
{
	$('form').build(function()
	{
		if (!this.length || !this.closest('form').length) return;

		var elements_selector = 'input[name$="]"], select[name$="]"], textarea[name$="]"]'
			+ ', input[data-name$="]"], select[data-name$="]"], textarea[data-name$="]"]';
		var next_elements_selector = 'input:not([data-name], [name])';

		//------------------------------------------------------------------------------- applyRequired
		/**
		 * Apply data-required => required into $form, depending on the last $modified_element
		 *
		 * this jQuery|object the modified element or the form : DOM element or jQuery object allowed
		 */
		var applyRequired = function()
		{
			var $element  = $(this);
			var $form     = $element.closest('form');
			var $elements = $form.find(elements_selector);

			// ensure that we descend
			$elements.sort(function(element1, element2) {
				var $element1 = $(element1);
				var $element2 = $(element2);
				var name1     = elementName($element1);
				var name2     = elementName($element2);
				var name1_id  = (name1.indexOf('[id]') >= 0);
				var name2_id  = (name2.indexOf('[id]') >= 0);
				if (name1_id && !name2_id) return -1;
				if (name2_id && !name1_id) return 1;
				var count1    = (name1.match(/]/g) || []).length;
				var count2    = (name2.match(/]/g) || []).length;
				return count1 - count2;
			});

			$elements.next(next_elements_selector).removeAttr('required');
			$elements.removeAttr('required');

			// calculate require
			var required_parents = [];
			$elements.each(function() {
				var $element = $(this);
				var name     = elementName($element);
				var parent   = parentName(name);
				if (
					(elementRequired($element) && (!parent || required_parents[parent]))
					|| haveChildrenValues(name, $elements)
				) {
					$element.attr('required', true);
					$element.next(next_elements_selector).attr('required', true);
					required_parents[name] = true;
				}
				else {
					required_parents[name] = false;
				}
			});
		};

		//------------------------------------------------------------------------ delayedApplyRequired
		var delayedApplyRequired = function()
		{
			var element = this;
			setTimeout(function() { applyRequired.call(element); }, 0);
		};

		//--------------------------------------------------------------------------------- elementName
		/**
		 * @param $element jQuery
		 * @return string
		 */
		var elementName = function($element)
		{
			var name = $element.attr('name');
			if (!name) {
				name = $element.data('name');
			}
			return name;
		};

		//----------------------------------------------------------------------------- elementRequired
		/**
		 * @param $element jQuery
		 * @return string
		 */
		var elementRequired = function($element)
		{
			return $element.data('required')
				|| $element.next(next_elements_selector).data('required');
		};

		//-------------------------------------------------------------------------------- elementValue
		/**
		 * @param $element jQuery
		 */
		var elementValue = function($element)
		{
			var value = $element.val();
			if (!value) {
				value = $element.next(next_elements_selector).val();
			}
			return value;
		};

		//-------------------------------------------------------------------------- haveChildrenValues
		/**
		 * Check if a parent[id] has any value typed in into any of its parent[property] sub-fields
		 *
		 * @var parent_name string
		 * @var $elements   object[] dom elements
		 * @return boolean
		 */
		var haveChildrenValues = function(parent_name, $elements)
		{
			var id_position = parent_name.indexOf('[id]');
			if (id_position < 0) {
				return false;
			}
			var filter_in = parent_name.substr(0, id_position);
			var required  = false;
			var trailing  = parent_name.match(/\[[0-9]*]$/)
				? parent_name.substr(parent_name.lastIndexOf('['))
				: '';
			$elements.each(function() {
				var $element = $(this);
				var name     = elementName($element);
				if (
					name.beginsWith(filter_in)
					&& (!trailing || name.endsWith(trailing))
					&& (parent_name !== name)
					&& elementValue($element)
				) {
					required = true;
					return false;
				}
			});
			return required;
		};

		//---------------------------------------------------------------------------------- parentName
		/**
		 * Calculate the name of the parent field whose required attribute is to check
		 *
		 * @param name string|null
		 */
		var parentName = function(name)
		{
			var id_position;
			var parent_name = null;
			var last        = name.lastIndexOf('[');
			var trailing    = '';

			// remove last [xx] where xx is a strict numeric
			if (last >= 0) {
				if (name.match(/\[[0-9]*]$/)) {
					trailing = name.substr(last);
					name     = name.substr(0, last);
				}
			}

			// parent of 'property' : null
			if (last < 0) {
				parent_name = null;
			}

			// parent of 'parent[property][id]' : 'parent[id]'
			else if (name.indexOf('[id]') >= 0) {
				// special case : parent of 'parent[id]' : null
				if (name.indexOf('][') >= 0) {
					// parent of 'parent[property][id]' : 'parent[id]'
					id_position         = name.indexOf('[id]');
					var parent_position = name.substr(0, id_position).lastIndexOf('[');
					parent_name         = name.substr(0, parent_position) + name.substr(id_position);
				}
			}

			// parent of 'parent[property]' : 'parent[id]'
			else {
				id_position = name.lastIndexOf('[');
				parent_name = name.substr(0, id_position) + '[id]' + trailing;
			}

			return parent_name;
		};

		//---------------------------------------------------------------------------------------------
		applyRequired.call(this.closest('form'));
		var $register_elements = this.inside(elements_selector);
		$register_elements = $register_elements.add($register_elements.next(next_elements_selector));
		$register_elements.add(next_elements_selector)
			.change(applyRequired)
			.keyup(delayedApplyRequired);

	});
});
