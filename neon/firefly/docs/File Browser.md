# Firefly File Browser

- info on the javascript file browser popup window

## Html Widget

<! neon\dev\widgets\NeonJsFiddle bundles='\neon\core\assets\CoreAsset,\neon\core\themes\neon\Assets,\neon\firefly\assets\BrowserAsset' >
<script>
window.showPicker = function(){
	FIREFLY.picker('replace', (file) => {
		document.getElementById('results').innerHTML = JSON.stringify(file, null, 2)
	})
};
</script>
<html>
	<div>
		<button onclick=showPicker()>
			Launch modal
		</button>
		<h3>Selected image</h3>
		<pre id="results"></pre>
	</div>
</html>
</!>

<! neon\dev\widgets\NeonJsFiddle bundles='\neon\core\assets\CoreAsset,\neon\core\themes\neon\Assets,\neon\firefly\assets\BrowserAsset' >
<script>
neon.mount('#app')
</script>
<html>
	<div id="app">
		<firefly-media-inline name="media" ></firefly-media-inline>
	</div>
</html>
</!>