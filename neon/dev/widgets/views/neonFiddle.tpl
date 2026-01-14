{css}
	<style>
		.neonFiddle { display: grid; grid-template-columns: auto 100px; grid-column-gap: 1px; }
		.neonFiddle_ide {  }
		pre { border:0; }
		pre.sf-dump { margin-top:0; padding-top:0;margin-bottom: 0;border:0; }
		[v-cloak] { display: none; }
	</style>
{/css}

<div id="{$formId}vue">
	<div class="neonFiddle">
		<neon-input-ide class="neonFiddle_ide" v-model="input" lang="php" :options="{ tabSize:4,highlightActiveLine:false,showGutter:false,showPrintMargin:false,printMargin:false }"></neon-input-ide>
		<button v-cloak v-show="canRun" class="btn btn-primary" @click="run()">Run</button>
	</div>
	<div style="display: grid; grid-template-columns: min-content auto; margin-bottom: 20px;">
		<pre class="sf-dump" style="margin:0;white-space:nowrap" title="Gives the result">=></pre>
		<div v-html="output"></div>
	</div>
	<select @change="selectFiddle($event.target.value)" class="form-control">{foreach $files as $file}<option >{$file}</option>{/foreach}</select>
</div>

{js}
<script>
	new Vue({
		el: '#{$formId}vue',
		data: { canRun: {$canRun}, input: {$content nofilter}, output: {$result nofilter} },
		delimiters: ["[[","]]"],
		mounted() {
			$(window).on('keypress', event => {
				if (event.which == 13 && event.shiftKey) {
					this.run(false);
					return false;
				}
			});
		},
		methods: {
			run: function(save = true) {
				var vm = this;
				$.post(neon.url('/dev/index/terminal-run'), { input: this.input, save: save })
					.done(function (response) {
						vm.output = response;
					})
					.fail(function(response){
						vm.output = response.responseText;
					});
			},
			selectFiddle: function(val) {
				// get code from file
				let vm = this;
				$.post(neon.url('/dev/index/terminal-php-get'), { file: val }).done(function (response) {
					vm.input = response;
				});
			}
		}
	});
</script>
{/js}