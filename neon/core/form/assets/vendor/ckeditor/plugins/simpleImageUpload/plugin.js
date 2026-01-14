CKEDITOR.plugins.add( 'simpleImageUpload', {

	icons: 'simpleImageUpload',

	init: function( editor ) {
		var fileDialog = $('<input type="file">');

		fileDialog.on('change', function (e) {
			var uploadUrl = neon.url('firefly/file/upload');
			var file = fileDialog[0].files[0];
			var imageData = new FormData();
			imageData.append('file', file);

			$.ajax({
				url: uploadUrl,
				type: 'POST',
				contentType: false,
				processData: false,
				data: imageData,
				success: function (response) {
					var ele = editor.document.createElement('img');
					ele.setAttribute('src', FIREFLY.getImageUrl(response.result.id));
					editor.insertElement(ele)
				},
				error: function (response) {
					alert('Unable to load image');
				}
			});
		});

		editor.ui.addButton( 'SimpleImageUpload', {
			label: 'Insert Image',
			command: 'openDialog',
			toolbar: 'insert'
		});

		editor.addCommand('openDialog', {
			exec: function(editor) {
				fileDialog.click();
			}
		})
	}
});