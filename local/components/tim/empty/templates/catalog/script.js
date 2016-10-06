$(document).ready(function() {
	var formInput = $('input[name=catalog]');
	var tree = $('.catalog');
	var inputs = tree.find('input');

	// ------
	tree.find('.r.st-e').attr('class', 'r st-c');
	inputs.prop('checked', false);

	var val = formInput.val();
	var ar = val.split(',');
	for (var i in ar) {
		var id = ar[i];
		var input = tree.find('input#cb' + id);
		var div = input.parent();
		input.prop('checked', true);
		div.parents('.r').attr('class', 'r st-e');
	}

	function getVal() {
		var ret = '';
		inputs.each(function() {
			var input = $(this);
			var ch = input.prop('checked');
			if (ch) {
				if (ret)
					ret += ',';
				ret += input.attr('id').substr(2);
			}
		});
		return ret;
	}

	tree.on('click', '.r span.ce', function() {
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
		var val = getVal();
		formInput.val(val);
	});

	var dmenu = $('#catalog_menu');
	var to_up = dmenu.find('#to_up');
	var to_0 = dmenu.find('#to_0');
	var selitem;
	tree.on('click', '.icon-list', function(e) {
		e.stopPropagation();
		selitem = $(this);
		var div = selitem.parent();
		if (div.parent().hasClass('catalog_tree')) {
			to_up.parent().addClass('disabled');
			to_0.parent().addClass('disabled');
		}
		else {
			to_up.parent().removeClass('disabled');
			to_0.parent().removeClass('disabled');
		}
		var pos = selitem.position();
		dmenu.css({left: pos.left, top: pos.top + 18});
		dmenu.show();
	});

	$(document).on('click', function() {
		if (selitem) {
			dmenu.hide();
			selitem = null;
		}
	});

	dmenu.find('#delete').click(function() {
		if (selitem) {
			selitem.parent().remove();
		}
		dmenu.hide();
		return false;
	});
	dmenu.find('#all_cat').click(function() {
		if (selitem) {
			var div = selitem.parent();
			div.find('input.sections').prop('checked', true);
			if (div.hasClass('st-c'))
			{
				div.attr('class', 'r st-e');
			}
		}
		dmenu.hide();
		return false;
	});
	dmenu.find('#all_prod').click(function() {
		if (selitem) {
			var div = selitem.parent();
			div.find('input.products').prop('checked', true);
			if (div.hasClass('st-c'))
			{
				div.attr('class', 'r st-e');
			}
		}
		dmenu.hide();
		return false;
	});
	to_up.click(function(e) {
		e.stopPropagation();
		if (to_up.parent().hasClass('disabled'))
			return false;
		if (selitem) {
			var div = selitem.parent();
			div.parent().parent().before(div);
		}
		dmenu.hide();
		return false;
	});
	to_0.click(function(e) {
		e.stopPropagation();
		if (to_0.parent().hasClass('disabled'))
			return false;
		if (selitem) {
			var div = selitem.parent();
			var parent = div;
			while (parent.parent().parent().hasClass('r'))
				parent = parent.parent().parent();
			parent.before(div);
		}
		dmenu.hide();
		return false;
	});

	dmenu.find('#to_cat').click(function() {
		if (selitem) {
			tree.addClass('copyRegime');
			selitem.parent().addClass('target');
		}
		dmenu.hide();
		return false;
	});

	tree.on('click', 'label', function(e) {
		if (tree.hasClass('copyRegime')) {
			tree.removeClass('copyRegime');
			if (selitem) {
				selitem.parent().removeClass('target');
				var new_par = $(this).siblings('.c');
				if (!new_par.length) {
					$(this).parent().append('<div class="c"></div>');
					new_par = $(this).siblings('.c');
				}
				new_par.append(selitem.parent());
				$(this).parent().attr('class', 'r st-e');
			}
		}
	});

	dmenu.find('#add_s').click(function() {
		$('#addCampModal').modal('show');
		dmenu.hide();
		return false;
	});

	$('#addCampPopup').click(function () {
		$('#addCampModal').modal('show');
		return false;
	});

	$('#addCampModal .btn-primary').click(function() {
		var url = $('#add_url').val();
		var name = $('#add_name').val();
		if (url && name) {
			var html = '<div class="r" id="rc"><span class="ce"></span> <span class="icon-list"></span> ' +
				'<input class="sections" type="checkbox" id="cbc" checked="checked" /> ' +
				'<label for="cbc">' + name + '</label></div>';
			if (selitem) {
				var new_par = selitem.siblings('.c');
				if (!new_par.length) {
					selitem.parent().append('<div class="c"></div>');
					new_par = selitem.siblings('.c');
				}
				new_par.append(html);
				selitem.parent().attr('class', 'r st-e');
			}
			else
				$('.catalog_tree').append(html);
			$('#addCampModal').modal('hide');
		}
	});



});