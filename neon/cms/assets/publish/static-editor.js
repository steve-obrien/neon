if (jQuery) {

	/**
	 * Add csrf validation to jQuery requests
	 */
	jQuery.ajaxSetup({
		headers: {
			'X-CSRF-Token': jQuery('meta[name="csrf-token"]').attr('content')
		}
	});
}

if (typeof neon === 'undefined') {
	neon = {};
}
if (typeof neon.cobe === 'undefined') {
	neon.cobe = {};
}

// ---------------
// wysiwyg Editors
// ---------------
neon.cobe.applyWysiwygEditor = function(element, path, config) {
	// stop any links from activating
	$(element).on('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
	});
	console.log(element);
	// set up the editor
	$(element).ckeditor(function(element) {
		var editor = this;
		var key = $(element).attr('data-key');
		var pageId = $(element).attr('data-page-id');

		editor.on('blur', function() {
			if (editor.checkDirty()) {
				var data = {
					content: editor.getData(),
					key: key,
					pageId: pageId
				};
				$.ajax({
					method: "POST",
					url: neon.url(path),
					data: data,
					success: function(response) {
						if (response.success == true) {
							editor.setData(response.saved);
							$(element).addClass('m_success');
							setTimeout(function () {
								$(element).removeClass('m_success');
							}, 1000);
						} else {
							$(element).addClass('m_error');
							setTimeout(function () {
								$(element).removeClass('m_error');
								if (typeof response.errors !== 'undefined') {
									alert('Debug mode - error while saving:'+JSON.stringify(response.errors));
								}
							}, 1000);
						}
					},
					error: function(response) {
						$(element).addClass('m_error');
						setTimeout(function () {
							$(element).removeClass('m_error');
						}, 1000);
					}
				});
			}
		});
	}, config);
};
/**
 * Wysiwyg editor for free text areas that can have paragraphs, images etc added
 */
neon.cobe.applyWysiwygEditor('[data-edit]', '/cms/data/update-content-widget', {
	allowedContent: true,
	toolbar:  [
		{ name: 'paragraph', items: ['Format', 'Bold', 'Italic', 'Underline', '-', 'Superscript', 'Subscript', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock','-', 'NumberedList', 'BulletedList', 'Outdent', 'Indent', 'Blockquote'] },
		{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor', '-', 'EmojiPanel' ] },
		{ name: 'insert', items: [ 'Table', 'SpecialChar' ] },
		{ name: 'image', items: ['Image', 'Fireflyimage'] },
		{ name: 'document', items: ['Maximize', 'Source' ] },
		{ name: 'source', items: [ 'RemoveFormat', 'Sourcedialog' ] },
	],
	extraPlugins: 'fireflyimage,emoji'
});