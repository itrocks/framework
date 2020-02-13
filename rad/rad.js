$(document).ready(function()
{
	window.zindex_counter = 0;

	var rad_mode = false;

	//----------------------------------------------------------------------------------------- enter
	/**
	 * Call this on a jQuery object you want to highlight and enable inserting elements between
	 * some contained elements
	 *
	 * @param container        string the container element selector
	 * @param element_selector string the children element selector between which the user will insert
	 * @param hint             string the hint text to display and follow the mouse cursor
	 * @param direction        string 'auto' (default), 'horizontal' or 'vertical'
	 * @param link             string
	 */
	var enter = function(container, element_selector, hint, direction, link)
	{
		var $container = $(container);

		// highlight zones
		$container.each(function() {
			$(this).addClass('rad highlight');
		});

		var $elements = (element_selector[0] === '>')
			? $container.children(element_selector.substr(1))
			: $container.find(element_selector);

		// horizontal or vertical ? (default is horizontal, if zero or one contained elements only)
		var vertical = (direction === 'vertical');
		if (direction === 'auto') {
			if ($elements.length > 1) {
				var position1 = $($elements[0]).position();
				var position2 = $($elements[1]).position();
				vertical = (position1.left === position2.left);
			}
		}

		// awful patch : display fields elements as blocks as table-row will not get red borders (but why ?)
		$elements.filter('fieldset>div').css('display', 'block');

		//---------------------------------------------------------------- $elements, $elements a click
		/**
		 * On clicking an element : the default action will be ignored : we call the insert form instead
		 */
		var click = function(event)
		{
			var $this = $(this);
			// call link
			var call = link;
			if (call === 'add_property') {
				$('.invisible').show();
			}
			else {
				if (call.indexOf('{class}') > -1) {
					call = call.repl('{class}', $this.closest('[data-class]').data('class').repl(BS, SL));
				}
				if (call.indexOf('{feature') > -1) {
					call = call.repl('{feature}', $this.closest('[data-feature]').data('feature'));
				}
				call = call.replace(/{(\w+)->(\w+)}/g, function(text, selector, attribute) {
					var value = $this.closest(selector).attr(attribute);
					if (attribute === 'class') {
						value = value.repl(SP, DOT);
					}
					return value;
				});
				call = window.app.uri_base + call + '?as_widget' + window.app.andSID();
				$.ajax({ url: call, success: function(data) {
					var $popup = $(data).addClass('rad popup');
					$popup.css('position', 'absolute');
					$popup.offset({
						left: $this.offset().left,
						top:  $this.offset().top + $this.height() + 5
					});
					$popup.appendTo('body');
					$popup.build();
				}});
				// prevent click inside <a>
				event.preventDefault();
				event.stopImmediatePropagation();
			}
		};

		$elements.click(click);
		$elements.find('*').off('click');
		$elements.find('a').click(click);

		//------------------------------------------------------------------------- $elements mousemove
		/**
		 * On entering : creates hint box and highlights.
		 * On moving : moves hint box,
		 * Highlights top, right, bottom or left border of the element we want to add before / after
		 */
		$elements.mousemove(function(event)
		{
			var $this = $(this);
			// the hint popup follows the mouse
			$('.hint.popup').css({ left: event.pageX + 30 + 'px', top: event.pageY + 30 + 'px' });
			// add rad classes
			$this.removeClass('insert-bottom insert-left insert-right insert-top');
			if (vertical) {
				var top = (event.pageY - $this.offset().top)
					< ($this.offset().top + $this.height() - event.pageY);
				$this.addClass(top ? 'insert-top' : 'insert-bottom');
			}
			else {
				var left = (event.pageX - $this.offset().left)
					< ($this.offset().left + $this.width() - event.pageX);
				$this.addClass(left ? 'insert-left' : 'insert-right');
			}
		});

		//-------------------------------------------------------------------------- $elements mouseout
		$elements.mouseout(function()
		{
			var $this = $(this);
			// remove rad classes
			$this.removeClass('rad insert-bottom insert-left insert-right insert-top');
			// remove hint popup
			$('.hint.popup').remove();
		});

		//------------------------------------------------------------------------- $elements mouseover
		$elements.mouseover(function(event)
		{
			var $this = $(this);
			// add rad class
			$this.addClass('rad');
			// add hint popup
			var $hint = $('<div>');
			$hint.addClass('hint popup');
			$hint.css({
				'background-color': 'white',
				border:             '1px solid black',
				display:            'inline-block',
				left:               event.pageX + 30 + 'px',
				padding:            '2px',
				position:           'absolute',
				top:                event.pageY + 30 + 'px'
			});
			$hint.text(hint);
			$hint.appendTo('body');
		});
	};

	//----------------------------------------------------------------------------- body.build (MAIN)
	$('body').build('click', '.rad.enter a', function(event)
	{
		event.preventDefault();
		event.stopImmediatePropagation();

		// what will the trashcan accept
		//noinspection JSJQueryEfficiency well, why ?
		var $trashcan = $('#trashcan a');
		var accept;
		var trashcan_accept = ', .action, .property';

		//-------------------------------------------------------------------------------------- exit
		if (rad_mode) {
			rad_mode = false;

			accept = $trashcan.data('accept').repl(trashcan_accept, '');

			//history.go(0);
		}

		//------------------------------------------------------------------------------------- enter
		else {
			rad_mode = true;
			radOutput();

			// properties can be dropped into trashcan only when in "RAD" mode
			accept = $trashcan.data('accept') + trashcan_accept;

		}

		// common code for enter / exit
		$trashcan.data('accept', accept);
		$trashcan.droppable({ accept: accept });
	});

});

/**
 //enter('.general.actions', '>li', 'Insérer une action', 'horizontal', '/ITRocks/Framework/Component/Button/edit/{class}/{feature}/{li->class}');
 //enter('.actions>li>ul', '>li', 'Insérer une action secondaire', 'vertical');
 //enter('fieldset', '>div:not(.tabber, .columns)', 'Insérer un champ', 'vertical','add_property');

 $(document).radAddOutput();
 //$(document).radAddButton();
 //$('.menu>ul').radAdd('>li', 'Insérer un bloc de menu', 'vertical');
 //$('.menu>ul>li>ul').radAdd('>li', 'Insérer un lien dans le menu', 'vertical');
 //$('.actions>li>ul').radAdd('>li', 'Insérer une action secondaire', 'vertical');
 //$('article.list>table>thead>tr:first-child').radAdd('>th.property', 'Insérer une colonne', 'horizontal');
 //$('.tabber>ul').radAdd('>li', 'Insérer un onglet', 'horizontal');
 //$('.columns').radAdd('>.column', 'Insérer un bloc de champs en colonne', 'horizontal');
 //$('table.collection>thead>tr:first-child').radAdd('>th:not(:last-child)', 'Insérer une colonne', 'horizontal');
*/
