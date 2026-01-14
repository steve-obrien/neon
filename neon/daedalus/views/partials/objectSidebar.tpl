<div class="workbenchBody_entity-sidebar" style="padding:20px; background:white;">
	<div class="dds-sidebar ">
		<h4>Object Record</h4>
		{if isset($object._uuid)}
			Uuid: {$object._uuid} <br />
			Created: {$object._created} <br />
			Updated: {$object._updated} <br />
			Deleted: {if ($object._deleted)} true {else} false {/if} <br />
		{else}
			Uuid: - <br />
			Created: - <br />
			Updated: - <br />
			Deleted: - <br />
		{/if}
		Data Key: <code id="dds-datakey"></code>
		<script>
			// Throttle function to control the rate of function execution
			function throttle(func, limit) {
				let inThrottle;
				return function() {
					if (!inThrottle) {
						func.apply(this, arguments);
						inThrottle = true;
						setTimeout(() => inThrottle = false, limit);
					}
				};
			}

			// Mouse move event handler
			function handleMouseMove(event) {
				const $element = event.target;
				const closestDataKeyElement = $element.closest('[datakey]');

				if (closestDataKeyElement) {
					const datakey = closestDataKeyElement.getAttribute('datakey');
					const displayDiv = document.getElementById('dds-datakey');
					if (displayDiv) {
						displayDiv.textContent = datakey;
					}
				}
			}

			// Attach throttled event listener to the document
			document.addEventListener('mousemove', throttle(handleMouseMove, 100));
		</script>
	</div>
</div>