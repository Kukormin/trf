if (siteOptions.categoryPage) {
	var Combo = {
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
			this.resCont = $('.result_cont');
			this.table = this.baseForm.find('#base_words_table');
			this.tableBody = this.table.find('tbody');
			this.editDiv = this.baseForm.find('#base_words_wrap .edit');
			this.editInput = this.editDiv.find('input');
			this.copyTextarea = $('#copy_textarea');
			this.totalSpan = this.baseForm.find('#total_cnt');
			this.maxColsInput = this.baseForm.find('#max');
			this.saveBaseBtn = this.baseForm.find('.btn-primary');

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
			this.resCont.on('click', 'input.cb', this.cbClick);
			this.resCont.on('click', 'input.all', this.allClick);
			this.resCont.on('click', 'input.new', this.newClick);
			this.resCont.on('click', '.ws_apply', this.wsApply);
			this.resCont.on('click', 'b', this.addKg);
			this.resCont.on('click', '.ws', this.ws);
			this.resCont.on('click', '.add_selected', this.addSelectedKg);
		},
		initData: function() {
			Combo.dataRows = 0;
			Combo.tableBody.children().each(function(j) {
				$(this).children().each(function(i) {
					if (i) {
						if (!j)
							Combo.data[i] = [];
						Combo.data[i][j] = $(this).text();
					}
				});
				Combo.dataRows++;
			});
			Combo.dataCols = Combo.data.length;
			Combo.table.find('tr:eq(1)').children().each(function(i) {
				if (i) {
					var input = $(this).find('input');
					Combo.req[i] = input.prop('checked');
				}
			});
		},
		/**
		 * Определение левого верхнего и правого нижнего углов
		 */
		setSelectedArea: function() {
			var sTdCol = Combo.selectedTd.index();
			var sTdRow = Combo.selectedTd.parent().index();
			var eTdCol = Combo.endTd.index();
			var eTdRow = Combo.endTd.parent().index();

			Combo.lc = sTdCol;
			Combo.rc = eTdCol;
			if (eTdCol < sTdCol) {
				Combo.lc = eTdCol;
				Combo.rc = sTdCol;
			}
			Combo.tr = sTdRow;
			Combo.br = eTdRow;
			if (eTdRow < sTdRow) {
				Combo.tr = eTdRow;
				Combo.br = sTdRow;
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
				Combo.table.find('td.area').removeClass('area');

				if (Combo.selectedTd) {
					if (Combo.selectedTd.get(0) == td.get(0))
						return false;
					Combo.selectedTd.removeClass('selected');
				}
				Combo.selectedTd = td;
				Combo.selectedTd.addClass('selected');
			}
			else {
				if (!Combo.selectedTd) {
					Combo.selectedTd = td;
					Combo.selectedTd.addClass('selected');
				}
			}
			Combo.currentTd = e.target;
			Combo.endTd = td;

			Combo.selectedRegime = true;
			Combo.setSelectedArea();
			if (e.shiftKey)
				Combo.selectCells();
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

			if (!Combo.selectedRegime)
				return false;

			if (Combo.currentTd == target)
				return false;

			Combo.currentTd = target;
			Combo.endTd = $(target);

			Combo.setSelectedArea();
			Combo.selectCells();

			return false;
		},
		/**
		 * Конец выделения
		 * @param e
		 * @returns {boolean}
		 */
		mouseUp: function(e) {
			e.stopPropagation();
			Combo.selectedRegime = false;

			return false;
		},
		/**
		 * Конец выделения
		 */
		bodyMouseUp: function() {
			Combo.selectedRegime = false;
		},
		/**
		 * Снимем режим обработки нажатия клавиш, если вышли за таблицу
		 * @param e
		 */
		bodyMouseDown: function(e) {
			Combo.tableKeys = $(e.target).closest('#base_words_table').length ? true : false;
		},
		/**
		 * Разные действия с таблицей при нажатии клавиш
		 * @param e
		 * @returns {boolean}
		 */
		keyDown: function(e) {
			if (Combo.endTd && Combo.tableKeys && e.target.tagName != 'INPUT') {
				var ctrlKey = e.ctrlKey || e.metaKey;
				var tr = false;
				var nextTr = false;
				var nextTd = false;
				if (e.key == 'ArrowDown') {
					tr = Combo.endTd.closest('tr');
					nextTr = tr.next();
					if (nextTr.length)
						nextTd = nextTr.children('td:eq(' + Combo.endTd.index() + ')');
				}
				else if (e.key == 'ArrowUp') {
					tr = Combo.endTd.closest('tr');
					nextTr = tr.prev();
					if (nextTr.length)
						nextTd = nextTr.children('td:eq(' + Combo.endTd.index() + ')');
				}
				else if (e.key == 'ArrowLeft') {
					if (Combo.endTd.index() > 1)
						nextTd = Combo.endTd.prev();
				}
				else if (e.key == 'ArrowRight') {
					nextTd = Combo.endTd.next();
				}
				else if (e.key == 'Tab') {
					nextTd = Combo.endTd.next();
					if (!nextTd.length) {
						tr = Combo.endTd.closest('tr');
						nextTr = tr.next();
						if (nextTr.length)
							nextTd = nextTr.children('td:eq(1)');
						else {
							Combo.addRow();
							nextTr = tr.next();
							nextTd = nextTr.children('td:eq(1)');
						}
					}
				}
				else if (e.key == 'Delete' || e.key == 'Backspace') {
					Combo.deleteCells();
					return false;
				}
				else if (e.key == 'Home') {
					if (Combo.endTd.index() > 1) {
						tr = Combo.endTd.closest('tr');
						nextTd = tr.children('td:eq(1)');
					}
					else
						return false;
				}
				else if (e.key == 'End') {
					tr = Combo.endTd.closest('tr');
					nextTd = tr.children('td:last');
					if (Combo.endTd.index() == nextTd.index())
						return false;
				}
				else if (e.which == 67 && ctrlKey && !e.shiftKey && !e.altKey) {
					Combo.copyCells();
				}
				else if (e.which == 86 && ctrlKey && !e.shiftKey && !e.altKey) {
					Combo.pasteCells();
				}
				else if (e.which == 88 && ctrlKey && !e.shiftKey && !e.altKey) {
					Combo.cutCells();
				}
				else if (e.which == 90 && ctrlKey && !e.shiftKey && !e.altKey) {
					Combo.undo();
				}
				else {
					if (!ctrlKey && !e.altKey) {
						if (e.key.length == 1 || e.key == 'Enter') {
							Combo.startEditTd(Combo.selectedTd, e.key == 'Enter');
							if (e.key == 'Enter')
								return false;
						}
					}
				}

				if (nextTd && nextTd.length) {
					if (e.shiftKey) {
						Combo.endTd = nextTd;
						Combo.setSelectedArea();
						Combo.selectCells();
					}
					else {
						Combo.unselectCells();

						if (Combo.selectedTd)
							Combo.selectedTd.removeClass('selected');
						Combo.selectedTd = nextTd;
						Combo.selectedTd.addClass('selected');
						Combo.endTd = nextTd;
						Combo.setSelectedArea();
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
			for (var row = Combo.tr; row <= Combo.br; row++) {
				var tr = Combo.tableBody.find('tr:eq(' + row + ')');
				for (var col = Combo.lc; col <= Combo.rc; col++) {
					var td = tr.find('td:eq(' + col + ')');
					var oldText = td.text();
					if (oldText != '') {
						td.text('');
						Combo.data[col][row] = '';
						historyItem.push([col, row, oldText]);
					}
				}
			}

			if (historyItem.length) {
				Combo.calcComboCnt();
				Combo.history.push(historyItem);
			}
		},
		/**
		 * Обновляем область выделения над ячейками
		 * @returns {boolean}
		 */
		selectCells: function() {
			Combo.unselectCells();
			for (var row = Combo.tr; row <= Combo.br; row++) {
				var tr = Combo.tableBody.find('tr:eq(' + row + ')');
				for (var col = Combo.lc; col <= Combo.rc; col++) {
					var td = tr.find('td:eq(' + col + ')');
					td.addClass('area');
				}
			}
		},
		/**
		 * Сбрасываем область выделения
		 */
		unselectCells: function() {
			Combo.table.find('td.area').removeClass('area');
		},
		/**
		 * Копирование выделенных ячеек
		 * @returns {boolean}
		 */
		copyCells: function() {
			var text = '';
			for (var row = Combo.tr; row <= Combo.br; row++) {
				var tr = Combo.tableBody.find('tr:eq(' + row + ')');
				for (var col = Combo.lc; col <= Combo.rc; col++) {
					var td = tr.find('td:eq(' + col + ')');
					var sep = col == Combo.rc ? (row == Combo.br ? '' : '\n') : '\t';
					text += td.text() + sep;
				}
			}
			Combo.copyTextarea.val(text).focus();
			Combo.copyTextarea.get(0).select();
		},
		/**
		 * Вырезать (копирование + очистка ячеек)
		 */
		cutCells: function() {
			var text = '';
			var historyItem = [];
			for (var row = Combo.tr; row <= Combo.br; row++) {
				var tr = Combo.tableBody.find('tr:eq(' + row + ')');
				for (var col = Combo.lc; col <= Combo.rc; col++) {
					var td = tr.find('td:eq(' + col + ')');
					var sep = col == Combo.rc ? (row == Combo.br ? '' : '\n') : '\t';
					var oldText = td.text();
					text += oldText + sep;
					if (oldText != '') {
						td.text('');
						Combo.data[col][row] = '';
						historyItem.push([col, row, oldText]);
					}
				}
			}
			Combo.copyTextarea.val(text).focus();
			Combo.copyTextarea.get(0).select();
			if (historyItem.length) {
				Combo.calcComboCnt();
				Combo.history.push(historyItem);
			}
		},
		/**
		 * Вставка
		 */
		pasteCells: function() {
			Combo.copyTextarea.val('').focus();
			Combo.pasteRegime = true;
		},
		paste: function() {
			if (Combo.pasteRegime) {
				var historyItem = [];
				var colRange = Combo.rc - Combo.lc;
				var rowRange = Combo.br - Combo.tr;
				var inRange = colRange > 0 || rowRange > 0;

				var text = Combo.copyTextarea.val();
				var rows = text.split('\n');
				var rowsCount = rows.length;

				var maxRowIndex = inRange ? Combo.tr + rowRange : Combo.tr + rowsCount;
				var curRowsCount = Combo.tableBody.find('tr').length;
				while (maxRowIndex >= curRowsCount)
				{
					Combo.addRow();
					maxRowIndex--;
				}

				for (var i = 0; i < rowsCount; i++) {
					if (inRange && i > rowRange)
						break;

					var row = Combo.tr + i;
					var cols = rows[i].split('\t');
					var colsCount = cols.length;
					var tr = Combo.tableBody.find('tr:eq(' + row + ')');

					for (var j = 0; j < colsCount; j++) {
						if (inRange && j > colRange)
							break;

						var col = Combo.lc + j;
						var word = cols[j];
						var td = tr.find('td:eq(' + col + ')');
						var oldText = td.text();
						if (oldText != word) {
							td.text(word);
							Combo.data[col][row] = word;
							historyItem.push([col, row, oldText]);
						}
					}
				}
				Combo.pasteRegime = false;

				if (historyItem.length) {
					Combo.calcComboCnt();
					Combo.history.push(historyItem);
				}
			}
		},
		/**
		 * Выделение вертикального столбца при клике на "Обязательно"
		 */
		reqClick: function() {
			var index = $(this).closest('th').index();
			var checked = $(this).prop('checked');
			Combo.tableBody.find('tr').each(function() {
				var td = $(this).children('td:eq(' + index + ')');
				if (checked)
					td.addClass('req');
				else
					td.removeClass('req');
			});
			Combo.req[index] = checked;
			Combo.calcComboCnt();
		},
		/**
		 * Входим режим редактирования ячейки при двойном клике
		 * @returns {boolean}
		 */
		dblClick: function() {
			Combo.startEditTd($(this), true);
		},
		/**
		 * Показываем инпут для редактирования ячейки
		 * @param td
		 * @param exValue
		 */
		startEditTd: function(td, exValue) {
			Combo.editedTd = td;
			var pos = td.position();
			var text = '';
			if (exValue)
				text = td.text();
			Combo.editInput.val(text).width(td.width());
			Combo.editDiv.addClass('editting').css({
				top: pos.top,
				left: pos.left
			});
			Combo.editInput.focus();
			Combo.checkAddRow(td);
		},
		/**
		 * Проверяет, нужно ли добавить новую строку и добавляет если нужно
		 * @param td
		 */
		checkAddRow: function(td) {
			var tr = td.parent();
			var nextTr = tr.next();
			if (!nextTr.length)
				Combo.addRow();
		},
		/**
		 * Добавление строки в конец
		 */
		addRow: function() {
			var tr = Combo.tableBody.find('tr:last');
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
			Combo.tableBody.append(newTr);
			for (var j = 1; j < Combo.dataCols; j++)
				Combo.data[j][Combo.dataRows] = '';
			Combo.dataRows++;
		},
		/**
		 * Выход из режима редактирования ячейки
		 * @returns {boolean}
		 */
		editWordEnd: function() {
			var oldText = Combo.editedTd.text();
			var text = Combo.editInput.val();
			Combo.editDiv.removeClass('editting');
			if (oldText != text) {
				Combo.editedTd.text(text);
				var col = Combo.editedTd.index();
				var row = Combo.editedTd.parent().index();
				Combo.data[col][row] = text;
				Combo.calcComboCnt();
				var historyItem = [[col, row, oldText]];
				Combo.history.push(historyItem);
			}
		},
		/**
		 * Действия при нажатии кнопки в поле редактирования ячейки
		 * @param e
		 * @returns {boolean}
		 */
		editKey: function(e) {
			var td = Combo.editedTd;
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
				Combo.editWordEnd();

				if (Combo.selectedTd)
					Combo.selectedTd.removeClass('selected');
				Combo.selectedTd = nextTd;
				Combo.selectedTd.addClass('selected');
				Combo.endTd = nextTd;
				Combo.setSelectedArea();

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
			Combo.tableBody.find('tr').each(function () {
				$(this).append('<td></td>');
			});
			$(this).remove();

			Combo.req[Combo.dataCols] = false;
			Combo.data[Combo.dataCols] = [];
			for (var j = 0; j < Combo.dataRows; j++)
				Combo.data[Combo.dataCols][j] = '';
			Combo.dataCols++;
		},
		undo: function() {
			var historyItem = Combo.history.pop();
			var l = historyItem.length;
			for (var i = 0; i < l; i++) {
				var col = historyItem[i][0];
				var row = historyItem[i][1];
				var text = historyItem[i][2];
				var td = Combo.tableBody.find('tr:eq(' + row + ')').find('td:eq(' + col + ')');
				td.text(text);
				Combo.data[col][row] = text;
			}
			Combo.calcComboCnt();
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
			Combo.tableBody.find('td.error').removeClass('error');
			for (var j = 1; j < Combo.dataCols; j++) {
				var req = Combo.req[j];
				if (req)
					reqCnt++;
				var cnt = 0;
				for (var i = 0; i < Combo.dataRows; i++) {
					var word = Combo.data[j][i];
					if (word) {
						if (words[word]) {
							Combo.setErrorClass(i, j);
							Combo.setErrorClass(words[word][0], words[word][1]);
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
			var max = Combo.maxColsInput.val();
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
				var x = Combo.f(counts, counts.length, 0, 0, 1, max);
				result = reqResult * x;
			}
			if (!reqCnt && result)
				result--;
			Combo.totalSpan.text(result);
			Combo.saveBaseBtn.prop('disabled', disabled);
			if (!init)
				Combo.saveBase();
		},
		setErrorClass: function(row, col) {
			Combo.tableBody.children('tr:eq(' + row + ')').children('td:eq(' + col + ')').addClass('error');
		},
		f: function(counts, l, level, cnt, current, max) {
			if (cnt < max && level < l) {
				var x1 = Combo.f(counts, l, level + 1, cnt + 1, current * (counts[level] - 1), max);
				var x2 = Combo.f(counts, l, level + 1, cnt, current, max);
				return x1 + x2;
			}
			else {
				return current;
			}
		},
		saveBase: function() {
			var add = '';
			for (var j = 1; j < Combo.dataCols; j++) {
				var req = Combo.req[j];
				if (req)
					add += '&r[' + (j-1) + ']=1';
				for (var i = 0; i < Combo.dataRows; i++) {
					var word = Combo.data[j][i];
					add += '&w[' + (j-1) + '][' + i + ']=' + word;
				}
			}
			CMN.ajax('category_save_base', {
				form: Combo.baseForm,
				post: add,
				strategy: 2,
				hideloader: true
			}, false);
			return false;
		},
		generate: function() {
			CMN.ajax('category_combo', {
				form: Combo.baseForm,
				strategy: 1
			}, function (data) {
				if (data.HTML) {
					Combo.resCont.html(data.HTML);
					Combo.cbList = Combo.resCont.find('tbody input');
					Combo.allCb = Combo.resCont.find('thead input');
					Combo.cntSpan = Combo.resCont.find('.scnt');
					Combo.allSpan = Combo.resCont.find('.allcnt');
				}
			});
			return false;
		},
		cbClick: function() {
			var exNotChecked = Combo.cbList.not(':checked').length > 0;
			Combo.allCb.prop('checked', !exNotChecked);
			var cnt = Combo.cbList.filter(':checked').length;
			Combo.cntSpan.text(cnt);
		},
		allClick: function() {
			var cb = $(this);
			var all = cb.prop('checked');
			var cnt = 0;
			Combo.cbList.prop('checked', all);
			if (all)
				cnt = Combo.cbList.length;
			Combo.cntSpan.text(cnt);
		},
		newClick: function() {
			Combo.resCont.find('#combo_results').toggleClass('new');
		},
		wsApply: function() {
			var limit = parseInt(Combo.resCont.find('.ws_val').val());
			Combo.resCont.find('tbody tr').each(function() {
				var ws = parseInt($(this).data('ws'));
				var cb = $(this).find('.cb');
				cb.prop('checked', ws > limit);
			});
			var exNotChecked = Combo.cbList.not(':checked').length > 0;
			Combo.allCb.prop('checked', !exNotChecked);
			var cnt = Combo.cbList.filter(':checked').length;
			Combo.cntSpan.text(cnt);
			return false;
		},
		addKg: function() {
			var b = $(this);
			var cid = Combo.resCont.find('input[name=cid]').val();
			var pid = Combo.resCont.find('input[name=pid]').val();
			CMN.ajax('keygroup_add', {
				post: 'kgid=' + b.data('id') + '&base=' + b.data('base') + '&cid=' + cid + '&pid=' + pid,
				strategy: 1
			}, function (data) {
				if (data.OK) {
					var tr = b.closest('tr');
					Combo.updateRow(tr);
					Combo.cbList = Combo.resCont.find('tbody input');
					var cnt = Combo.cbList.length;
					Combo.allSpan.text(cnt);
					Combo.cbList.prop('checked', false);
					Combo.cntSpan.text('0');
				}
			});
			return false;
		},
		addSelectedKg: function() {
			var cid = Combo.resCont.find('input[name=cid]').val();
			var pid = Combo.resCont.find('input[name=pid]').val();
			CMN.ajax('category_combo_add', {
				form: Combo.resCont,
				strategy: 1
			}, function () {
				Combo.cbList.filter(':checked').each(function() {
					var tr = $(this).closest('tr');
					Combo.updateRow(tr);
				});
				Combo.cbList = Combo.resCont.find('tbody input');
				var cnt = Combo.cbList.length;
				Combo.allSpan.text(cnt);
				Combo.cbList.prop('checked', false);
				Combo.cntSpan.text('0');
			});
			return false;
		},
		ws: function() {
			CMN.ajax('category_ws', {
				form: Combo.baseForm,
				strategy: 1
			}, function (data) {

			});
			return false;
		},
		updateRow: function(tr) {
			tr.find('input').remove();
			tr.find('b').remove();
			tr.children('td:eq(3)').text('base');
		}
	};
}