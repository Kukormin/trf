if (siteOptions.keygroupFilters) {
	var KeyGroupList = {
		filtersForm: false,
		btnApply: false,
		pageInput: false,
		resultCont: false,
		multiNav: false,
		selectedCount: 0,
		countSpan: false,
		allCountSpan: false,
		multiAction: false,
		thisPage: false,
		allPage: false,
		togglePage: false,
		selectInputs: false,
		table: false,
		allPageChecked: false,
		filterActive: false,
		init: function () {
			this.filtersForm = $('#keygroup-form');
			this.filtersBlock = $('#filters-block');
			this.viewBlock = $('#view-block');
			this.btnApply = this.filtersForm.find('.btn-primary');
			this.pageInput = this.filtersForm.find('input[name="page"]');
			this.resultCont = $('#keygroup-table');
			this.multiNav = $('#multi-nav');
			this.countSpan = this.multiNav.find('#selected_count');
			this.allCountSpan = this.multiNav.find('#all_count');
			this.multiAction = this.multiNav.find('#multi-action');
			this.thisPage = this.multiNav.find('#this_page');
			this.allPage = this.multiNav.find('#all_page');
			this.togglePage = this.multiNav.find('#toggle_page');
			this.filtersToggle = $('#filters_toogle');
			this.filtersToggleLi = this.filtersToggle.parent();
			this.filterActive = this.filtersToggleLi.hasClass('active');
			this.viewToggle = $('#view_toogle');
			this.viewToggleLi = this.viewToggle.parent();
			this.viewActive = this.viewToggleLi.hasClass('active');
			this.addFiltersMenu = $('#add_filter_menu');

			this.btnApply.click(this.applyFilters);
			this.thisPage.click(this.selectThisPage);
			this.allPage.click(this.selectAllPage);
			this.togglePage.click(this.toggleThisPage);
			this.filtersToggle.click(this.toggleFilters);
			this.viewToggle.click(this.toggleView);
			this.resultCont.on('click', '.pagination a', this.toPage);
			this.resultCont.on('click', '.select_item', this.selectItem);
			this.multiNav.on('click', 'li.add_mark > ul a', this.addMark);
			this.multiNav.on('click', 'li.remove_mark > ul a', this.removeMark);
			this.multiNav.on('click', 'li.remove_all_mark > a', this.removeAllMark);
			this.addFiltersMenu.on('click', 'a', this.addFilterClick);

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
				if (KeyGroupList.allPageChecked)
					ids = 'all';
				else
					KeyGroupList.selectInputs.filter(':checked').each(function () {
						if (ids)
							ids += ',';
						ids += $(this).attr('id');
					});
				addParam += '&ids=' + ids;
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
				KeyGroupList.allCountSpan.text(KeyGroupList.table.data('all'));
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
				KeyGroupList.selectedCount = KeyGroupList.table.data('all');
			else
				KeyGroupList.selectedCount = KeyGroupList.selectInputs.filter(':checked').length;
			KeyGroupList.countSpan.text(KeyGroupList.selectedCount);
			if (KeyGroupList.selectedCount > 0)
				KeyGroupList.multiAction.removeClass('hidden');
			else
				KeyGroupList.multiAction.addClass('hidden');
		},
		selectItem: function () {
			KeyGroupList.allPageChecked = false;
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
		toggleThisPage: function () {
			KeyGroupList.allPageChecked = false;
			var selected = KeyGroupList.selectInputs.filter(':checked');
			var empty = KeyGroupList.selectInputs.not(':checked');
			selected.prop('checked', false);
			empty.prop('checked', true);
			KeyGroupList.calcSelected();
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
		toggleView: function() {
			KeyGroupList.viewActive = !KeyGroupList.viewActive;
			if (KeyGroupList.viewActive) {
				KeyGroupList.viewToggleLi.addClass('active');
				KeyGroupList.viewBlock.slideDown();
			}
			else {
				KeyGroupList.viewToggleLi.removeClass('active');
				KeyGroupList.viewBlock.slideUp();
			}
			CMN.ajax('user_save_data', {
				post: 'view_show=' + (KeyGroupList.viewActive ? 1 : 0),
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
		}
	};
}
