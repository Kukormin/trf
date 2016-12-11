if (siteOptions.viewPage) {
	var View = {
		name: '',
		nameTimerId: 0,
		dragRegime: false,
		draggedDiv: false,
		draggedCont: false,
		draggedIndex: 0,
		draggedReq: false,
		x: 0,
		y: 0,
		leftLevels: [],
		rightLevels: [],
		rightX: 0,
		inLeft: false,
		init: function () {
			this.form = $('#view_detail');
			this.btnSave = this.form.find('.btn-primary');
			this.btnCancel = this.form.find('.cancel');
			this.nameInput = this.form.find('#name');
			this.nameControlGroup = this.nameInput.closest('.control-group');
			this.nameHelp = this.nameControlGroup.find('.help-inline');
			this.constructor = this.form.find('.view_constuctor');
			this.left = this.form.find('.view_columns.left');
			this.right = this.form.find('.view_columns.right');

			this.initCheck();

			this.nameInput.on('input', this.checkName);
			this.btnSave.click(this.saveSettings);
			this.btnCancel.click(CMN.historyBack);
			this.constructor.on('mousedown', '.view_column b', this.remove);
			this.constructor.on('mousedown', '.view_column', this.mouseDown);
			CMN.body.on('mousemove', this.mouseMove);
			CMN.body.on('mouseup', this.mouseUp);

		},
		initCheck: function() {
			this.name = this.nameInput.val();
			if (!this.name)
				View.btnSave.prop('disabled', true);
		},
		checkName: function () {
			var newName = View.nameInput.val();
			if (View.name == newName)
				return;

			View.name = newName;
			if (View.nameTimerId)
				clearTimeout(View.nameTimerId);
			if (View.name) {
				View.nameControlGroup.removeClass('error');
				View.nameHelp.text('');
				View.nameTimerId = setTimeout(View.saveSettingsAjax, 500);
				View.btnSave.prop('disabled', false);
			}
			else {
				View.nameControlGroup.addClass('error');
				View.nameHelp.text('Введите название');
				View.btnSave.prop('disabled', true);
			}
		},
		saveSettingsAjax: function (save) {
			var addParams = '';
			if (!save)
				addParams = 'only_check=Y';
			else {
				View.left.children().each(function() {
					if (addParams)
						addParams += '&';
					addParams += 'columns[]=' + $(this).data('code');
				});
			}
			CMN.ajax('view_save', {
				form: View.form,
				post: addParams,
				strategy: 2,
				overlay: save
			}, function (data) {
				if (data.EX) {
					View.nameControlGroup.addClass('error');
					View.nameHelp.text('Вид с таким именем уже существует');
					View.btnSave.prop('disabled', true);
				}
				else {
					View.nameControlGroup.removeClass('error');
					View.nameHelp.text('');
					View.btnSave.prop('disabled', false);
				}
			});
		},
		saveSettings: function () {
			if (View.nameTimerId)
				clearTimeout(View.nameTimerId);

			View.saveSettingsAjax(true);

			return false;
		},
		remove: function (e) {
			e.stopPropagation();

			var cont = $(this).parent().parent();
			View.right.append(cont);

			return false;
		},
		mouseDown: function (e) {
			View.dragRegime = true;
			View.draggedDiv = $(this);
			View.draggedCont = View.draggedDiv.parent();
			View.draggedReq = !View.draggedDiv.children('b').length;
			var pos = View.draggedDiv.position();
			View.x = e.pageX - pos.left;
			View.y = e.pageY - pos.top;
			View.draggedDiv.css({
				left: pos.left,
				top: pos.top
			});
			View.draggedCont.addClass('target');
			View.draggedDiv.addClass('target');

			View.setLevels();

			return false;
		},
		/**
		 * Обработка перемещения выделения
		 * @param e
		 * @returns {boolean}
		 */
		mouseMove: function(e) {
			e.stopPropagation();

			if (!View.dragRegime)
				return false;

			var l = e.pageX - View.x;
			var t = e.pageY - View.y;

			View.draggedDiv.css({
				left: l,
				top: t
			});

			var toLeft = e.pageX < View.rightX || View.draggedReq;

			var levels = View.rightLevels;
			if (toLeft)
				levels = View.leftLevels;
			var len = levels.length;
			var index = -1;
			for (var i = 0; i < len; i++) {
				var y = levels[i];
				if (t < y) {
					index = i;
					break;
				}
			}
			if (index == -1)
				index = len;

			if (toLeft != View.inLeft || index < View.draggedIndex || index > View.draggedIndex + 1) {
				var targetPanel = toLeft ? View.left : View.right;
				if (index == len)
					targetPanel.append(View.draggedCont);
				else
					targetPanel.children('div:eq(' + index + ')').before(View.draggedCont);
				View.setLevels();
			}

			return false;
		},
		/**
		 * Конец выделения
		 */
		mouseUp: function() {
			if (!View.dragRegime)
				return false;

			View.draggedCont.removeClass('target');
			View.draggedDiv.removeClass('target');

			View.dragRegime = false;
		},
		setLevels: function() {
			View.leftLevels = [];
			View.left.children().each(function(index) {
				var pos = $(this).position();
				var height = $(this).height() + 2;
				View.leftLevels[index] = pos.top + height / 2;
			});
			View.rightLevels = [];
			View.right.children().each(function(index) {
				var pos = $(this).position();
				var height = $(this).height() + 2;
				View.rightLevels[index] = pos.top + height / 2;
			});
			var pos = View.right.offset();
			View.rightX = pos.left;
			View.draggedIndex = View.draggedCont.index();
			View.inLeft = View.draggedCont.parent().is('.left');
		}
	};
}
