if (siteOptions.keygroupFilters) {
	var KeyGroupList = {
		selectedCount: 0,
		selectInputs: false,
		table: false,
		allPageChecked: false,
		filterActive: false,
		init: function () {
			this.filtersForm = $('#keygroup-form');
			this.filtersBlock = $('#filters-block');
			this.btnApply = this.filtersForm.find('.btn-primary');
			this.pageInput = this.filtersForm.find('input[name="page"]');
			this.resultCont = $('#keygroup-table');
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
			this.views = $('.views');

			this.filtersForm.on('submit', this.applyFilters);
			this.btnApply.click(this.applyFilters);
			this.thisPage.click(this.selectThisPage);
			this.allPage.click(this.selectAllPage);
			this.toggleSelect.click(this.toggleSelection);
			this.cancelSelect.click(this.cancelSelection);
			this.filtersToggle.click(this.toggleFilters);
			this.resultCont.on('click', '.pagination a', this.toPage);
			this.resultCont.on('click', '.select_item', this.selectItem);
			this.multiNav.on('click', 'li.add_mark > ul a', this.addMark);
			this.multiNav.on('click', 'li.remove_mark > ul a', this.removeMark);
			this.multiNav.on('click', 'li.remove_all_mark > a', this.removeAllMark);
			this.multiNav.on('click', 'li.add_templ > ul a', this.addTempl);
			this.ygsn.on('click', 'div', this.ygsnClick);
			this.views.on('click', '.view_change', this.viewChange);
			this.addFiltersMenu.on('click', 'a', this.addFilterClick);
			$('#to-add-tab').click(this.toAddTab);
			$('#to-base-tab').click(this.toBaseTab);
			this.resultCont.on('change', '#size', this.sizeChange);

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
		viewChange: function() {
			if ($(this).hasClass('active'))
				return false;

			$('.view_change.active').removeClass('active');
			$(this).addClass('active');

			var id = $(this).data('id');
			KeyGroupList.getKeyGroups('view=' + id);
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
		selectNavItems: function() {
			var y = KeyGroupList.filtersForm.find('input[name=y]').val() == 1;
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
				gn.removeClass('selected');
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
		}
	};
}
