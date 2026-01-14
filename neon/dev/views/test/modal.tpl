<div id="app">
	<button @click="onClick">Launch modal</button>
	<neon-modal name="modal2">Hello!</neon-modal>
</div>

{js}{literal}
<script>
	window.showModal1 = function () {
		neon.modal.show(
			{
				data: function() {
					return {lines:[]}
				},
				template: `
					<div>
						<h1>Hello! my things</h1>
						<button @click="modal2()">Modal 2</button>
						<button @click="addLine()">Add Line</button>
						<p v-for="line in lines">New line</p>
					</div>
				`,
				methods: {
					modal2: function () {
						window.showModal2()
					},
					addLine: function () {
						this.lines.push('line')
					}
				},
			}, // dymaic vue component to draw modal
			{}, //props passed to the component defined in arg #1
			{ // modal options
				draggable: true,
				buttons: [
					{title: 'Close'},
					{
						title: 'Hello', handler: function () {
							alert('hello')
						}
					}
				]
			}
		);
	};

	window.showModal2 = function () {
		neon.modal.show('modal2')
	}


	var v = new Vue({
		methods: {
			onClick: function () {
				showModal1();
			}
		}
	})
	v.$mount('#app');
</script>
{/literal}{/js}

