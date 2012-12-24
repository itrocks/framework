$("document").ready(function() {

	$(".edit").click(function() {
		var $this = $(this);
		if ($this.parents("form").length) {
			$this.find("input").focus();
		}
		else {
			var $form;
			$this.parents(".form").replaceWith(function(){
				$this = $(this);
				return $form = $('<form>', {
					"class":  $this.attr("class"),
					"id":     $this.attr("id"),
					"method": "POST"
				}).append($this.children());
			});
			$form.find(".edit").html(function() {
				$this = $(this);
				return $('<input>', { "name": $this.parent().attr("id"), "value": $this.text() })
					.css({ "width": getInputTextWidth($this.text()) })
					.keyup(function() { $(this).width(getInputTextWidth(this.value)); });
			});
			$this.first().focus();
			$form.find(".ifedit").css("display", "inline-block");
		}
	});

});
