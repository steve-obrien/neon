<template>
	<neon-core-form-field v-bind="fieldProps" class="neonFieldTextarea">
		I am an editor
		<div v-if="isPrintOnly" v-nl2br="value"></div>
		<div id="editorjs"></div>
		<button @click.prevent="doSave()">Save</button>
		<pre>{{saveData}}</pre>
	</neon-core-form-field>
</template>

<script>

import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';

class SimpleImage {

	static get toolbox() {
		return {
			title: 'Image',
			icon: '<svg width="17" height="15" viewBox="0 0 336 276" xmlns="http://www.w3.org/2000/svg"><path d="M291 150V79c0-19-15-34-34-34H79c-19 0-34 15-34 34v42l67-44 81 72 56-29 42 30zm0 52l-43-30-56 30-81-67-66 39v23c0 19 15 34 34 34h178c17 0 31-13 34-29zM79 0h178c44 0 79 35 79 79v118c0 44-35 79-79 79H79c-44 0-79-35-79-79V79C0 35 35 0 79 0z"/></svg>'
		};
	}

	render() {
		// Create the div element
		const element = document.createElement('div');

		// Create a text node
		// const imgNode = document.createElement('img')

		// Append the text node to the div
		// element.appendChild(imgNode);


		// show picker:
		FIREFLY.picker('editor', function (item) {
			var alt = _.get(item, 'file.alt', '');
			var mime = _.get(item, 'file.mime_type', '');
			var base = FIREFLY.getImageUrl(item.id);

			if (mime.substring(0, 5) === 'video') {
				let html = `<video loading="lazy" controls src="${base}" alt="${alt}" type="${mime}" />`;
				let video = document.createElement('video');
				// Set attributes more succinctly
				video.sizes = '100vw';
				video.loading = 'lazy';
				video.controls = true;
				video.type = mime;
				video.src = base;
				video.alt = alt;
				video.srcset = srcset;
				element.appendChild(video)
			}

			else {
				// assume image
				var src = base + '&w=200';
				var sizes = [200, 640, 1280, 2000, 2600];
				var srcset = '';
				_.each(sizes, function (size) { srcset += base + '&w=' + size + '&q=90 ' + size + 'w, '; });
				let html = '<img sizes="100vw" loading="lazy" src="' + src + '" ' + alt + ' srcset="' + srcset + '"   />';
				let img = document.createElement('img');
				// Set attributes more succinctly
				img.sizes = '100vw';
				img.loading = 'lazy';
				img.src = src;
				img.alt = alt;
				img.srcset = srcset;
				element.appendChild(img)
			}
		});
		
		return element
	}

	save(blockContent) {
		return {
			url: blockContent.value
		}
	}
}

export default {
	name: 'neon-core-form-fields-editor',
	extends: Vue.component('neon-core-form-fields-base'),
	data() {
		return {
			saveData: {},
			editor: null
		}
	},
	mounted() {
		this.editor = new EditorJS({
			holder: 'editorjs',
			data: this.value,
			tools: {
				image: SimpleImage,
				/**
				 * Each Tool is a Plugin. Pass them via 'class' option with necessary settings {@link docs/tools.md}
				 */
				header: {
					class: Header,
					inlineToolbar: ['marker', 'link'],
					config: {
						placeholder: 'Header...',
						levels: [2, 3, 4, 5],
						defaultLevel: 3
					},
				}
			}
		});
	},
	methods: {
		doSave() {
			this.editor.save().then((outputData) => {
				console.log('Article data: ', outputData)
				this.saveData = outputData
			}).catch((error) => {
				console.log('Saving failed: ', error)
			});
		}
	}
}
</script>