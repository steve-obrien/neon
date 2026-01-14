/**
 * @license Copyright (c) 2014-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
import ClassicEditor from '@ckeditor/ckeditor5-editor-classic/src/classiceditor.js';
import Autoformat from '@ckeditor/ckeditor5-autoformat/src/autoformat.js';
import AutoLink from '@ckeditor/ckeditor5-link/src/autolink.js';
import BlockQuote from '@ckeditor/ckeditor5-block-quote/src/blockquote.js';
import Bold from '@ckeditor/ckeditor5-basic-styles/src/bold.js';
import CloudServices from '@ckeditor/ckeditor5-cloud-services/src/cloudservices.js';
import Essentials from '@ckeditor/ckeditor5-essentials/src/essentials.js';
import Heading from '@ckeditor/ckeditor5-heading/src/heading.js';
import Image from '@ckeditor/ckeditor5-image/src/image.js';
import ImageCaption from '@ckeditor/ckeditor5-image/src/imagecaption.js';
import ImageStyle from '@ckeditor/ckeditor5-image/src/imagestyle.js';
import ImageToolbar from '@ckeditor/ckeditor5-image/src/imagetoolbar.js';
import ImageUpload from '@ckeditor/ckeditor5-image/src/imageupload.js';
import Indent from '@ckeditor/ckeditor5-indent/src/indent.js';
import Italic from '@ckeditor/ckeditor5-basic-styles/src/italic.js';
import Link from '@ckeditor/ckeditor5-link/src/link.js';
import List from '@ckeditor/ckeditor5-list/src/list.js';
import MediaEmbed from '@ckeditor/ckeditor5-media-embed/src/mediaembed.js';
import Paragraph from '@ckeditor/ckeditor5-paragraph/src/paragraph.js';
import PasteFromOffice from '@ckeditor/ckeditor5-paste-from-office/src/pastefromoffice.js';
import Table from '@ckeditor/ckeditor5-table/src/table.js';
import TableToolbar from '@ckeditor/ckeditor5-table/src/tabletoolbar.js';
import TextTransformation from '@ckeditor/ckeditor5-typing/src/texttransformation.js';

class Editor extends ClassicEditor {}


import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import ButtonView from '@ckeditor/ckeditor5-ui/src/button/buttonview';

class Firefly extends Plugin {
	init() {
		const editor = this.editor;

		editor.ui.componentFactory.add( 'firefly', () => {
			// The button will be an instance of ButtonView.
			const button = new ButtonView();

			button.set( {
				label: 'Insert image Firefly',
				withText: true,
				tooltip: true,
			} );

			//Execute a callback function when the button is clicked
			button.on( 'execute', () => {
				
				FIREFLY.picker('ckedit', (item) => {
					var alt = _.get(item, 'file.meta.alt', '');
					// alt="auto load alt text"
					var base = FIREFLY.getImageUrl(item.id);
					var src = base + '&w=200';
				
					var sizes = [200,400,640,768,800,1024,1280,1536,1700,2000,2400,2592];
					var srcset = '';
					sizes.forEach(function(size) {
						srcset += base + '&w=' + size + '&q=90 ' + size + 'w, ';
					});

					alert('here ' + src);

					// editor.model.change(writer => {
					// 	const imageElement = writer.createElement('image', {
					// 		src: imageUrl
					// 	});
	
					// 	// Insert the image in the current selection location
					// 	editor.model.insertContent(imageElement, editor.model.document.selection);
					// });
					// //Change the model using the model writer
					editor.model.change( writer => {
						const imageElement = writer.createElement('image', { src: src } );

						//Insert the text at the user's current position
						editor.model.insertContent( imageElement );
					} );

					// editor.insertHtml('<img sizes="100vw" loading="lazy" src="' + src + '" '+alt+' srcset="' + srcset + '"   />');
				});

			} );

			return button;
		} );
	}
}



// Plugins to include in the build.
Editor.builtinPlugins = [
	Autoformat,
	AutoLink,
	BlockQuote,
	Bold,
	CloudServices,
	Essentials,
	Heading,
	Image,
	ImageCaption,
	ImageStyle,
	ImageToolbar,
	ImageUpload,
	Indent,
	Italic,
	Link,
	List,
	MediaEmbed,
	Paragraph,
	PasteFromOffice,
	Table,
	TableToolbar,
	TextTransformation,
	Firefly
];

// Editor configuration.
Editor.defaultConfig = {
	toolbar: {
		items: [
			'heading',
			'|',
			'bold',
			'italic',
			'link',
			'bulletedList',
			'numberedList',
			'|',
			'outdent',
			'indent',
			'|',
			'imageUpload',
			'blockQuote',
			'insertTable',
			'mediaEmbed',
			'undo',
			'redo',
			'firefly'
		]
	},
	language: 'en-gb',
	image: {
		toolbar: [
			'imageTextAlternative',
			'toggleImageCaption',
			'imageStyle:inline',
			'imageStyle:block',
			'imageStyle:side'
		]
	},
	table: {
		contentToolbar: [
			'tableColumn',
			'tableRow',
			'mergeTableCells'
		]
	}
};

export default Editor;
