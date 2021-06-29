$(document).ready(function () {
	$('body').build('click', 'div.accordion-header', function (event) {
		var target_name        = $(this).data('accordion-target');
		var $accordion_content = $(this).closest('div.accordion').find(
			'div[data-accordion="' + target_name + '"]');

		if ($accordion_content.hasClass('accordion-hide')) {
			// Get all accordion with the same input name and hide content
			var accordion_name      = $(this).find('input[type="radio"]').attr('name');
			var $article            = $(this).closest('article');
			var $inputs             = $article.find(
				'.accordion > .accordion-header  > input[type="radio"][name="' + accordion_name + '"]');
			var $accordions_content = $inputs.closest('div.accordion').find('div.accordion-container');
			$accordions_content.each(function () {
				if (!$(this).hasClass('accordion-hide')) {
					$(this).addClass('accordion-hide');
				}
			});

			// Check the target and display it
			$(this).find('input[type=radio]').prop("checked", true);
			$accordion_content.removeClass('accordion-hide');
		}
		else {
			// uncheck the target and hide it
			$(this).find('input[type=radio]').prop("checked", false);
			$accordion_content.addClass('accordion-hide');
		}
		event.preventDefault();
		event.stopImmediatePropagation();
	});
});
