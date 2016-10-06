$(document).ready(function() {
	var formInput = $('input[name=regions]');
	var valSpan = $('#regions_text');
	var popup = $('#regionsModal');
	var btnSave = popup.find('.btn-primary');
	var tree = $('.region_tree');
	var inputs = tree.find('input');
	var regionSearch = $('#region_search');
	var regionsBody = popup.find('.modal-body');

	$('#regionsPopup').click(function () {
		tree.find('.r.st-e').attr('class', 'r st-c');
		inputs.prop('checked', false);
		regionSearch.val('');
		tree.find('label.finded').each(function() {
			$(this).html($(this).text());
			$(this).removeClass('finded');
		});

		var val = formInput.val();
		var ar = val.split(',');
		for (var i in ar) {
			var id = ar[i];
			var div = tree.find('#r' + id);
			var input = div.children('input');
			input.prop('checked', true);
			div.find('.c input').prop('checked', true);
			div.parents('.r').attr('class', 'r st-e');
		}

		popup.modal('show');
		return false;
	});

	function getVal(div) {
		var ret = '';
		div.children('.r').each(function() {
			var input = $(this).children('input');
			var ch = input.prop('checked');
			if (ch) {
				if (ret)
					ret += ',';
				ret += input.attr('id').substr(2);
			}
			else {
				var children = $(this).children('.c');
				if (children.length) {
					var val = getVal(children);
					if (val) {
						if (ret)
							ret += ',';
						ret += val;
					}
				}
			}
		});
		return ret;
	}

	function getText(div) {
		var ret = '';
		div.children('.r').each(function() {
			var input = $(this).children('input');
			var ch = input.prop('checked');
			if (ch) {
				if (ret)
					ret += ', ';
				ret += input.siblings('label').text();
			}
			else {
				var children = $(this).children('.c');
				if (children.length) {
					var val = getText(children);
					if (val) {
						if (ret)
							ret += ', ';
						ret += val;
					}
				}
			}
		});
		return ret;
	}

	btnSave.click(function() {
		formInput.val(getVal(tree));
		valSpan.text(getText(tree));
		popup.modal('hide');
	});
	valSpan.text(getText(tree));

	$('.region_tree .r span').click(function() {
		var div = $(this).parent();
		if (div.hasClass('st-e'))
		{
			div.attr('class', 'r st-c');
		}
		else if (div.hasClass('st-c'))
		{
			div.attr('class', 'r st-e');
		}
	});

	inputs.click(function() {
		var div = $(this).parent();
		var checked = $(this).prop('checked');

		// Вниз
		div.find('.c input').prop('checked', checked);

		// Наверх
		checkParents(div, checked);
	});

	function checkParents(div, checked) {
		var parent = div.parent();
		if (!parent.hasClass('c'))
			return;

		if (checked)
			div.siblings().each(function() {
				var ch = $(this).children('input').prop('checked');
				if (!ch) {
					checked = false;
					return false;
				}
			});
		var input = parent.siblings('input');
		if (input.prop('checked') != checked) {
			input.prop('checked', checked);
			checkParents(parent.parent(), checked);
		}
	}

	regionSearch.bind('keypress', function(e) {
		if (e.keyCode == 13) {
			e.preventDefault();
			search();
		}
	});

	// allRegions заполняется в php
	regionSearch.typeahead({
		source: allRegions,
		updater: function (item) {
			search(item);
			return item;
		}
	});

	function search(text) {
		if (!text)
			text = regionSearch.val().trim().toLowerCase();
		if (text) {
			var first = false;
			text = text.toLowerCase();
			var l = text.length;
			tree.find('label').each(function() {
				var orig = $(this).text();
				var name = orig.toLowerCase();
				var p = name.indexOf(text);
				if (p >= 0) {
					var part1 = orig.substr(0, p);
					var part2 = orig.substr(p, l);
					var part3 = orig.substr(p + l);
					$(this).html(part1 + '<b>' + part2 + '</b>' + part3);
					$(this).addClass('finded');
					$(this).parent().parents('.r').attr('class', 'r st-e');
					if (!first)
						first = $(this);
				}
				else if ($(this).hasClass('finded'))
				{
					$(this).html(orig);
					$(this).removeClass('finded');
				}
			});
			if (first) {
				var cur = regionsBody.scrollTop();
				var top = first.position().top;
				var dest = cur + top - 20;
				regionsBody.animate({scrollTop: dest}, 300);
			}
		}
	}

	$('#region_search_btn').click(function() {
		search();
	});

});