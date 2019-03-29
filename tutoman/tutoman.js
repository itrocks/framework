$(document).ready(function()
{

	var tuto = {

		animations:  {},

		$container:  $('<div class="tuto"></div>'),
		$left_eye:   $('<span class="left eye"><span></span></span>'),
		$left_hand:  $('<span class="left hand"></span>'),
		$right_eye:  $('<span class="right eye"><span></span></span>'),
		$right_hand: $('<span class="right hand"></span>'),
		$mouth:      $('<div class="mouth"></div>'),

		animate: function()
		{
			for (var animation in this.animations) if (this.animations.hasOwnProperty(animation)) {
				this.animations[animation](this);
			}
		},

		followMouse: function()
		{
			this.animations.follow_mouse = function(me)
			{
				var hot_spot = me.hotSpot();
				var dx = document.mouse.x - hot_spot.x;
				var dy = document.mouse.y - hot_spot.y;
				var r = (dx * dx / 16 + dy * dy / 16 < 1) ? 1
					: Math.sqrt(16 * 16 / (dx * dx * 16 + dy * dy * 16));
				var x = r * dx + 4;
				var y = r * dy + 4;
				me.$left_eye.children('span').css({ left: x + 'px', top: y + 'px' });
				me.$right_eye.children('span').css({ left: x + 'px', top: y + 'px' });
			};
			return this;
		},

		goLeftOf: function(selector)
		{
			selector = $(selector);
			if (selector.length) {
				this.goto(selector.offset().left - this.$container.width(), selector.offset().top);
			}
			return this;
		},

		goRightOf: function(selector)
		{
			selector = $(selector);
			if (selector.length) {
				this.goto(selector.offset().left + selector.width(), selector.offset().top);
			}
			return this;
		},

		goto: function(x, y)
		{
			this.animations.goto = function(me)
			{
				var pos = me.position();
				var dx = Math.max((x - pos.x) / 15, 1);
				var dy = Math.max((y - pos.y) / 15, 1);
				me.setPosition(pos.x + Math.round(dx / 20), pos.y + Math.round(dy / 20));
			};
			return this;
		},

		hotSpot: function()
		{
			return {
				x: this.$container.offset().left + Math.round(this.$container.width() / 2),
				y: this.$left_eye.offset().top + Math.round(this.$left_eye.height() / 2)
			}
		},

		init: function()
		{
			this.$container
				.append(this.$left_eye).append(this.$right_eye)
				.append(this.$left_hand).append(this.$right_hand)
				.append(this.$mouth);
			this.noHand();
			$('body').append(this.$container);

			var tuto = this;
			setInterval(function() { tuto.animate(); }, 100);
			return this;
		},

		leftHand: function()
		{
			this.$container.children('.hand.right').hide();
			this.$container.children('.hand.left').show();
			return this;
		},

		noHand: function()
		{
			this.$container.children('.hand').hide();
			return this;
		},

		position: function()
		{
			return {
				x: this.$container.offset().left,
				y: this.$container.offset().top
			}
		},

		rightHand: function()
		{
			this.$container.children('.hand.left').hide();
			this.$container.children('.hand.right').show();
			return this;
		},

		setPosition: function(x, y)
		{
			this.$container.css({ left: x + 'px', top: y + 'px' });
		}

	};

	/*
	tuto.init()
		.followMouse()
		.goRightOf('#login').leftHand();
	*/

	/*
	tuto.init()
		.step({
			action: tuto.goRightOf('#login'),
			done:   'Veuillez taper votre identifiant utilisateur',
			wait:   function() { return ($('#login').val().length > 3); }
		})
		.step({
			action: tuto.goRightOf('#password'),
			done:   'Veuillez ensuite taper votre mot de passe',
			wait:   function() { return ($('#password').val().length > 3); }
		})
		.step({
			action: tuto.goRightOf('article.user.login > form input[type=submit]'),
			done:   'Enfin, cliquez sur le bouton Connexion pour continuer',
			wait:   function() { return () }
		});
	*/

});
