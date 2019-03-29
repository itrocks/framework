$(document).ready(function()
{

	//------------------------------------------------------------------------ input[data-conditions]
	var will_change = {};
	var selector    = 'input[data-conditions], select[data-conditions], textarea[data-conditions]';
	$(selector).build('each', function()
	{
		var $this      = $(this);
		var conditions = $this.data('conditions').replace(/\(.*\)/g);
		$.each(conditions.split(';'), function(condition_key, condition) {
			condition = condition.split('=');
			var $condition;
			if (will_change.hasOwnProperty(condition[0])) {
				$condition = will_change[condition[0]];
			}
			else {
				$condition = $this.closest('form').find('[name="id_' + condition[0] + DQ + ']');
				if ($condition.length) {
					$condition = $condition.next();
				}
				else {
					$condition = $this.closest('form').find('[name=' + DQ + condition[0] + DQ + ']');
				}
				will_change[condition[0]] = $condition;
			}
			var condition_name = $condition.attr('name');
			if (!condition_name) {
				condition_name = $condition.prev().attr('name');
			}
			if ((typeof $this.data('conditions')) === 'string') {
				$this.data('conditions', {});
			}
			if (!$this.data('conditions').hasOwnProperty(condition_name)) {
				$this.data('conditions')[condition_name] = { element: $condition, values: {}};
			}
			$.each(condition[1].split(','), function(value_key, value) {
				$this.data('conditions')[condition_name].values[value] = value;
			});
			var this_name = $this.attr('name');
			if (!this_name) {
				this_name = $this.prev().attr('name');
			}
			if ($condition.data('condition-of')) {
				$condition.data('condition-of')[this_name] = $this;
			}
			else {
				var condition_of = {};
				condition_of[this_name] = $this;
				$condition.data('condition-of', condition_of);
			}
		});
	});

	$.each(will_change, function(condition_name, $condition) {
		if (!$condition.data('condition-change')) {
			$condition.data('condition-change', true);
			$condition.change(function()
			{
				var $this = $(this);
				$.each($this.data('condition-of'), function(element_name, $element) {
					var show = true;
					$.each($element.data('conditions'), function(condition_name, condition) {
						var found = false;
						$.each(condition.values, function(value) {
							var element_type = condition.element.attr('type');
							var element      = (element_type === 'radio')
								? condition.element.filter(':checked')
								: condition.element;
							var element_value = (element_type === 'checkbox')
								? (element.is(':checked') ? '1' : '0')
								: element.val();
							if (value === '@empty') {
								found = !element_value.length;
							}
							else if (value === '@set') {
								found = element_value.length;
							}
							else {
								found = (element_value === value);
							}
							return !found;
						});
						return (show = found);
					});
					var name = $element.attr('name');
					if (!name) {
						name = $element.data('name');
					}
					if (!name) {
						name = $element.prev().attr('name');
					}
					if (!name && $element.is('label')) {
						name = $element.closest('[id]').attr('id');
					}
					if (name.beginsWith('id_')) {
						name = name.substr(3);
					}
					name = name.replace('[', '.').replace(']', '').replace('id_', '');
					var $field = $element.closest('[id="' + name + '"]');
					if (!$field.length) {
						$field = $element.closest('[data-name="' + name + '"]');
					}
					if (!$field.length) {
						$field = $element.parent().children();
					}
					var $input_parent = $field.is('input, select, textarea') ? $field.parent() : $field;
					if (show) {
						// when shown, get the locally saved value back (undo restores last typed value)
						$input_parent.find('input, select, textarea').each(function() {
							var $this = $(this);
							// show can be called on already visible and valued element : ignore them
							if ($this.data('value')) {
								$this.val($this.data('value'));
							}
							$this.data('value', '');
						});
						$field.show();
					}
					else {
						$field.hide();
						// when hidden, reset value to empty
						$input_parent.find('input, select, textarea').each(function() {
							var $this = $(this);
							$this.data('value', $this.val());
							// never empty values on required fields TODO should be managed in validator
							if (!$this.attr('required') && !$this.data('required')) {
								$this.val('');
							}
						});
					}
				});
			});
		}
		$condition.change();
	});

});
