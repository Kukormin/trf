if (siteOptions.vcardPage) {
	var Regime = {
		days: ['пн','вт','ср','чт','пт','сб','вс'],
		init: function () {
			this.div = $('.regime-controls');
			this.input = this.div.find('input[type=hidden]');
			this.cont = this.div.find('.cont');

			this.checkAdd();

			this.cont.on('click', '.regime_part > span', this.dayClick);
			this.cont.on('click', '.icon-plus-sign', this.plusClick);
			this.cont.on('click', '.icon-minus-sign', this.minusClick);
			this.cont.on('click', '.works', this.onlyWork);
			this.cont.on('click', '.holidays', this.onlyHoliday);
			this.cont.on('click', '.every', this.everyDay);
			this.cont.on('click', '.t24', this.t24);
			this.cont.on('change', 'select', this.setInputValue);
		},
		dayClick: function() {
			Regime.changeDay($(this));
			Regime.checkAdd();
		},
		plusClick: function() {
			var newRegime = Regime.cont.find('.regime_part:first').clone();
			newRegime.children('span').each(function() {
				$(this).removeClass('active');
				var cl = $(this).attr('class');
				if (!$('.regime_part .active.' + cl).length)
					$(this).addClass('active');
			});
			newRegime.appendTo(Regime.cont);
			Regime.cont.addClass('hide_add');
			Regime.cont.removeClass('hide_remove');
			Regime.setInputValue();
		},
		minusClick: function() {
			$(this).closest('.regime_part').remove();
			Regime.checkAdd();
		},
		onlyWork: function() {
			var part = $(this).closest('.regime_part');
			for (var i = 0; i < 7; i++) {
				var span = part.children('.dw' + i);
				if (i < 5) {
					if (!span.hasClass('active'))
						Regime.changeDay(span);
				}
				else {
					if (span.hasClass('active'))
						Regime.changeDay(span);
				}
			}
			Regime.checkAdd();
		},
		onlyHoliday: function() {
			var part = $(this).closest('.regime_part');
			for (var i = 0; i < 7; i++) {
				var span = part.children('.dw' + i);
				if (i < 5) {
					if (span.hasClass('active'))
						Regime.changeDay(span);
				}
				else {
					if (!span.hasClass('active'))
						Regime.changeDay(span);
				}
			}
			Regime.checkAdd();
		},
		everyDay: function() {
			var part = $(this).closest('.regime_part');
			for (var i = 0; i < 7; i++) {
				var span = part.children('.dw' + i);
				if (!span.hasClass('active'))
					Regime.changeDay(span);
			}
			Regime.checkAdd();
		},
		t24: function() {
			if ($(this).hasClass('disabled'))
				return;
			var part = $(this).closest('.regime_part');
			part.children('.from_h').val('00');
			part.children('.from_m').val('00');
			part.children('.to_h').val('24');
			part.children('.to_m').val('00');
			Regime.setInputValue();
		},
		changeDay: function(target) {
			var cl = '';
			if (target.hasClass('active')) {
				target.removeClass('active');
			}
			else {
				cl = target.attr('class');
				Regime.cont.find('.regime_part .active.' + cl).removeClass('active');
				target.addClass('active');
			}
		},
		checkAdd: function() {
			var canAdd = true;
			var parts = Regime.cont.find('.regime_part');
			parts.each(function () {
				var l = $(this).children('.active').length;
				if (l) {
					$(this).children('select').removeAttr('disabled');
					$(this).children('.t24').removeClass('disabled');
				}
				else {
					$(this).children('select').attr('disabled', 'disabled');
					$(this).children('.t24').addClass('disabled');
					canAdd = false;
				}
			});
			if (canAdd) {
				canAdd = false;
				for (var i = 0; i < 7; i++) {
					if (!Regime.cont.find('.regime_part .active.dw' + i).length) {
						canAdd = true;
						break;
					}
				}
			}
			if (canAdd)
				Regime.cont.removeClass('hide_add');
			else
				Regime.cont.addClass('hide_add');
			if (parts.length > 1)
				Regime.cont.removeClass('hide_remove');
			else
				Regime.cont.addClass('hide_remove');

			Regime.setInputValue();
		},
		setInputValue: function() {
			var val = '';
			var text = '';
			var gtext = '';
			var day1 = -1;
			var day2 = -1;
			var prevtime = '';
			var prevtimeText = '';
			for (var i = 0; i < 7; i++) {
				var span = Regime.cont.find('.active.dw' + i);
				var time = '';
				var timeText = '';
				if (span.length) {
					var part = span.parent();
					var fh = part.children('.from_h').val();
					var fm = part.children('.from_m').val();
					var th = part.children('.to_h').val();
					var tm = part.children('.to_m').val();
					time = fh + ';' + fm + ';' + th + ';' + tm;
					timeText = fh + ':' + fm + '-' + th + ':' + tm;
					if (time == prevtime) {
						day2 = i;
					}
					else {
						if (day1 >= 0) {
							val += ';' + day1 + ';' + day2 + ';' + prevtime;
							text += ', ' + (day1 == day2 ? Regime.days[day1] : Regime.days[day1] + '-' + Regime.days[day2]) + ' ' + prevtimeText;
						}
						day1 = i;
						day2 = i;
					}
				}
				else {
					if (day1 >= 0) {
						val += ';' + day1 + ';' + day2 + ';' + prevtime;
						text += ', ' + (day1 == day2 ? Regime.days[day1] : Regime.days[day1] + '-' + Regime.days[day2]) + ' ' + prevtimeText;
					}
					day1 = -1;
					day2 = -1;
				}

				prevtime = time;
				prevtimeText = timeText;

				if (!gtext)
					gtext = ' - Часы работы сегодня · ' + timeText;
			}
			if (day1 >= 0) {
				val += ';' + day1 + ';' + day2 + ';' + prevtime;
				text += ', ' + (day1 == day2 ? Regime.days[day1] : Regime.days[day1] + '-' + Regime.days[day2]) + ' ' + prevtimeText;
			}
			if (val) {
				val = val.substr(1);
				text = text.substr(2);
			}

			Regime.input.val(val);
			if (Vcard) {
				Vcard.yandexRegime.text(text);
				Vcard.googleRegime.text(gtext);
			}
		}
	};
}
