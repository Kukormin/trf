if (siteOptions.categoryPage) {
	var CategoryPage = {
		detail: false,
		settingsForm: false,
		btnSave: false,
		nameInput: false,
		schemeInput: false,
		host: '',
		pathInput: false,
		resInput: false,
		nameControlGroup: false,
		nameHelp: false,
		resControlGroup: false,
		resHelp: false,
		name: '',
		nameTimerId: 0,
		urlTimerId: 0,
		init: function () {
			this.detail = $('#category_detail');
			this.settingsForm = this.detail.find('#settings_form');
			this.btnSave = this.settingsForm.find('#save_settings');
			this.nameInput = this.settingsForm.find('#name');
			this.schemeInput = this.settingsForm.find('#scheme');
			this.host = this.settingsForm.find('#host').val();
			this.pathInput = this.settingsForm.find('#path');
			this.resInput = this.settingsForm.find('#res');
			this.nameControlGroup = this.nameInput.closest('.control-group');
			this.resControlGroup = this.resInput.closest('.control-group');
			this.nameHelp = this.nameControlGroup.find('.help-inline');
			this.resHelp = this.resControlGroup.find('.help-inline');
			this.initCheck();
			this.nameInput.on('input', this.checkCategoryName);
			this.schemeInput.on('input', this.checkHref);
			this.pathInput.on('input', this.checkHref);
			this.btnSave.click(this.saveSettings);
		},
		initCheck: function() {
			this.name = this.nameInput.val();
			if (!this.name)
				CategoryPage.btnSave.prop('disabled', true);
		},
		checkCategoryName: function () {
			var newName = CategoryPage.nameInput.val();
			if (CategoryPage.name == newName)
				return;

			CategoryPage.name = newName;
			if (CategoryPage.nameTimerId)
				clearTimeout(CategoryPage.nameTimerId);
			if (CategoryPage.name) {
				CategoryPage.nameControlGroup.removeClass('error');
				CategoryPage.nameHelp.text('');
				CategoryPage.btnSave.prop('disabled', false);
				CategoryPage.nameTimerId = setTimeout(CategoryPage.saveSettingsAjax, 500);
			}
			else {
				CategoryPage.nameControlGroup.addClass('error');
				CategoryPage.nameHelp.text('Введите название категории');
				CategoryPage.btnSave.prop('disabled', true);
			}
		},
		checkHref: function() {
			var res = CategoryPage.schemeInput.val() + '://' + CategoryPage.host + CategoryPage.pathInput.val();
			CategoryPage.resInput.val(res);
			if (CategoryPage.urlTimerId)
				clearTimeout(CategoryPage.urlTimerId);
			CategoryPage.urlTimerId = setTimeout(CategoryPage.getSite, 500);
		},
		getSite: function() {
			CategoryPage.resControlGroup.find('.loader').addClass('inp');
			CategoryPage.resHelp.text('');
			CMN.ajax('get_site', {
				form: CategoryPage.resControlGroup,
				post: 'url=' + CategoryPage.resInput.val(),
				strategy: 2
			}, function(data) {
				if (data.error)
				{
					CategoryPage.resControlGroup.addClass('warning');
					CategoryPage.resHelp.text(data.error);
				}
				else
				{
					CategoryPage.resControlGroup.removeClass('warning');
				}
			});
		},
		saveSettingsAjax: function (save) {
			var addParams = '';
			if (!save)
				addParams = 'only_check=Y';
			CMN.ajax('category_save_settings', {
				form: CategoryPage.settingsForm,
				post: addParams,
				strategy: 2,
				overlay: save
			}, function (data) {
				if (data.EX) {
					CategoryPage.nameControlGroup.addClass('error');
					CategoryPage.nameHelp.text('Категория с таким именем уже существует');
					CategoryPage.btnSave.prop('disabled', true);
				}
				else {
					CategoryPage.nameControlGroup.removeClass('error');
					CategoryPage.nameHelp.text('');
					CategoryPage.btnSave.prop('disabled', false);
					if (data.name)
						Titles.changeCategoryName(data.name);
				}
			});
		},
		saveSettings: function () {
			if (CategoryPage.nameTimerId)
				clearTimeout(CategoryPage.nameTimerId);

			CategoryPage.saveSettingsAjax(true);

			return false;
		}
	};

	var Words = {
		data: [],
		req: [],
		dataCols: 0,
		dataRows: 0,
		editedTd: false,
		selectedTd: false,
		endTd: false,
		currentTd: false,
		selectedRegime: false,
		tableKeys: false,
		pasteRegime: false,
		history: [],
		lc: 0,
		rc: 0,
		tr: 0,
		br: 0,
		init: function () {
			this.baseForm = $('#base_words');
			this.table = this.baseForm.find('#base_words_table');
			this.tableBody = this.table.find('tbody');
			this.editDiv = this.baseForm.find('#base_words_wrap .edit');
			this.editInput = this.editDiv.find('input');
			this.copyTextarea = $('#copy_textarea');
			this.totalSpan = this.baseForm.find('#total_cnt');
			this.maxColsInput = this.baseForm.find('#max');
			this.saveBaseBtn = this.baseForm.find('.btn-primary');

			this.addForm = $('#additional_words');
			this.saveAddBtn = this.addForm.find('.btn-primary');

			this.initData();
			this.calcComboCnt(true);

			this.table.on('mousedown', 'td:not(.f)', this.mouseDown);
			this.table.on('mousemove', 'td:not(.f)', this.mouseMove);
			this.table.on('mouseup', 'td:not(.f)', this.mouseUp);
			this.table.on('dblclick', 'td:not(.f)', this.dblClick);
			CMN.body.on('keydown', this.keyDown);
			CMN.body.on('mousedown', this.bodyMouseDown);
			CMN.body.on('mouseup', this.bodyMouseUp);
			this.table.on('click', 'th i', this.iClick);
			this.table.on('click', 'th input', this.reqClick);
			this.editInput.blur(this.editWordEnd);
			this.editInput.keypress(this.editKey);
			this.copyTextarea.on('input', this.paste);
			this.maxColsInput.on('input', this.calcComboCnt);
			this.saveBaseBtn.click(this.generate);

			this.saveAddBtn.click(this.saveAdd);
		},
		initData: function() {
			Words.dataRows = 0;
			Words.tableBody.children().each(function(j) {
				$(this).children().each(function(i) {
					if (i) {
						if (!j)
							Words.data[i] = [];
						Words.data[i][j] = $(this).text();
					}
				});
				Words.dataRows++;
			});
			Words.dataCols = Words.data.length;
			Words.table.find('tr:eq(1)').children().each(function(i) {
				if (i) {
					var input = $(this).find('input');
					Words.req[i] = input.prop('checked');
				}
			});
		},
		/**
		 * Определение левого верхнего и правого нижнего углов
		 */
		setSelectedArea: function() {
			var sTdCol = Words.selectedTd.index();
			var sTdRow = Words.selectedTd.parent().index();
			var eTdCol = Words.endTd.index();
			var eTdRow = Words.endTd.parent().index();

			Words.lc = sTdCol;
			Words.rc = eTdCol;
			if (eTdCol < sTdCol) {
				Words.lc = eTdCol;
				Words.rc = sTdCol;
			}
			Words.tr = sTdRow;
			Words.br = eTdRow;
			if (eTdRow < sTdRow) {
				Words.tr = eTdRow;
				Words.br = sTdRow;
			}
		},
		/**
		 * Обработка начала выделения
		 * @param e
		 * @returns {boolean}
		 */
		mouseDown: function(e) {
			var td = $(this);

			if (!e.shiftKey) {
				Words.table.find('td.area').removeClass('area');

				if (Words.selectedTd) {
					if (Words.selectedTd.get(0) == td.get(0))
						return false;
					Words.selectedTd.removeClass('selected');
				}
				Words.selectedTd = td;
				Words.selectedTd.addClass('selected');
			}
			else {
				if (!Words.selectedTd) {
					Words.selectedTd = td;
					Words.selectedTd.addClass('selected');
				}
			}
			Words.currentTd = e.target;
			Words.endTd = td;

			Words.selectedRegime = true;
			Words.setSelectedArea();
			if (e.shiftKey)
				Words.selectCells();
		},
		/**
		 * Обработка перемещения выделения
		 * @param e
		 * @returns {boolean}
		 */
		mouseMove: function(e) {
			e.stopPropagation();
			var target = e.target;
			if (target.getAttribute('unselectable') == 'on')
				target.ownerDocument.defaultView.getSelection().removeAllRanges();

			if (!Words.selectedRegime)
				return false;

			if (Words.currentTd == target)
				return false;

			Words.currentTd = target;
			Words.endTd = $(target);

			Words.setSelectedArea();
			Words.selectCells();

			return false;
		},
		/**
		 * Конец выделения
		 * @param e
		 * @returns {boolean}
		 */
		mouseUp: function(e) {
			e.stopPropagation();
			Words.selectedRegime = false;

			return false;
		},
		/**
		 * Конец выделения
		 */
		bodyMouseUp: function() {
			Words.selectedRegime = false;
		},
		/**
		 * Снимем режим обработки нажатия клавиш, если вышли за таблицу
		 * @param e
		 */
		bodyMouseDown: function(e) {
			Words.tableKeys = $(e.target).closest('#base_words_table').length ? true : false;
		},
		/**
		 * Разные действия с таблицей при нажатии клавиш
		 * @param e
		 * @returns {boolean}
		 */
		keyDown: function(e) {
			if (Words.endTd && Words.tableKeys && e.target.tagName != 'INPUT') {
				var ctrlKey = e.ctrlKey || e.metaKey;
				var tr = false;
				var nextTr = false;
				var nextTd = false;
				if (e.key == 'ArrowDown') {
					tr = Words.endTd.closest('tr');
					nextTr = tr.next();
					if (nextTr.length)
						nextTd = nextTr.children('td:eq(' + Words.endTd.index() + ')');
				}
				else if (e.key == 'ArrowUp') {
					tr = Words.endTd.closest('tr');
					nextTr = tr.prev();
					if (nextTr.length)
						nextTd = nextTr.children('td:eq(' + Words.endTd.index() + ')');
				}
				else if (e.key == 'ArrowLeft') {
					if (Words.endTd.index() > 1)
						nextTd = Words.endTd.prev();
				}
				else if (e.key == 'ArrowRight') {
					nextTd = Words.endTd.next();
				}
				else if (e.key == 'Tab') {
					nextTd = Words.endTd.next();
					if (!nextTd.length) {
						tr = Words.endTd.closest('tr');
						nextTr = tr.next();
						if (nextTr.length)
							nextTd = nextTr.children('td:eq(1)');
						else {
							Words.addRow();
							nextTr = tr.next();
							nextTd = nextTr.children('td:eq(1)');
						}
					}
				}
				else if (e.key == 'Delete' || e.key == 'Backspace') {
					Words.deleteCells();
					return false;
				}
				else if (e.key == 'Home') {
					if (Words.endTd.index() > 1) {
						tr = Words.endTd.closest('tr');
						nextTd = tr.children('td:eq(1)');
					}
					else
						return false;
				}
				else if (e.key == 'End') {
					tr = Words.endTd.closest('tr');
					nextTd = tr.children('td:last');
					if (Words.endTd.index() == nextTd.index())
						return false;
				}
				else if (e.which == 67 && ctrlKey && !e.shiftKey && !e.altKey) {
					Words.copyCells();
				}
				else if (e.which == 86 && ctrlKey && !e.shiftKey && !e.altKey) {
					Words.pasteCells();
				}
				else if (e.which == 88 && ctrlKey && !e.shiftKey && !e.altKey) {
					Words.cutCells();
				}
				else if (e.which == 90 && ctrlKey && !e.shiftKey && !e.altKey) {
					Words.undo();
				}
				else {
					if (!ctrlKey && !e.altKey) {
						if (e.key.length == 1 || e.key == 'Enter') {
							Words.startEditTd(Words.endTd, e.key == 'Enter');
							if (e.key == 'Enter')
								return false;
						}
					}
				}

				if (nextTd && nextTd.length) {
					if (e.shiftKey) {
						Words.endTd = nextTd;
						Words.setSelectedArea();
						Words.selectCells();
					}
					else {
						Words.unselectCells();

						if (Words.selectedTd)
							Words.selectedTd.removeClass('selected');
						Words.selectedTd = nextTd;
						Words.selectedTd.addClass('selected');
						Words.endTd = nextTd;
						Words.setSelectedArea();
					}

					return false;
				}
			}
		},
		/**
		 * Удаление значений в выбранных ячейках
		 * @returns {boolean}
		 */
		deleteCells: function() {
			var historyItem = [];
			for (var row = Words.tr; row <= Words.br; row++) {
				var tr = Words.tableBody.find('tr:eq(' + row + ')');
				for (var col = Words.lc; col <= Words.rc; col++) {
					var td = tr.find('td:eq(' + col + ')');
					var oldText = td.text();
					if (oldText != '') {
						td.text('');
						Words.data[col][row] = '';
						historyItem.push([col, row, oldText]);
					}
				}
			}

			if (historyItem.length) {
				Words.calcComboCnt();
				Words.history.push(historyItem);
			}
		},
		/**
		 * Обновляем область выделения над ячейками
		 * @returns {boolean}
		 */
		selectCells: function() {
			Words.unselectCells();
			for (var row = Words.tr; row <= Words.br; row++) {
				var tr = Words.tableBody.find('tr:eq(' + row + ')');
				for (var col = Words.lc; col <= Words.rc; col++) {
					var td = tr.find('td:eq(' + col + ')');
					td.addClass('area');
				}
			}
		},
		/**
		 * Сбрасываем область выделения
		 */
		unselectCells: function() {
			Words.table.find('td.area').removeClass('area');
		},
		/**
		 * Копирование выделенных ячеек
		 * @returns {boolean}
		 */
		copyCells: function() {
			var text = '';
			for (var row = Words.tr; row <= Words.br; row++) {
				var tr = Words.tableBody.find('tr:eq(' + row + ')');
				for (var col = Words.lc; col <= Words.rc; col++) {
					var td = tr.find('td:eq(' + col + ')');
					var sep = col == Words.rc ? (row == Words.br ? '' : '\n') : '\t';
					text += td.text() + sep;
				}
			}
			Words.copyTextarea.val(text).focus();
			Words.copyTextarea.get(0).select();
		},
		/**
		 * Вырезать (копирование + очистка ячеек)
		 */
		cutCells: function() {
			var text = '';
			var historyItem = [];
			for (var row = Words.tr; row <= Words.br; row++) {
				var tr = Words.tableBody.find('tr:eq(' + row + ')');
				for (var col = Words.lc; col <= Words.rc; col++) {
					var td = tr.find('td:eq(' + col + ')');
					var sep = col == Words.rc ? (row == Words.br ? '' : '\n') : '\t';
					var oldText = td.text();
					text += oldText + sep;
					if (oldText != '') {
						td.text('');
						Words.data[col][row] = '';
						historyItem.push([col, row, oldText]);
					}
				}
			}
			Words.copyTextarea.val(text).focus();
			Words.copyTextarea.get(0).select();
			if (historyItem.length) {
				Words.calcComboCnt();
				Words.history.push(historyItem);
			}
		},
		/**
		 * Вставка
		 */
		pasteCells: function() {
			Words.copyTextarea.val('').focus();
			Words.pasteRegime = true;
		},
		paste: function() {
			if (Words.pasteRegime) {
				var historyItem = [];
				var colRange = Words.rc - Words.lc;
				var rowRange = Words.br - Words.tr;
				var inRange = colRange > 0 || rowRange > 0;

				var text = Words.copyTextarea.val();
				var rows = text.split('\n');
				var rowsCount = rows.length;

				var maxRowIndex = inRange ? Words.tr + rowRange : Words.tr + rowsCount;
				var curRowsCount = Words.tableBody.find('tr').length;
				while (maxRowIndex >= curRowsCount)
				{
					Words.addRow();
					maxRowIndex--;
				}

				for (var i = 0; i < rowsCount; i++) {
					if (inRange && i > rowRange)
						break;

					var row = Words.tr + i;
					var cols = rows[i].split('\t');
					var colsCount = cols.length;
					var tr = Words.tableBody.find('tr:eq(' + row + ')');

					for (var j = 0; j < colsCount; j++) {
						if (inRange && j > colRange)
							break;

						var col = Words.lc + j;
						var word = cols[j];
						var td = tr.find('td:eq(' + col + ')');
						var oldText = td.text();
						if (oldText != word) {
							td.text(word);
							Words.data[col][row] = word;
							historyItem.push([col, row, oldText]);
						}
					}
				}
				Words.pasteRegime = false;

				if (historyItem.length) {
					Words.calcComboCnt();
					Words.history.push(historyItem);
				}
			}
		},
		/**
		 * Выделение вертикального столбца при клике на "Обязательно"
		 */
		reqClick: function() {
			var index = $(this).closest('th').index();
			var checked = $(this).prop('checked');
			Words.tableBody.find('tr').each(function() {
				var td = $(this).children('td:eq(' + index + ')');
				if (checked)
					td.addClass('req');
				else
					td.removeClass('req');
			});
			Words.req[index] = checked;
			Words.calcComboCnt();
		},
		/**
		 * Входим режим редактирования ячейки при двойном клике
		 * @returns {boolean}
		 */
		dblClick: function() {
			Words.startEditTd($(this), true);
		},
		/**
		 * Показываем инпут для редактирования ячейки
		 * @param td
		 * @param exValue
		 */
		startEditTd: function(td, exValue) {
			Words.editedTd = td;
			var pos = td.position();
			var text = '';
			if (exValue)
				text = td.text();
			Words.editInput.val(text).width(td.width());
			Words.editDiv.addClass('editting').css({
				top: pos.top,
				left: pos.left
			});
			Words.editInput.focus();
			Words.checkAddRow(td);
		},
		/**
		 * Проверяет, нужно ли добавить новую строку и добавляет если нужно
		 * @param td
		 */
		checkAddRow: function(td) {
			var tr = td.parent();
			var nextTr = tr.next();
			if (!nextTr.length)
				Words.addRow();
		},
		/**
		 * Добавление строки в конец
		 */
		addRow: function() {
			var tr = Words.tableBody.find('tr:last');
			var newTr = tr.clone();
			newTr.children().each(function(i) {
				var td = $(this);
				td.removeClass('area');
				td.removeClass('selected');
				if (i)
					td.text('');
				else
					td.text(parseInt(td.text()) + 1);
			});
			Words.tableBody.append(newTr);
			for (var j = 1; j < Words.dataCols; j++)
				Words.data[j][Words.dataRows] = '';
			Words.dataRows++;
		},
		/**
		 * Выход из режима редактирования ячейки
		 * @returns {boolean}
		 */
		editWordEnd: function() {
			var oldText = Words.editedTd.text();
			var text = Words.editInput.val();
			Words.editDiv.removeClass('editting');
			if (oldText != text) {
				Words.editedTd.text(text);
				var col = Words.editedTd.index();
				var row = Words.editedTd.parent().index();
				Words.data[col][row] = text;
				Words.calcComboCnt();
				var historyItem = [[col, row, oldText]];
				Words.history.push(historyItem);
			}
		},
		/**
		 * Действия при нажатии кнопки в поле редактирования ячейки
		 * @param e
		 * @returns {boolean}
		 */
		editKey: function(e) {
			var td = Words.editedTd;
			if (!td)
				return false;

			var tr = false;
			var nextTr = false;
			var nextTd = false;
			if (e.key == 'ArrowDown' || e.key == 'Enter') {
				tr = td.closest('tr');
				nextTr = tr.next();
				if (nextTr.length)
					nextTd = nextTr.children('td:eq(' + td.index() + ')');
			}
			else if (e.key == 'ArrowUp') {
				tr = td.closest('tr');
				nextTr = tr.prev();
				if (nextTr.length)
					nextTd = nextTr.children('td:eq(' + td.index() + ')');
			}
			else if (e.key == 'Tab') {
				nextTd = td.next();
				if (!nextTd.length) {
					tr = td.closest('tr');
					nextTr = tr.next();
					if (nextTr.length)
						nextTd = nextTr.children('td:eq(1)');
				}
			}

			if (nextTd && nextTd.length) {
				Words.editWordEnd();

				if (Words.selectedTd)
					Words.selectedTd.removeClass('selected');
				Words.selectedTd = nextTd;
				Words.selectedTd.addClass('selected');
				Words.endTd = nextTd;
				Words.setSelectedArea();

				e.stopPropagation();
				return false;
			}
		},
		/**
		 * Добавление столбца
		 */
		iClick: function() {
			var th = $(this).parent();
			var parent = th.parent();
			var l = parent.children().length;
			var char = String.fromCharCode(64 + l);
			var i = l < 26 ? '<i></i>' : '';
			parent.append('<th>' + i + char + '</th>');
			parent.next().append('<th><label><input type="checkbox" /> Обязательно</label></th>');
			Words.tableBody.find('tr').each(function () {
				$(this).append('<td></td>');
			});
			$(this).remove();

			Words.req[Words.dataCols] = false;
			Words.data[Words.dataCols] = [];
			for (var j = 0; j < Words.dataRows; j++)
				Words.data[Words.dataCols][j] = '';
			Words.dataCols++;
		},
		undo: function() {
			var historyItem = Words.history.pop();
			var l = historyItem.length;
			for (var i = 0; i < l; i++) {
				var col = historyItem[i][0];
				var row = historyItem[i][1];
				var text = historyItem[i][2];
				var td = Words.tableBody.find('tr:eq(' + row + ')').find('td:eq(' + col + ')');
				td.text(text);
				Words.data[col][row] = text;
			}
			Words.calcComboCnt();
		},
		/**
		 * Подсчет количества комбинаций
		 * @param init
		 */
		calcComboCnt: function(init) {
			var reqCnt = 0;
			var words = [];
			var result = 1;
			var reqResult = 1;
			var counts = [];
			var disabled = false;
			Words.tableBody.find('td.error').removeClass('error');
			for (var j = 1; j < Words.dataCols; j++) {
				var req = Words.req[j];
				if (req)
					reqCnt++;
				var cnt = 0;
				for (var i = 0; i < Words.dataRows; i++) {
					var word = Words.data[j][i];
					if (word) {
						if (words[word]) {
							Words.setErrorClass(i, j);
							Words.setErrorClass(words[word][0], words[word][1]);
							disabled = true;
						}
						else {
							words[word] = [i, j];
							cnt++;
						}
					}
				}
				if (cnt) {
					if (!req) {
						cnt++;
						counts.push(cnt);
					}
					else {
						reqResult *= cnt;
					}
					result *= cnt;
				}
			}
			var max = Words.maxColsInput.val();
			if (!max)
				max = 4;
			max -= reqCnt;
			if (max < 0) {
				result = 0;
			}
			else if (max == 0) {
				result = reqResult;
			}
			else if (max > 0 && counts.length > max) {
				var x = Words.f(counts, counts.length, 0, 0, 1, max);
				result = reqResult * x;
			}
			if (!reqCnt && result)
				result--;
			Words.totalSpan.text(result);
			Words.saveBaseBtn.prop('disabled', disabled);
			if (!init)
				Words.saveBase();
		},
		setErrorClass: function(row, col) {
			Words.tableBody.children('tr:eq(' + row + ')').children('td:eq(' + col + ')').addClass('error');
		},
		f: function(counts, l, level, cnt, current, max) {
			if (cnt < max && level < l) {
				var x1 = Words.f(counts, l, level + 1, cnt + 1, current * (counts[level] - 1), max);
				var x2 = Words.f(counts, l, level + 1, cnt, current, max);
				return x1 + x2;
			}
			else {
				return current;
			}
		},
		saveBase: function() {
			var add = '';
			for (var j = 1; j < Words.dataCols; j++) {
				var req = Words.req[j];
				if (req)
					add += '&r[' + (j-1) + ']=1';
				for (var i = 0; i < Words.dataRows; i++) {
					var word = Words.data[j][i];
					add += '&w[' + (j-1) + '][' + i + ']=' + word;
				}
			}
			CMN.ajax('category_save_base', {
				form: Words.baseForm,
				post: add,
				strategy: 2,
				hideloader: true
			}, false);
			return false;
		},
		generate: function() {
			CMN.ajax('category_generate', {
				form: Words.baseForm,
				strategy: 1
			}, function () {
				if (siteOptions.keygroupFilters)
					KeyGroupList.getKeyGroups();
			});
			return false;
		},
		saveAdd: function() {
			CMN.ajax('category_save_additional', {
				form: Words.addForm
			}, false);
			return false;
		}
	};
}