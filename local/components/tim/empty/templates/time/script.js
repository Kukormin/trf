$(document).ready(function() {
	var regime = 0;
	var tableTd = $('.time_table .dw_row span');
	var table = $('.time_table');
	var bot = $('.time_table .bot');
	var formInput = $('input[name=time]');
	var work = $('.work');
	var popup = $('#timeModal');
	var btnSave = popup.find('.btn-primary');

	$('#timePopup').click(function () {
		var val = formInput.val();
		var ar = val.split(',');
		var k = 0;
		table.find('.dw_row').each(function() {
			$(this).children('span').each(function() {
				var v = ar[k];
				var cl = v == 1 ? 't100' : 't0';
				$(this).attr('class', cl);
				k++;
			});
		});
		for (var i = 0; i < 7; i++) {
			checkRow(i);
		}
		for (var j = 0; j < 24; j++) {
			checkCol(j);
		}
		workCount();

		popup.modal('show');
		return false;
	});

	tableTd.mousedown(function(e) {
		e.stopPropagation();
		e.cancelBubble = true;
		var td = $(this);
		if (td.hasClass('t100')) {
			sqOff(td);
			regime = 1;
		}
		else {
			sqOn(td);
			regime = 2;
		}
	});
	$(document).mouseup(function() {
		regime = 0;
	});
	tableTd.mousemove(function() {
		if (regime == 1)
			sqOff($(this));
		else if (regime == 2)
			sqOn($(this));
	});
	$(document).on("dragstart", function (e) { e.preventDefault(); } );

	table.mouseleave(function() {
		regime = 0;
	});

	function sqOn(td) {
		if (td.attr('class') != 't100')
		{
			td.attr('class', 't100');
			checkRow(td.parent().index());
			checkCol(td.index() - 1);
			workCount();
		}
	}
	function sqOff(td) {
		if (td.attr('class') == 't100')
		{
			td.attr('class', 't0');
			checkRow(td.parent().index());
			checkCol(td.index() - 1);
			workCount();
		}
	}

	function checkRow(i) {
		var row = table.find('.dw_row:eq(' + i + ')');
		var input = row.find('dd > input').get(0);
		var all = true;
		row.children('span').each(function() {
			if (!$(this).hasClass('t100')) {
				all = false;
				return false;
			}
		});
		if (all && !input.checked)
			input.checked = true;
		else if (!all && input.checked)
			input.checked = false;
	}
	function checkCol(j) {
		var span = bot.children('span:eq(' + j + ')');
		var input = span.find('input').get(0);
		var all = true;
		table.find('.dw_row').each(function() {
			var td = $(this).children('span:eq(' + j + ')');
			if (!td.hasClass('t100')) {
				all = false;
				return false;
			}
		});
		if (all && !input.checked)
			input.checked = true;
		else if (!all && input.checked)
			input.checked = false;
	}

	table.find('.dw_row input').click(function() {
		var cl = $(this).prop('checked') ? 't100' : 't0';
		var row = $(this).closest('.dw_row');
		row.children('span').attr('class', cl);
		for (var j = 0; j < 24; j++) {
			checkCol(j);
		}
		workCount();
	});
	bot.find('input').click(function() {
		var cl = $(this).prop('checked') ? 't100' : 't0';
		var j = $(this).closest('span').index() - 1;
		table.find('.dw_row').each(function() {
			var td = $(this).children('span:eq(' + j + ')');
			td.attr('class', cl);
		});
		for (var i = 0; i < 7; i++) {
			checkRow(i);
		}
		workCount();
	});

	var correct = false;
	function workCount() {
		var cnt = table.find('.work_day span.t100').length;
		correct = cnt >= 40
		if (correct) {
			work.parent().removeClass('text-error');
			btnSave.removeClass('disabled');
		}
		else {
			work.parent().addClass('text-error');
			btnSave.addClass('disabled');
		}

		work.text(cnt);
	}

	btnSave.click(function() {
		if (correct) {
			var val = '';
			table.find('.dw_row').each(function() {
				$(this).children('span').each(function() {
					var cl = $(this).attr('class');
					var v = cl == 't100' ? '1' : '0';
					if (val)
						val += ',';
					val += v;
				});
			});
			formInput.val(val);
			popup.modal('hide');
		}
	});

});