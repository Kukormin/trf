if (siteOptions.keygroupFilters) {
	var KeyGroupList = {
		selectedCount: 0,
		selectInputs: false,
		table: false,
		tableBody: false,
		allPageChecked: false,
		filterActive: false,
		editMode: false,
		selectedTd: false,
		editedTd: false,
		tableKeys: false,
		addTr: false,
		endTd: false,
		currentTd: false,
		selectedRegime: false,
		pasteRegime: false,
		platformTd: false,
		history: [],
		lc: 0,
		rc: 0,
		tr: 0,
		br: 0,
		platformTitle: {
			'ys': 'Яндекс.Поиск',
			'yn': 'Яндекс.Сети',
			'gs': 'Google.Поиск',
			'gn': 'Google.Сети'
		},
		init: function () {
			this.filtersForm = $('#keygroup-form');
			this.filtersBlock = $('#filters-block');
			this.btnApply = this.filtersForm.find('.btn-primary');
			this.pageInput = this.filtersForm.find('input[name="page"]');
			this.resultCont = $('#keygroup-table');
			this.editDiv = $('#keygroup-table-wrap').find('.edit');
			this.editInput = this.editDiv.find('input');
			this.multiNav = $('#multi-nav');
			this.countSpan = this.multiNav.find('#selected_count');
			this.allCountSpan = this.multiNav.find('#all_count');
			this.multiAction = this.multiNav.find('#multi-action');
			this.thisPage = this.multiNav.find('#this_page');
			this.allPage = this.multiNav.find('#all_page');
			this.toggleSelect = this.multiNav.find('#toggle_select');
			this.cancelSelect = this.multiNav.find('#cancel_select');
			this.filtersToggle = $('#filters_toogle');
			this.filtersToggleLi = this.filtersToggle.parent();
			this.filterActive = this.filtersToggleLi.hasClass('active');
			this.addFiltersMenu = $('#add_filter_menu');
			this.ygsn = $('#filter_ygsn');
			this.platform = $('#filter_platform');
			this.views = $('.views');
			this.viewsUm = $('#views-um');
			this.viewsEm = $('#views-em');
			this.deleteModal = $('#delete_modal');
			this.editRegime = $('#edit_regime');
			this.editMode = this.editRegime.is('.active');
			this.platformMenu = $('#platform-menu');
			this.copyTextarea = $('#kg_copy_textarea');

			this.filtersForm.on('submit', this.applyFilters);
			this.btnApply.click(this.applyFilters);
			this.thisPage.click(this.selectThisPage);
			this.allPage.click(this.selectAllPage);
			this.toggleSelect.click(this.toggleSelection);
			this.cancelSelect.click(this.cancelSelection);
			this.filtersToggle.click(this.toggleFilters);
			this.resultCont.on('click', '.pagination a', this.toPage);
			this.resultCont.on('click', '.select_item', this.selectItem);
			this.resultCont.on('click', '.btn.delete', this.deleteAd);
			this.resultCont.on('click', '.btn.add', this.addAdClick);
			this.resultCont.on('mousedown', 'td.e-td', this.mouseDown);
			this.resultCont.on('mousemove', 'td.e-td', this.mouseMove);
			this.resultCont.on('mouseup', 'td.e-td', this.mouseUp);
			this.resultCont.on('dblclick', 'td.e-td', this.dblClick);
			CMN.body.on('keydown', this.keyDown);
			CMN.body.on('mousedown', this.bodyMouseDown);
			this.copyTextarea.on('input', this.paste);
			this.editInput.blur(this.editWordEnd);
			this.editInput.keypress(this.editKey);
			this.multiNav.on('click', 'li.add_mark > ul a', this.addMark);
			this.multiNav.on('click', 'li.remove_mark > ul a', this.removeMark);
			this.multiNav.on('click', 'li.remove_all_mark > a', this.removeAllMark);
			this.multiNav.on('click', 'li.add_templ > ul a', this.addTempl);
			this.ygsn.on('click', 'div', this.ygsnClick);
			this.platform.on('click', 'div', this.platformClick);
			this.views.on('click', '.view_change', this.viewChange);
			this.addFiltersMenu.on('click', 'a', this.addFilterClick);
			$('#to-add-tab').click(this.toAddTab);
			$('#to-base-tab').click(this.toBaseTab);
			this.resultCont.on('change', '#size', this.sizeChange);
			this.deleteModal.on('click', '.btn-primary', this.deleteAdConfirm);
			this.editRegime.click(this.toggleEditMode);
			this.platformMenu.on('click', 'a', this.selectAdPlatform);

			this.getKeyGroups();
		},
		applyFilters: function () {
			KeyGroupList.pageInput.val(1);
			KeyGroupList.getKeyGroups();
			return false;
		},
		getKeyGroups: function (addParam, groupAction) {
			if (addParam)
				addParam += '&';
			else
				addParam = '';
			addParam += 'mode=ajax';
			if (groupAction) {
				var ids = '';
				var inputs = false;
				if (KeyGroupList.allPageChecked)
					inputs = KeyGroupList.selectInputs.not(':checked');
				else
					inputs = KeyGroupList.selectInputs.filter(':checked');
				inputs.each(function () {
					if (ids)
						ids += ',';
					ids += $(this).attr('id');
				});
				addParam += '&ids=' + ids;
				if (KeyGroupList.allPageChecked)
					addParam += '&select_all=1';
			}
			KeyGroupList.history = [];
			KeyGroupList.resultCont.addClass('process');
			KeyGroupList.resultCont.removeClass('long');
			setTimeout(KeyGroupList.showOverlay, 200);
			CMN.ajax('get_keygroups_by_filter', {
				form: KeyGroupList.filtersForm,
				post: addParam,
				strategy: 2,
				custom: true,
				quick: true
			}, function (data) {
				KeyGroupList.resultCont.removeClass('process');
				KeyGroupList.resultCont.html(data);
				KeyGroupList.table = KeyGroupList.resultCont.find('table');
				KeyGroupList.tableBody = KeyGroupList.table.find('tbody');
				KeyGroupList.selectInputs = KeyGroupList.table.find('input.select_item');
				var totalCnt = KeyGroupList.table.data('all');
				if (totalCnt)
					KeyGroupList.multiNav.show();
				else
					KeyGroupList.multiNav.hide();
				KeyGroupList.allCountSpan.text(totalCnt);
				if (!groupAction)
					KeyGroupList.allPageChecked = false;
				KeyGroupList.calcSelected();
			});
		},
		toggleEditMode: function() {
			KeyGroupList.editRegime.toggleClass('active');
			KeyGroupList.editMode = KeyGroupList.editRegime.is('.active');
			var id = 0;
			if (KeyGroupList.editMode) {
				KeyGroupList.resultCont.addClass('edit-mode');
				KeyGroupList.viewsEm.show();
				KeyGroupList.viewsUm.hide();
				id = KeyGroupList.viewsEm.find('.view_change.active').data('id');
			}
			else {
				KeyGroupList.resultCont.removeClass('edit-mode');
				KeyGroupList.viewsEm.hide();
				KeyGroupList.viewsUm.show();
				id = KeyGroupList.viewsUm.find('.view_change.active').data('id');
			}
			var em = KeyGroupList.editMode ? 1 : 0;
			KeyGroupList.getKeyGroups('view=' + id + '&em=' + em);
		},
		viewChange: function() {
			if ($(this).hasClass('active'))
				return false;

			var ul = $(this).closest('ul');
			var btn = ul.siblings('button');
			ul.find('.view_change.active').removeClass('active');
			$(this).addClass('active');
			btn.children('i').text($(this).text());

			var id = $(this).data('id');
			var em = KeyGroupList.editMode ? 1 : 0;
			KeyGroupList.getKeyGroups('view=' + id + '&em=' + em);
		},
		showOverlay: function() {
			KeyGroupList.resultCont.addClass('long');
		},
		toPage: function () {
			var page = $(this).data('page');
			if (page) {
				KeyGroupList.pageInput.val(page);
				KeyGroupList.getKeyGroups();
			}
			return false;
		},
		calcSelected: function() {
			if (KeyGroupList.allPageChecked)
				KeyGroupList.selectedCount = KeyGroupList.table.data('all') - KeyGroupList.selectInputs.not(':checked').length;
			else
				KeyGroupList.selectedCount = KeyGroupList.selectInputs.filter(':checked').length;
			KeyGroupList.countSpan.text(KeyGroupList.selectedCount);
			if (KeyGroupList.selectedCount > 0) {
				KeyGroupList.multiAction.removeClass('hidden');
				KeyGroupList.cancelSelect.parent().removeClass('disabled');
			}
			else {
				KeyGroupList.multiAction.addClass('hidden');
				KeyGroupList.cancelSelect.parent().addClass('disabled');
			}
		},
		selectItem: function () {
			KeyGroupList.calcSelected();
		},
		deleteAd: function () {
			KeyGroupList.deleteBtn = $(this);
			KeyGroupList.deleteModal.modal('show');
		},
		deleteAdConfirm: function () {
			var td = KeyGroupList.deleteBtn.closest('td');
			var tr = td.closest('tr');
			var id = tr.data('id');
			var kgid = tr.data('kgid');
			KeyGroupList.deleteModal.modal('hide');
			CMN.ajax('ad_delete', {
				form: KeyGroupList.filtersForm,
				post: 'adid=' + id + '&kgid=' + kgid,
				strategy: 1
			}, function() {
				td.siblings('.ad-td').add(td).html('');
			});
		},
		addAdClick: function() {
			var addedTr = KeyGroupList.table.children('.hidden').children('tr').clone();
			var tr = $(this).closest('tr');
			var cnt = tr.data('cnt') + 1;
			tr.data('cnt', cnt);
			tr.children('td.vs-td').attr('rowspan', cnt);
			var kgid = tr.data('kgid');
			addedTr.data('kgid', kgid);
			if (cnt > 1)
				tr = KeyGroupList.tableBody.children('tr[data-kgid=' + kgid + ']:last');
			tr.after(addedTr);
			return false;
		},
		selectThisPage: function () {
			KeyGroupList.allPageChecked = false;
			KeyGroupList.selectInputs.prop('checked', true);
			KeyGroupList.calcSelected();
		},
		selectAllPage: function () {
			KeyGroupList.allPageChecked = true;
			KeyGroupList.selectInputs.prop('checked', true);
			KeyGroupList.calcSelected();
		},
		toggleSelection: function () {
			KeyGroupList.allPageChecked = !KeyGroupList.allPageChecked;
			var selected = KeyGroupList.selectInputs.filter(':checked');
			var empty = KeyGroupList.selectInputs.not(':checked');
			selected.prop('checked', false);
			empty.prop('checked', true);
			KeyGroupList.calcSelected();
		},
		cancelSelection: function () {
			KeyGroupList.allPageChecked = false;
			KeyGroupList.selectInputs.prop('checked', false);
			KeyGroupList.calcSelected();
		},
		ygsnClick: function() {
			$(this).toggleClass('active');
			var id = $(this).data('id');
			var input = KeyGroupList.filtersForm.find('input[name=' + id + ']');
			var val = $(this).hasClass('active') ? 1 : 0;
			input.val(val);
			KeyGroupList.selectNavItems();
			KeyGroupList.getKeyGroups();
		},
		platformClick: function() {
			$(this).toggleClass('active');
			var id = $(this).data('id');
			var input = KeyGroupList.filtersForm.find('input[name=' + id + ']');
			var val = $(this).hasClass('active') ? 1 : 0;
			input.val(val);
			KeyGroupList.selectNavItems();
			KeyGroupList.getKeyGroups();
			var menuItem = KeyGroupList.platformMenu.find('a[data-code=' + id + ']');
			if (val)
				menuItem.parent().removeClass('disabled');
			else
				menuItem.parent().addClass('disabled');
		},
		selectNavItems: function() {
			/*var y = KeyGroupList.filtersForm.find('input[name=y]').val() == 1;
			 var g = KeyGroupList.filtersForm.find('input[name=g]').val() == 1;
			 var s = KeyGroupList.filtersForm.find('input[name=s]').val() == 1;
			 var n = KeyGroupList.filtersForm.find('input[name=n]').val() == 1;
			 var ys = $('.cat_selected_ys');
			 var yn = $('.cat_selected_yn');
			 var gs = $('.cat_selected_gs');
			 var gn = $('.cat_selected_gn');
			 if (y && s)
			 ys.addClass('selected');
			 else
			 ys.removeClass('selected');
			 if (y && n)
			 yn.addClass('selected');
			 else
			 yn.removeClass('selected');
			 if (g && s)
			 gs.addClass('selected');
			 else
			 gs.removeClass('selected');
			 if (g && n)
			 gn.addClass('selected');
			 else
			 gn.removeClass('selected');*/

			var ys = KeyGroupList.filtersForm.find('input[name=ys]').val() == 1;
			var yn = KeyGroupList.filtersForm.find('input[name=yn]').val() == 1;
			var gs = KeyGroupList.filtersForm.find('input[name=gs]').val() == 1;
			var gn = KeyGroupList.filtersForm.find('input[name=gn]').val() == 1;
			var ysi = $('.cat_selected_ys');
			var yni = $('.cat_selected_yn');
			var gsi = $('.cat_selected_gs');
			var gni = $('.cat_selected_gn');
			if (ys)
				ysi.addClass('selected');
			else
				ysi.removeClass('selected');
			if (yn)
				yni.addClass('selected');
			else
				yni.removeClass('selected');
			if (gs)
				gsi.addClass('selected');
			else
				gsi.removeClass('selected');
			if (gn)
				gni.addClass('selected');
			else
				gni.removeClass('selected');
		},
		toggleFilters: function() {
			KeyGroupList.filterActive = !KeyGroupList.filterActive;
			if (KeyGroupList.filterActive) {
				KeyGroupList.filtersToggleLi.addClass('active');
				KeyGroupList.filtersBlock.slideDown();
			}
			else {
				KeyGroupList.filtersToggleLi.removeClass('active');
				KeyGroupList.filtersBlock.slideUp();
			}
			CMN.ajax('user_save_data', {
				post: 'filters_show=' + (KeyGroupList.filterActive ? 1 : 0),
				strategy: 2
			}, false);
		},
		addMark: function () {
			var markId = $(this).data('id');
			var addParam = 'action=add_mark&add_mark=' + markId;
			KeyGroupList.getKeyGroups(addParam, true);
		},
		removeMark: function () {
			var markId = $(this).data('id');
			var addParam = 'action=remove_mark&add_mark=' + markId;
			KeyGroupList.getKeyGroups(addParam, true);
		},
		removeAllMark: function () {
			var addParam = 'action=remove_all_mark';
			KeyGroupList.getKeyGroups(addParam, true);
		},
		addTempl: function () {
			var templId = $(this).data('id');
			var addParam = 'action=add_templ&add_templ=' + templId;
			KeyGroupList.getKeyGroups(addParam, true);
		},
		addFilterClick: function (e) {
			e.stopPropagation();
			var li = $(this).parent();
			var cg = $('#fcg_' + li.data('id'));
			if (li.is('.active')) {
				li.removeClass('active');
				cg.slideUp();
			}
			else {
				li.addClass('active');
				cg.slideDown();
			}
			var s = '';
			KeyGroupList.addFiltersMenu.find('li.active').each(function() {
				if (s)
					s += '|';
				s += $(this).data('id');
			});
			CMN.ajax('user_save_data', {
				post: 'filters_active=' + s,
				strategy: 2
			}, false);
		},
		toBaseTab: function() {
			var a = $('#tab-base');
			a.click();
		},
		toAddTab: function() {
			var a = $('#tab-add');
			a.click();
		},
		sizeChange: function() {
			KeyGroupList.getKeyGroups('size=' + $(this).val());
		},
		/**
		 * Определение левого верхнего и правого нижнего углов
		 */
		setSelectedArea: function() {
			var sTdCol = KeyGroupList.selectedTd.index();
			var sTdRow = KeyGroupList.selectedTd.parent().index();
			var eTdCol = KeyGroupList.endTd.index();
			var eTdRow = KeyGroupList.endTd.parent().index();

			KeyGroupList.lc = sTdCol;
			KeyGroupList.rc = eTdCol;
			if (eTdCol < sTdCol) {
				KeyGroupList.lc = eTdCol;
				KeyGroupList.rc = sTdCol;
			}
			KeyGroupList.tr = sTdRow;
			KeyGroupList.br = eTdRow;
			if (eTdRow < sTdRow) {
				KeyGroupList.tr = eTdRow;
				KeyGroupList.br = sTdRow;
			}
		},
		/**
		 * Обновляем область выделения над ячейками
		 * @returns {boolean}
		 */
		selectCells: function() {
			KeyGroupList.unselectCells();
			for (var row = KeyGroupList.tr; row <= KeyGroupList.br; row++) {
				var tr = KeyGroupList.tableBody.find('tr:eq(' + row + ')');
				for (var col = KeyGroupList.lc; col <= KeyGroupList.rc; col++) {
					var td = tr.find('td:eq(' + col + ')');
					td.addClass('area');
				}
			}
		},
		/**
		 * Сбрасываем область выделения
		 */
		unselectCells: function() {
			KeyGroupList.table.find('td.area').removeClass('area');
		},
		/**
		 * Обработка начала выделения
		 * @param e
		 * @returns {boolean}
		 */
		mouseDown: function(e) {
			if (KeyGroupList.editMode) {
				var td = $(this);

				if (!e.shiftKey) {
					KeyGroupList.table.find('td.area').removeClass('area');

					if (KeyGroupList.selectedTd) {
						if (KeyGroupList.selectedTd.get(0) == td.get(0))
							return false;
						KeyGroupList.selectedTd.removeClass('selected');
					}
					KeyGroupList.selectedTd = td;
					KeyGroupList.selectedTd.addClass('selected');
				}
				else {
					if (!KeyGroupList.selectedTd) {
						KeyGroupList.selectedTd = td;
						KeyGroupList.selectedTd.addClass('selected');
					}
				}
				KeyGroupList.currentTd = e.target;
				KeyGroupList.endTd = td;

				KeyGroupList.selectedRegime = true;
				KeyGroupList.setSelectedArea();
				if (e.shiftKey)
					KeyGroupList.selectCells();
			}
		},
		/**
		 * Обработка перемещения выделения
		 * @param e
		 * @returns {boolean}
		 */
		mouseMove: function(e) {
			if (KeyGroupList.editMode) {
				e.stopPropagation();
				var target = e.target;
				var td = $(target);
				if (!td.is('td'))
					return false;

				if (target.getAttribute('unselectable') == 'on')
					target.ownerDocument.defaultView.getSelection().removeAllRanges();

				if (!KeyGroupList.selectedRegime)
					return false;

				if (KeyGroupList.currentTd == target)
					return false;

				KeyGroupList.currentTd = target;
				KeyGroupList.endTd = td;

				KeyGroupList.setSelectedArea();
				KeyGroupList.selectCells();
			}
			return false;
		},
		/**
		 * Конец выделения
		 * @param e
		 * @returns {boolean}
		 */
		mouseUp: function(e) {
			if (KeyGroupList.editMode) {
				e.stopPropagation();
				KeyGroupList.selectedRegime = false;
			}
			return false;
		},
		dblClick: function() {
			if (KeyGroupList.editMode) {
				KeyGroupList.startEditTd($(this), true);
			}
		},
		startEditTd: function(td, exValue) {
			KeyGroupList.editedTd = td;
			var pos = td.position();
			if (td.data('col') == 'platform') {
				KeyGroupList.platformTd = td;
				KeyGroupList.platformMenu.css({
					top: pos.top - 6,
					left: pos.left - 5
				});
				KeyGroupList.platformMenu.show();
				KeyGroupList.tableKeys = false;
				return false;
			}
			var text = '';
			if (exValue)
				text = td.text();
			KeyGroupList.editInput.val(text).width(td.width());
			KeyGroupList.editDiv.addClass('editting').css({
				top: pos.top,
				left: pos.left
			});
			KeyGroupList.editInput.focus();
		},
		selectAdPlatform: function () {
			if (KeyGroupList.platformTd) {
				var item = $(this);
				var span = KeyGroupList.platformTd.children('span');
				var value = item.data('code');
				var oldValue = span.data('value');
				if (value != oldValue) {
					span.attr('title', KeyGroupList.platformTitle[value]);
					span.attr('class', 'ad-icon ' + value);
					span.data('value', value);
					var tr = KeyGroupList.platformTd.closest('tr');
					var id = tr.data('id');
					if (!id)
						id = '~' + tr.index();
					var kgid = tr.data('kgid');
					var column = KeyGroupList.platformTd.data('col');
					var col = KeyGroupList.platformTd.index();
					var row = KeyGroupList.platformTd.parent().index();
					var historyItem = [[col, row, oldValue]];
					KeyGroupList.history.push(historyItem);
					KeyGroupList.adSaveCols('data[' + kgid + '][' + id + '][' + column + ']=' + value);
					KeyGroupList.platformTd = false;
				}
			}
			KeyGroupList.platformMenu.hide();
			KeyGroupList.tableKeys = true;
			return false;
		},
		bodyMouseDown: function(e) {
			KeyGroupList.tableKeys = $(e.target).closest('#keygroup-table').length ? true : false;
			if (!$(e.target).closest('#platform-menu').length)
				KeyGroupList.platformMenu.hide();
		},
		keyDown: function(e) {
			if (KeyGroupList.editMode && KeyGroupList.selectedTd &&
				KeyGroupList.tableKeys && e.target.tagName != 'INPUT') {
				var ctrlKey = e.ctrlKey || e.metaKey;
				var tr = false;
				var nextTr = false;
				var nextTd = false;
				if (e.key == 'ArrowDown') {
					tr = KeyGroupList.endTd.closest('tr');
					nextTr = tr.next();
					if (nextTr.length)
						nextTd = nextTr.children('td:eq(' + KeyGroupList.endTd.index() + ')');
				}
				else if (e.key == 'ArrowUp') {
					tr = KeyGroupList.endTd.closest('tr');
					nextTr = tr.prev();
					if (nextTr.length)
						nextTd = nextTr.children('td:eq(' + KeyGroupList.endTd.index() + ')');
				}
				else if (e.key == 'ArrowLeft') {
					nextTd = KeyGroupList.endTd.prev();
					if (nextTd.length && !nextTd.is('.e-td'))
						nextTd = false;
				}
				else if (e.key == 'ArrowRight') {
					nextTd = KeyGroupList.endTd.next();
					if (nextTd.length && !nextTd.is('.e-td'))
						nextTd = false;
				}
				else if (e.key == 'Tab') {
					nextTd = KeyGroupList.endTd.next();
					if (nextTd.length && !nextTd.is('.e-td'))
						nextTd = false;
					if (!nextTd || !nextTd.length) {
						tr = KeyGroupList.endTd.closest('tr');
						nextTr = tr.next();
						if (nextTr.length)
							nextTd = nextTr.children('td.e-td:first');
					}
				}
				else if (e.key == 'Delete' || e.key == 'Backspace') {
					KeyGroupList.deleteCells();
					return false;
				}
				else if (e.key == 'Home') {
					tr = KeyGroupList.endTd.closest('tr');
					nextTd = tr.children('td.e-td:first');
					if (KeyGroupList.endTd.index() == nextTd.index())
						return false;
				}
				else if (e.key == 'End') {
					tr = KeyGroupList.endTd.closest('tr');
					nextTd = tr.children('td.e-td:last');
					if (KeyGroupList.endTd.index() == nextTd.index())
						return false;
				}
				else if (e.which == 67 && ctrlKey && !e.shiftKey && !e.altKey) {
					KeyGroupList.copyCells();
				}
				else if (e.which == 86 && ctrlKey && !e.shiftKey && !e.altKey) {
					KeyGroupList.pasteCells();
				}
				else if (e.which == 88 && ctrlKey && !e.shiftKey && !e.altKey) {
					KeyGroupList.cutCells();
				}
				else if (e.which == 90 && ctrlKey && !e.shiftKey && !e.altKey) {
					KeyGroupList.undo();
				}
				else {
					if (!ctrlKey && !e.altKey) {
						if (e.key.length == 1 || e.key == 'Enter') {
							KeyGroupList.startEditTd(KeyGroupList.selectedTd, e.key == 'Enter');
							if (e.key == 'Enter')
								return false;
							if (KeyGroupList.selectedTd.data('col') == 'platform')
								return false;
						}
					}
				}

				if (nextTd && nextTd.length) {
					if (e.shiftKey) {
						KeyGroupList.endTd = nextTd;
						KeyGroupList.setSelectedArea();
						KeyGroupList.selectCells();
					}
					else {
						KeyGroupList.unselectCells();
						if (KeyGroupList.selectedTd)
							KeyGroupList.selectedTd.removeClass('selected');
						KeyGroupList.selectedTd = nextTd;
						KeyGroupList.selectedTd.addClass('selected');
						KeyGroupList.endTd = nextTd;
						KeyGroupList.setSelectedArea();
					}

					return false;
				}
			}
		},
		editWordEnd: function() {
			var oldValue = KeyGroupList.editedTd.text();
			var value = KeyGroupList.editInput.val();
			KeyGroupList.editDiv.removeClass('editting');
			if (oldValue != value) {
				KeyGroupList.editedTd.text(value);
				var tr = KeyGroupList.editedTd.closest('tr');
				var id = tr.data('id');
				if (!id)
					id = '~' + tr.index();
				var kgid = tr.data('kgid');
				var column = KeyGroupList.editedTd.data('col');
				var col = KeyGroupList.editedTd.index();
				var row = KeyGroupList.editedTd.parent().index();
				var historyItem = [[col, row, oldValue]];
				KeyGroupList.history.push(historyItem);
				KeyGroupList.adSaveCols('data[' + kgid + '][' + id + '][' + column + ']=' + value);
			}
		},
		editKey: function(e) {
			if (KeyGroupList.editMode) {
				var td = KeyGroupList.editedTd;
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
					if (nextTd.length && !nextTd.is('.e-td'))
						nextTd = false;
					if (!nextTd || !nextTd.length) {
						tr = td.closest('tr');
						nextTr = tr.next();
						if (nextTr.length)
							nextTd = nextTr.children('td.e-td:first');
					}
				}

				if (nextTd && nextTd.length) {
					KeyGroupList.editWordEnd();

					if (KeyGroupList.selectedTd)
						KeyGroupList.selectedTd.removeClass('selected');
					KeyGroupList.selectedTd = nextTd;
					KeyGroupList.selectedTd.addClass('selected');
					KeyGroupList.endTd = nextTd;
					KeyGroupList.setSelectedArea();

					e.stopPropagation();
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
			var post = '';
			var x = 0;
			for (var row = KeyGroupList.tr; row <= KeyGroupList.br; row++) {
				var tr = KeyGroupList.tableBody.find('tr:eq(' + row + ')');
				var id = tr.data('id');
				if (!id)
					id = '~' + tr.index();
				var kgid = tr.data('kgid');
				for (var col = KeyGroupList.lc; col <= KeyGroupList.rc; col++) {
					var td = tr.find('td:eq(' + col + ')');
					var column = td.data('col');
					if (column != 'platform') {
						var oldText = td.text();
						if (oldText != '') {
							td.text('');
							historyItem.push([col, row, oldText]);
							if (post)
								post += '&';
							post += 'data[' + kgid + '][' + id + '][' + column + ']=';
							x++;
						}
					}
				}
			}

			if (historyItem.length) {
				KeyGroupList.history.push(historyItem);
				KeyGroupList.adSaveCols(post);
			}
		},
		/**
		 * Копирование выделенных ячеек
		 * @returns {boolean}
		 */
		copyCells: function() {
			var text = '';
			for (var row = KeyGroupList.tr; row <= KeyGroupList.br; row++) {
				var tr = KeyGroupList.tableBody.find('tr:eq(' + row + ')');
				for (var col = KeyGroupList.lc; col <= KeyGroupList.rc; col++) {
					var td = tr.find('td:eq(' + col + ')');
					var sep = col == KeyGroupList.rc ? (row == KeyGroupList.br ? '' : '\n') : '\t';
					var column = td.data('col');
					var oldText = td.text().trim();
					if (column == 'platform')
						oldText = td.children('span').data('value');
					text += oldText + sep;
				}
			}
			KeyGroupList.copyTextarea.val(text).focus();
			KeyGroupList.copyTextarea.get(0).select();
		},
		/**
		 * Вырезать (копирование + очистка ячеек)
		 */
		cutCells: function() {
			var text = '';
			var historyItem = [];
			var post = '';
			for (var row = KeyGroupList.tr; row <= KeyGroupList.br; row++) {
				var tr = KeyGroupList.tableBody.find('tr:eq(' + row + ')');
				var id = tr.data('id');
				if (!id)
					id = '~' + tr.index();
				var kgid = tr.data('kgid');
				for (var col = KeyGroupList.lc; col <= KeyGroupList.rc; col++) {
					var td = tr.find('td:eq(' + col + ')');
					var sep = col == KeyGroupList.rc ? (row == KeyGroupList.br ? '' : '\n') : '\t';
					var column = td.data('col');
					var oldText = '';
					if (column == 'platform')
						oldText = td.children('span').data('value');
					else {
						oldText = td.text().trim();
						if (oldText != '') {
							td.text('');
							historyItem.push([col, row, oldText]);
							if (post)
								post += '&';
							post += 'data[' + kgid + '][' + id + '][' + column + ']=';
						}
					}
					text += oldText + sep;
				}
			}
			KeyGroupList.copyTextarea.val(text).focus();
			KeyGroupList.copyTextarea.get(0).select();
			if (historyItem.length) {
				KeyGroupList.history.push(historyItem);
				KeyGroupList.adSaveCols(post);
			}
		},
		/**
		 * Вставка
		 */
		pasteCells: function() {
			KeyGroupList.copyTextarea.val('').focus();
			KeyGroupList.pasteRegime = true;
		},
		paste: function() {
			if (KeyGroupList.pasteRegime) {
				var historyItem = [];
				var post = '';
				var colRange = KeyGroupList.rc - KeyGroupList.lc;
				var rowRange = KeyGroupList.br - KeyGroupList.tr;
				var inRange = colRange > 0 || rowRange > 0;

				var text = KeyGroupList.copyTextarea.val();
				var rows = text.split('\n');
				var rowsCount = rows.length;

				for (var i = 0; i < rowsCount; i++) {
					if (inRange && i > rowRange)
						break;

					var row = KeyGroupList.tr + i;
					var tr = KeyGroupList.tableBody.find('tr:eq(' + row + ')');
					if (!tr.length)
						break;

					var id = tr.data('id');
					if (!id)
						id = '~' + tr.index();
					var kgid = tr.data('kgid');

					var cols = rows[i].split('\t');
					var colsCount = cols.length;

					for (var j = 0; j < colsCount; j++) {
						if (inRange && j > colRange)
							break;

						var col = KeyGroupList.lc + j;
						var td = tr.find('td:eq(' + col + ')');
						if (!td.length || !td.is('.e-td'))
							break;

						var value = cols[j];
						var oldValue = '';
						var change = false;
						var column = td.data('col');
						if (column == 'platform') {
							var span = td.children('span');
							oldValue = span.data('value');
							if (oldValue != value && (value in KeyGroupList.platformTitle)) {
								span.attr('title', KeyGroupList.platformTitle[value]);
								span.attr('class', 'ad-icon ' + value);
								span.data('value', value);
								change = true;
							}
						}
						else {
							oldValue = td.text();
							if (oldValue != value) {
								td.text(value);
								change = true;
							}
						}

						if (change) {
							historyItem.push([col, row, oldValue]);
							if (post)
								post += '&';
							post += 'data[' + kgid + '][' + id + '][' + column + ']=' + value;
						}
					}
				}
				KeyGroupList.pasteRegime = false;

				if (historyItem.length) {
					KeyGroupList.history.push(historyItem);
					KeyGroupList.adSaveCols(post);
				}
			}
		},
		/**
		 * Отмена изменений
		 */
		undo: function() {
			if (!KeyGroupList.history.length)
				return;
			var historyItem = KeyGroupList.history.pop();
			if (!historyItem)
				return;
			var l = historyItem.length;
			var post = '';
			for (var i = 0; i < l; i++) {
				var col = historyItem[i][0];
				var row = historyItem[i][1];
				var value = historyItem[i][2];
				var tr = KeyGroupList.tableBody.find('tr:eq(' + row + ')');
				var id = tr.data('id');
				if (!id)
					id = '~' + tr.index();
				var kgid = tr.data('kgid');
				var td = tr.find('td:eq(' + col + ')');
				var column = td.data('col');
				if (td.length) {
					if (column == 'platform') {
						var span = td.children('span');
						span.attr('title', KeyGroupList.platformTitle[value]);
						span.attr('class', 'ad-icon ' + value);
						span.data('value', value);
					}
					else
						td.text(text);
					if (post)
						post += '&';
					post += 'data[' + kgid + '][' + id + '][' + column + ']=' + value;
				}
			}
			if (post)
				KeyGroupList.adSaveCols(post);
		},
		adSaveCols: function(post) {
			CMN.ajax('ad_save_col', {
				form: KeyGroupList.filtersForm,
				post: post,
				strategy: 2
			}, function(data) {
				if (data.added)
					for (var row in data.added) {
						var id = data.added[row];
						var tr = KeyGroupList.tableBody.find('tr:eq(' + row + ')');
						tr.data('id', id);
						console.log(row, id, tr);
					}
			});
		}
	};
}
