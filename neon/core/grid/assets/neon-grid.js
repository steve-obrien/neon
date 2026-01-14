/**
 * @package  neonGrid
 * @author	Steve O'Brien <steve.obrien@newicon.net>
 * @copyright  Copyright &copy; 2015 Newicon Ltd
 *
 * Grid ajax functions
 */
(function ($) {

	// could use the jquery jui widget functions to make this code cleaner.  But that would require another dependancy on the JUI library

	$.fn.neonGrid = function (method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.neonGrid');
			return false;
		}
	};

	var defaults = {
		filterUrl: '',
		onAjaxAfter: function(){},
		onAjaxBefore: function(){},
		onInitAfter: function(){}
	};

	var methods = {

		init: function (options) {
			return this.each(function () {
				var $grid = $(this);
				$grid.neonGrid('settings', options || {});
				$grid.neonGrid('attachSearchEvents');
				$grid.neonGrid('attachButtonEvents');
				$grid.neonGrid('initAfterEvents');
			});
		},

		initAfterEvents: function() {
			var $grid = $(this);
			// set the method on a timeout to ensure rest of initialisation completed
			setTimeout($grid.data('settings').onInitAfter, 0);
		},

		attachSearchEvents: function() {
			var $grid = this;
			var gridId = '#'+$grid.attr('id');
			//Search function
			neon.form.forms[$grid.attr('id')+'Filter'].onChange(_.debounce(function(){
				$grid.neonGrid('doAjax', ['[data-rows]', '[data-header]', '.pagination', '[data-debug]', '.neonGrid_summary']);
				return false;
			}, 300));

			// scope links
			$grid.on('click', '[data-scope]', function(event){
				event.preventDefault();
				$grid.neonGrid('setScope', $(this).attr('data-scope'));
				return false;
			});
			// sort links
			$grid.on('click', '[data-sort]', function(event){
				event.preventDefault();
				$grid.neonGrid('setSort', $(this).attr('data-sort'));
				return false;
			});

			// check columns should probably be registered by the check column
			$grid.on('click', '[data-check-all]', function(){
				if ($(this).is(':checked')) {
					$grid.find('[data-check-row]').prop('checked', true);
				} else {
					$grid.find('[data-check-row]').prop('checked', false);
				}
			});
			// row actions
			$grid.on('click', '[data-action]', function(event){
				event.preventDefault();
				if ($(event.target).attr('data-confirm')) {
					if (!confirm($(event.target).attr('data-confirm'))){
						return false;
					}
				}
				var id = $grid.attr('id');
				var replace = ['[data-rows]', '.neonGrid_controls', '[data-headers]', '[data-header]', '.pagination', '[data-debug]', '.neonGrid_summary'];
				$grid.neonGrid('doAjax', replace, '&' + id + '[action]=' + $(this).attr('data-action') + '&' + id + '[action_index]=' + $(this).attr('data-index'));
				//$grid.neonGrid('doAjax', 'all',  );
				return false;
			});

			$grid.on('click', '[data-page]', function(event){
				event.preventDefault();
				var id = $grid.attr('id');
				$grid.neonGrid('doAjax', ['[data-rows]', '[data-header]', '.pagination', '[data-debug]', '.neonGrid_controls', '.neonGrid_summary'], '&' + id + '[page]=' + $(this).attr('data-page'));
			});

			$grid.on('click', '[data-wrap]', function () {$(this).closest('.neonGrid').toggleClass('neonGrid_nowrap'); return false;});
			$grid.on('click', '[data-export]', function(event) {
				event.preventDefault();
				var data = $grid.neonGrid('getData');
				window.location = $(this).attr('href') + '&' + $.param(data);
			});
		},

		attachButtonEvents: function() {
			var $grid = this;
			$grid.on('click', '[data-delete-ajax]', function(event) {
				event.preventDefault();
				var href = $(this).attr('href');
				var data = $(this).data();
				var message = ((typeof data['message'] !== 'undefined') ? data['message'] : 'Are you sure you want to delete this item?');
				//if (confirm(message)) {
					$.ajax({
						url:href,
						success: function(response) {
							$grid.neonGrid('doAjax', ['[data-rows]', '.neonGrid_controls', '[data-header]', '.pagination', '[data-debug]', '.neonGrid_summary']);
						},
						error: function() {
							neon.alert('error', 'Something went wrong when deleting the item');
						}
					});
				// }
			});
		},

		/**
		 * Se the scope of the current grid:
		 * useage:
		 *
		 * ```JS
		 * // set the "trashed" scope to be active
		 * $('#myGridId').neonGrid('setScope', 'trashed');
		 * ```
		 * @param string key the key of the scope to activate
		 */
		setScope: function(key) {
			var $grid = this;
			var $scope = $grid.find('[data-scope="' + key + '"]');
			if (!$scope) {
				throw 'No scope exists with key ' + key;
			}
			// set the scope data for the grid
			$grid.data('scope', key);
			// remove active state for all scopes
			$grid.find('[data-scope]').removeClass('active');
			// set the active scope
			$scope.addClass('active');
			// reload the grid, loading the new scope
			$grid.neonGrid('doAjax', ['[data-rows]', '[data-headers]', '[data-header]', '[data-debug]', '.neonGrid_controls', '.panel-footer', '.neonGrid_summary']);
		},

		setSort: function(sort) {
			// set the sort data for the grid
			this.data('sort', sort);
			this.neonGrid('doAjax', ['[data-rows]', '[data-headers]', '[data-header]', '[data-debug]', '.neonGrid_summary']);
		},

		getData: function() {
			"use strict";
			var $grid = this;
			var data = {};
			var id = $grid.attr('id');
			data[id] = {};

			// this is pretty horrible - we should ask the specific filter form js to get us the data not the DOM!
			// search filter data:
			var $searchRow = $grid.find('[data-filters]');

			// get values of input and select boxes
			$searchRow.find('input,select').each(function(i, el) {
				// handle radio inputs - only allow checked radio inputs to be added to the list
				if ($(this).attr('type') === 'radio' && !$(this).is(':checked'))
					return;
				var val = $(this).val();
				if (val !== '' && val !== null)
					data[$(this).attr('name')] = $(this).val();
			});

			// The following code would allow a more programatic approach to form filter data where the form
			// itself is asked to get the current filters applied rather than relying on
			// jquery to scrape it from the dom.
			// Then we strip out null and empty values
			// var d = neon.form.forms[id+'Filter'].getData().filter;
			// Object.keys(d).forEach(function(key) {
			// 	if (d[key] === '' || d[key] === null) {
			// 		delete d[key];
			// 	}
			// });


			// scope data
			if ($grid.data('scope')) {
				data[id]['scope'] = $grid.data('scope');
			}

			// sort data
			if ($grid.data('sort')) {
				data[id]['sort'] = $grid.data('sort');
			}
			return data;
		},

		getRequestData: function() {
			var data = this.neonGrid('getData');
			return $.param(data);
		},

		ajaxBefore: function() {
			var $grid = this;
			$grid.addClass('is_loading');
			$grid.data('settings').onAjaxBefore.call();
		},

		/**
		 * Executes the ajax request to fetch the grid data - then replaces the defined portions in replaceSelector
		 * @param array replaceSelector
		 * @param data
		 */
		doAjax: function(replaceSelector, data) {
			var $grid = this;
			var gridId = $grid.attr('id');
			var data = data || '';
			data = $grid.neonGrid('getRequestData') + data;
			$grid.neonGrid('ajaxBefore');

			var replaceParts = function(subject, replaceParts) {

				if (!replaceParts || replaceParts == 'all') {
					// no replace parts defined so just replace everything
					var replaceHtml = $('#' + gridId, subject).html();
					$grid.html(replaceHtml);
				}

				if ($.type(replaceParts) === "string") {
					replaceParts = [replaceParts];
				}

				for (var i in replaceParts) {
					var part = '#' + gridId + ' ' + replaceParts[i];
					var html = $(part, subject).html();
					$(part).html(html)
				}

			};
			$.ajax({
				url: $grid.data('settings').filterUrl,
				type: 'POST',
				data: data,
				success: function(data){
					var $data = $('<div>' + data + '</div>');
					// strip out everything apart from the html with the gridId
					// giving: <div id="[the-grid-id]">...</div>
					$data = $('#'+$grid.attr('id') , $data).parent();
					replaceParts($data, replaceSelector);
				},
				complete: function(){
					$grid.neonGrid('ajaxAfter');
				}
			});
		},

		reload: function() {
			var $grid = this;
			$grid.neonGrid('doAjax');
		},

		reloadRows: function() {
			var $grid = this;
			$grid.neonGrid('doAjax', ['[data-rows]']);
		},

		ajaxAfter: function() {
			var $grid = $(this);
			$grid.removeClass('is_loading');
			$grid.data('settings').onAjaxAfter.call();
		},

		getSettings: function() {
			var $grid = $(this);
			return $grid.data('settings');
		},

		/**
		 * Can set bulk settings via an object - settings defined will be merged with existing settings
		 * settings defined will be overridden
		 *
		 * ```js
		 * $('#myGridId').neonGrid('settings', {
		 *     onAjaxAfter: function() { console.log('after update') },
		 *     onAjaxBefore: function(){ console.log('before update') },
		 *     onInitAfter: function() { console.log('after initialisation') }
		 * })
		 * ```
		 *
		 * @param settings
		 */
		settings: function(settings) {
			var grid = $(this);
			return grid.data('settings', $.extend({}, defaults, grid.data('settings'), settings));
		},

		/**
		 * Set the function to be called after update
		 * ```js
		 * $('#myGridId').neonGrid('onAjaxAfter', function() { console.log('after ajax'); });
		 * ```
		 * @param callable
		 */
		onAjaxAfter: function(callable){
			$(this).neonGrid('settings', {onAjaxAfter: callable});
		},

		/**
		 * Set the function to be called before update
		 * ```js
		 * $('#myGridId').neonGrid('onAjaxBefore', function() { console.log('before ajax'); });
		 * ```
		 * @param callable
		 */
		onAjaxBefore: function(callable){
			$(this).neonGrid('settings', {onAjaxBefore: callable});
		},

		/**
		 * Set the function to be called after initialisation
		 * ```js
		 * $('#myGridId').neonGrid('onInitAfter', function() { console.log('after initialisation'); });
		 * ```
		 * @param callable
		 */
		onInitAfter: function(callable){
			$(this).neonGrid('settings', {onInitAfter: callable});
		}

	};

})(jQuery);
