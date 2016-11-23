if (siteOptions.templPage) {
	var Templ = {
		name: '',
		nameTimerId: 0,
		previewTimerId: 0,
		fieldsError: false,
		nameError: false,
		init: function () {
			this.form = $('#templ_detail');
			this.btnSave = this.form.find('.btn-primary');
			this.btnCancel = this.form.find('.cancel');
			this.nameInput = this.form.find('#name');
			this.nameControlGroup = this.nameInput.closest('.control-group');
			this.nameHelp = this.nameControlGroup.find('.help-inline');
			this.example = this.form.find('.example');

			this.initCheck();
			this.setPartsInit();
			this.nameInput.on('input', this.checkName);
			this.btnSave.click(this.saveSettings);
			this.btnCancel.click(CMN.historyBack);
			this.form.find('input[name="title_len"]').click(this.preview);
			this.form.find('input[type="checkbox"]').click(this.preview);
			this.form.on('input', 'input[type="text"]', this.preview);
			this.form.on('change', '.part select', this.changePart);
			this.form.find('.add-part').click(this.addPart);
			this.form.on('click', '.icon-remove', this.removePart);
			this.form.on('click', '.part-type .tgl > span', this.toggleWords);
		},
		initCheck: function() {
			this.name = this.nameInput.val();
			if (!this.name)
				Templ.nameError = true;
			Templ.btnDisabled();
			Templ.preview();
		},
		btnDisabled: function() {
			Templ.btnSave.prop('disabled', Templ.fieldsError || Templ.nameError);
		},
		toggleWords: function() {
			var div = $(this).parent().next();
			div.toggle();
		},
		checkName: function () {
			var newName = Templ.nameInput.val();
			if (Templ.name == newName)
				return;

			Templ.name = newName;
			if (Templ.nameTimerId)
				clearTimeout(Templ.nameTimerId);
			if (Templ.name) {
				Templ.nameControlGroup.removeClass('error');
				Templ.nameHelp.text('');
				Templ.nameError = false;
				Templ.nameTimerId = setTimeout(Templ.saveSettingsAjax, 500);
			}
			else {
				Templ.nameControlGroup.addClass('error');
				Templ.nameHelp.text('Введите название шаблона');
				Templ.nameError = true;
			}
			Templ.btnDisabled();
		},
		saveSettingsAjax: function (save) {
			var addParams = '';
			if (!save)
				addParams = 'only_check=Y';
			CMN.ajax('templ_save', {
				form: Templ.form,
				post: addParams,
				strategy: 2,
				overlay: save
			}, function (data) {
				if (data.EX) {
					Templ.nameControlGroup.addClass('error');
					Templ.nameHelp.text('Шаблон с таким именем уже существует');
					Templ.nameError = true;
				}
				else {
					Templ.nameControlGroup.removeClass('error');
					Templ.nameHelp.text('');
					Templ.nameError = false;
				}
				Templ.btnDisabled();
			});
		},
		saveSettings: function () {
			if (Templ.nameTimerId)
				clearTimeout(Templ.nameTimerId);

			Templ.saveSettingsAjax(true);

			return false;
		},
		previewAjax: function () {
			CMN.ajax('templ_preview', {
				form: Templ.form,
				strategy: 2,
				quick: true
			}, function (data) {
				Templ.example.html(data);
			});
		},
		preview: function () {
			if (Templ.previewTimerId)
				clearTimeout(Templ.previewTimerId);

			Templ.previewTimerId = setTimeout(Templ.previewAjax, 1000);

			return false;
		},
		selectPart: function(select) {
			var part = select.closest('.part');
			var curr = part.find('.part-type.type-' + select.val());
			var prev = curr.siblings('.selected');
			curr.addClass('selected');
			curr.find('.num').prop('disabled', false);
			prev.removeClass('selected');
			prev.find('.num').prop('disabled', true);
		},
		changePart: function() {
			var select = $(this);
			Templ.selectPart(select);
			this.preview();
		},
		addPart: function() {
			var parts = $(this).parent().siblings('.templ-parts');
			var part = parts.children('.part:last').clone();
			var select = part.find('select');
			select.val('text');
			Templ.setPartIndex(part, parts.children('.part').length);
			Templ.selectPart(select);
			part.appendTo(parts);
			parts.children().removeClass('single');
		},
		removePart: function() {
			var part = $(this).parent();
			var parts = part.parent();
			part.remove();
			part = parts.children();
			if (part.length == 1)
				part.addClass('single');
		},
		setPartsInit: function() {
			var parts = $('.templ-parts').children('.part');
			parts.each(function(i) {
				Templ.setPartIndex($(this), i);
			});
		},
		setPartIndex: function(part, index) {
			part.find('.num').each(function() {
				var input = $(this);
				var name = input.data('name');
				name = name.split('index').join(index);
				input.attr('name', name);
			});
		}
	};
}
