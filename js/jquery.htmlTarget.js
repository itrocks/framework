(function($)
{

	//------------------------------------------------------------------------------------ htmlTarget
	/**
	 * Write multi-target HTML data into multiple targets
	 *
	 * @example
	 *   $('#main').htmlTarget('Some text <!--target #another-id-->and other<!--end--> continues')
	 * equiv :
	 *   $('#another-id').html('and other');
	 *   $('#main').html('Some text  continues');
	 * @example
	 *   $('#main').htmlTarget('<!--target #another-id-->and other<!--end-->')
	 * equiv :
	 *   $('#another-id').html('and other');
	 *   $('#main').empty();
	 * @param data string
	 * @return jQuery[] affected targets
	 */
	$.fn.htmlTarget = function(data)
	{
		var $targets        = $();
		var target_position = 0;
		while ((target_position = data.indexOf('<!--target ', target_position)) > -1) {
			var target_data_position = target_position + 11;
			var target_end_position  = data.indexOf('-->', target_data_position);
			var target               = data.substring(target_data_position, target_end_position);
			var $target              = $(target);
			target_data_position     = target_end_position + 3;
			target_end_position      = data.indexOf('<!--end-->', target_data_position);
			var target_data          = data.substring(target_data_position, target_end_position);
			target_end_position     += 10;
			$target.html(target_data);
			data     = (data.substr(0, target_position) + data.substr(target_end_position)).trim();
			$targets = $targets.add($target);
		}
		if (data.trim().length) {
			this.html(data);
			$targets = this.add($targets);
		}
		else if (this.is('.popup')) {
			this.remove();
		}
		else {
			this.html('');
			$targets = this.add($targets);
		}
		return $targets;
	}

})(jQuery);
