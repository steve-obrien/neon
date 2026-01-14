/**
 * Copyright (c) 2014-2016, CKSource - Frederico Knabben. All rights reserved.
 * Licensed under the terms of the MIT License (see LICENSE.md).
 *
 * Basic sample plugin inserting current date and time into the CKEditor editing area.
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_intro
 */

// Register the plugin within the editor.
CKEDITOR.plugins.add( 'fireflyimage', {

	// Register the icons. They must match command names.
	icons: 'fireflyimage',

	// The plugin initialization logic goes inside this method.
	init: function( editor ) {

		// Define the editor command that inserts an image.
		editor.addCommand( 'showMediaBrowser', {
			// Define the function that will be fired when the command is executed.
			exec: function( editor ) {
				FIREFLY.picker('ckedit', function(item){
					var alt = _.get(item, 'file.alt', '');
					var mime = _.get(item, 'file.mime_type', '');
					console.log(mime, 'mime')
					var base = FIREFLY.getImageUrl(item.id);

					if (mime.substring(0, 5) === 'video') {
						editor.insertHtml(`<video loading="lazy" controls src="${base}" alt="${alt}" type="${mime}" />`);
					}
					
					else {
						// assume image
						var src = base + '&w=200';
						var sizes = [200,640,1280,2000,2600];
						var srcset = '';
						_.each(sizes, function(size) {srcset += base+'&w='+size+'&q=90 '+size+'w, ';});
						editor.insertHtml('<img sizes="100vw" loading="lazy" src="' + src + '" '+alt+' srcset="' + srcset + '"   />');
					}
				});
			}
		});

		// Create the toolbar button that executes the above command.
		editor.ui.addButton( 'Fireflyimage', {
			label: 'Insert Image',
			command: 'showMediaBrowser',
			toolbar: 'insert'
		});

		// Listen for key press events
		editor.on('key', function(evt) {
			// Check if the backspace key was pressed
			if (evt.data.keyCode === CKEDITOR.CTRL + 8 || evt.data.keyCode === CKEDITOR.SHIFT + 8 || evt.data.keyCode === CKEDITOR.CTRL + 46 || evt.data.keyCode === CKEDITOR.SHIFT + 46 || evt.data.keyCode === 8 || evt.data.keyCode === 46) {
				var selection = editor.getSelection();
				var element = selection.getStartElement();

				// Check if the selection start element is a video
				if (element && element.is('video')) {

					// Prevent the default backspace action
					evt.cancel();

					// Remove the video element
					element.remove();
				}
			}
		});
	}
});
