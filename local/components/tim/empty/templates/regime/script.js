$(document).ready(function() {

	var regimeControls = $('#regime_controls');
	var regimeInput = $('#regime');
	regimeControls.on('click', '.regime_part > span', function() {
		changeDay($(this));
		checkRegimeAdd();
	});
	regimeControls.on('click', '.icon-plus-sign', function() {
		var newRegime = $('.regime_part:first').clone();
		newRegime.children('span').each(function() {
			$(this).removeClass('active');
			var cl = $(this).attr('class');
			if (!$('.regime_part .active.' + cl).length)
				$(this).addClass('active');
		});
		newRegime.appendTo(regimeControls);
		regimeControls.addClass('hide_add');
		regimeControls.removeClass('hide_remove');
		setInputValue();
	});
	regimeControls.on('click', '.icon-minus-sign', function() {
		$(this).closest('.regime_part').remove();
		checkRegimeAdd();
	});
	regimeControls.on('click', '.works', function() {
		var part = $(this).closest('.regime_part');
		for (var i = 0; i < 7; i++) {
			var span = part.children('.dw' + i);
			if (i < 5) {
				if (!span.hasClass('active'))
					changeDay(span);
			}
			else {
				if (span.hasClass('active'))
					changeDay(span);
			}
		}
		checkRegimeAdd();
	});
	regimeControls.on('click', '.holidays', function() {
		var part = $(this).closest('.regime_part');
		for (var i = 0; i < 7; i++) {
			var span = part.children('.dw' + i);
			if (i < 5) {
				if (span.hasClass('active'))
					changeDay(span);
			}
			else {
				if (!span.hasClass('active'))
					changeDay(span);
			}
		}
		checkRegimeAdd();
	});
	regimeControls.on('click', '.every', function() {
		var part = $(this).closest('.regime_part');
		for (var i = 0; i < 7; i++) {
			var span = part.children('.dw' + i);
			if (!span.hasClass('active'))
				changeDay(span);
		}
		checkRegimeAdd();
	});
	regimeControls.on('click', '.t24', function() {
		if ($(this).hasClass('disabled'))
			return;
		var part = $(this).closest('.regime_part');
		part.children('.from_h').val('00');
		part.children('.from_m').val('00');
		part.children('.to_h').val('24');
		part.children('.to_m').val('00');
		setInputValue();
	});
	regimeControls.on('change', 'select', function() {
		setInputValue();
	});

	function changeDay(target) {
		var cl = '';
		if (target.hasClass('active')) {
			target.removeClass('active');
		}
		else {
			cl = target.attr('class');
			$('.regime_part .active.' + cl).removeClass('active');
			target.addClass('active');
		}
	}

	function checkRegimeAdd() {
		var canAdd = true;
		$('.regime_part').each(function () {
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
				if (!$('.regime_part .active.dw' + i).length) {
					canAdd = true;
					break;
				}
			}
		}
		if (canAdd)
			regimeControls.removeClass('hide_add');
		else
			regimeControls.addClass('hide_add');
		if ($('.regime_part').length > 1)
			regimeControls.removeClass('hide_remove');
		else
			regimeControls.addClass('hide_remove');

		setInputValue();
	}

	function setInputValue() {
		var val = '';
		var day1 = -1;
		var day2 = -1;
		var prevtime = '';
		for (var i = 0; i < 7; i++) {
			var span = $('.active.dw' + i);
			var time = '';
			if (span.length) {
				var part = span.parent();
				time = part.children('.from_h').val() + ';' + part.children('.from_m').val() + ';' +
				part.children('.to_h').val() + ';' + part.children('.to_m').val();
				if (time == prevtime) {
					day2 = i;
				}
				else {
					if (day1 >= 0)
						val += ';' + day1 + ';' + day2 + ';' + prevtime;
					day1 = i;
					day2 = i;
				}
			}
			else {
				if (day1 >= 0)
					val += ';' + day1 + ';' + day2 + ';' + prevtime;
				day1 = -1;
				day2 = -1;
			}

			prevtime = time;
		}
		if (day1 >= 0)
			val += ';' + day1 + ';' + day2 + ';' + prevtime;
		if (val)
			val = val.substr(1);

		regimeInput.val(val);
	}

	checkRegimeAdd();

});