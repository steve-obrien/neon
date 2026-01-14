{css}
	<style>
		.neonFiddle { position:relative;}
		.neonFiddle_button { position:absolute;top:0;right:0;  }
		pre { border:0; }
		[v-cloak] { display: none; }
	</style>
{/css}

<div id="jsFiddle-{$fileId}">
	<div class="neonFiddle">
		<el-tabs v-model="activeName">
			<el-tab-pane label="JavaScript" name="javascript">
				<neon-input-ide class="neonFiddle_ide" v-model="script" lang="javascript" :options="{ tabSize:4,highlightActiveLine:false,showGutter:false,showPrintMargin:false,printMargin:false }"></neon-input-ide>
			</el-tab-pane>
			<el-tab-pane label="CSS" name="css">
				<neon-input-ide class="neonFiddle_ide" v-model="style" lang="css" :options="{ tabSize:4,highlightActiveLine:false,showGutter:false,showPrintMargin:false,printMargin:false }"></neon-input-ide>
			</el-tab-pane>
			<el-tab-pane label="HTML" name="html">
				<neon-input-ide class="neonFiddle_ide" v-model="html" lang="html" :options="{ tabSize:4,highlightActiveLine:false,showGutter:false,showPrintMargin:false,printMargin:false }"></neon-input-ide>
			</el-tab-pane>
			<el-tab-pane label="Dependencies" name="dependencies">
				<neon-core-form-form :fields="depFormFields" v-model="deps" id="dep{$fileId}" name="dependencies"></neon-core-form-form>
			</el-tab-pane>
			<el-tab-pane label="Result" name="result">
				<iframe height="600px" width="100%" frameBorder="0" :src="resultUrl + '?fileId=' + fileId"></iframe>
			</el-tab-pane>
		</el-tabs>
		<button class="neonFiddle_button btn btn-primary" v-cloak v-show="canRun" class="btn btn-primary" @click="run()">Run</button>
	</div>
</div>

{js}

	<script>
		(function() {

			var escapedScript = `{$script}`.replace(/>>>BACK_TICK<<</g, '`');
			var escapedStyle = `{$style}`.replace(/>>>BACK_TICK<<</g, '`');
			var escapedHtml = `{$html}`.replace(/>>>BACK_TICK<<</g, '`');
			var fileId = '{$fileId}';
			var bundles = {$bundles};
			var urls = {$urls};
			var activeTab = '{$activeTab}';

			console.log({$bundles});

			new Vue({
				el: '#jsFiddle-{$fileId}',
				data: {
					activeName: activeTab,
					canRun: true,
					script: escapedScript,
					style: escapedStyle,
					html: escapedHtml,
					resultUrl: neon.url('/dev/index/terminal-js-result'),
					fileId: fileId,
					deps: { },
					depFormFields: {
						bundles: {
							class: 'neon-core-form-fields-selectmultiple',
							name: 'bundles',
							items: {
								'\\neon\\core\\assets\\CoreAsset': '\\neon\\core\\assets\\CoreAsset',
								'\\neon\\core\\form\\assets\\FormAsset': '\\neon\\core\\form\\assets\\FormAsset',
								'\\neon\\core\\themes\\neon\\Assets': '\\neon\\core\\themes\\neon\\Assets'
							},
							placeholder:"Select dependent asset bundles",
							hint: 'You can specify your own paths to Asset bundle classes.  For e.g. <code>\\neon\\cms\\assets\\CmsEditorAsset</code>',
							placeholderLabel: true,
							value: bundles,
							create: true,
						},
						urls: {
							class: 'neon-core-form-fields-selectmultiple',
							id: 'urls',
							name: 'urls',
							placeholder: 'Dependent Url: e.g. http://cdn.com/jquery.js',
							placeholderLabel: true,
							create: true,
							value: urls
						}
					}
				},
				delimiters: ["[[", "]]"],
				methods: {
					getBundles: function() {
						return ['\\neon\\core\\assets\\CoreAsset'];
					},
					run: function () {
						var vm = this;
						this.deps = neon.form.forms.dep{$fileId}.getData();
						$.post(neon.url('/dev/index/terminal-js-run'), {
							style: this.style,
							script: this.script,
							html: this.html,
							bundles: this.deps.bundles,
							urls: this.deps.urls
						})
							.done(function (response) {
								// updated new file with js,css,html data
								// created new sha1 hash
								// update iframe with new hash
								vm.fileId = response.fileId;
							})
							.fail(function (response) {
								vm.output = response.responseText;
							});
					}
				}
			});
		})();
	</script>
{/js}