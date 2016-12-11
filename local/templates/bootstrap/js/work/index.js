if (siteOptions.indexPage) {
	var Marks = {
		form: false,
		rowsDiv: false,
		addRowBtn: false,
		saveBtn: false,
		init: function () {
			this.form = $('#mark-form');
			this.rowsDiv = this.form.find('.rows');
			this.addRowBtn = this.form.find('.add-row');
			this.saveBtn = this.form.find('.btn-primary');

			this.addRowBtn.click(this.addRow);
			this.saveBtn.click(this.save);
			this.form.on('input', '.color', this.changeColor);
		},
		addRow: function() {
			var html = '<div class="control-group"><input type="text" placeholder="Название" name="mark[]" />' +
				' #<input type="text" class="color" placeholder="Код цвета" name="color[]" /> <i class="mark"></i></div>';
			Marks.rowsDiv.append(html);
		},
		changeColor: function() {
			var col = $(this).val();
			if (col.substr(0, 1) == '#')
				col = col.substr(1);
			col = col ? ('#' + col) : 'none';
			var i = $(this).siblings('i');
			i.css('background', col);
		},
		save: function() {
			CMN.ajax('mark_save', {
				form: Marks.form,
				strategy: 2,
				overlay: true
			}, false);
			return false;
		}
	};

	var ViewList = {
		init: function () {
			$('.view_delete').click(this.viewDelete);
		},
		viewDelete: function() {
			var a = $(this);
			var id = a.data('id');
			var row = a.closest('tr');
			row.remove();
			CMN.ajax('view_delete', {
				strategy: 1,
				post: 'id=' + id
			}, false);
			return false;
		}
	};
}
