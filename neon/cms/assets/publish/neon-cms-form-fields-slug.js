Vue.component('neon-cms-form-fields-slug', {
	extends: Vue.component('neon-core-form-fields-base'),
	props: {
		pageId: { type: String, editor: { order: 4, hint: 'Enter the page nice_id', required: true } },
		prefix: { type: String, default: '', editor: { order: 5, hint: 'A url prefix like "/category/{slug}"', required: false } }
	},
	/**
	  * A template string containing HTML for a form field.
	* @type {string} HTML
	*/
	template: /*html*/ `
		<neon-core-form-field class="neonFieldText" v-bind="fieldProps">
			<a v-if="isPrintOnly" :href="neon.url(value)" :target="neon.url(value)">
				{{ base + neon.url(value) }}
			</a>
			<div v-else>
				<input class="form-control" type="text" :name="inputName" :id="id" :placeholder="placeholder" :readonly="isReadOnly" :disabled="disabled" v-model="update"@keyup="validate" @blur="onBlur" @focus="onFocus" @input="update = $event.target.value">
				<div>
					<svg style="width:16px; height:16px; vertical-align: middle; display:inline-block;" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
					<span style="color:#666">{{ base }}</span>
					<strong>{{ modelValue }}</strong>
				</div>
				<div v-if="!isNew && modelValue !== startValue">
					<svg style="width:16px; height:16px; vertical-align: middle; display:inline-block;"
						fill="none" 
						stroke-linecap="round" 
						stroke-linejoin="round" 
						stroke-width="2" 
						stroke="currentColor" 
						viewBox="0 0 24 24">
						<path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
					</svg>
					<span>Changing the url will break external links. To prevent broken links, </span>
					<a href="#">set up a redirect</a>
				</div>
			</div>
		</neon-core-form-field>
	`,
	// render: function (h) {
	// 	var self = this;

	// 	return h('neon-core-form-field', { class: 'neonFieldText', props: this.fieldProps }, [
	// 		// <div v-if="isPrintOnly" v-nl2br="value"></div>
	// 		h('div', {}, [this.isPrintOnly ? h('a', { attrs: { 'href': neon.url(this.value), 'target': neon.url(this.value) } }, this.base + neon.url(this.value)) : [
	// 			h('input', {
	// 				class: 'form-control',
	// 				on: { keyup: this.validate, blur: this.onBlur, focus: this.onFocus, input: function (event) { self.update = event.target.value; } },
	// 				attrs: { type: 'text', name: this.inputName, id: this.id, placeholder: this.placeholder, readOnly: this.isReadOnly, disabled: this.disabled },
	// 				domProps: { value: this.update }
	// 			}),
	// 			h('div', {}, [
	// 				h('svg', { style: 'width:16px;height:16px;vertical-align: middle;display:inline-block;', attrs: { fill: "none", "stroke-linecap": "round", "stroke-linejoin": "round", "stroke-width": "2", stroke: "currentColor", viewBox: "0 0 24 24", } }, [
	// 					h('path', { attrs: { d: "M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" } })
	// 				]),
	// 				h('span', { style: "color:#666" }, [this.base]),
	// 				h('strong', {}, [this.modelValue])
	// 			]),
	// 			(!this.isNew && this.modelValue !== this.startValue)
	// 				?
	// 				h('div', {}, [
	// 					h('svg', { style: "width:16px;height:16px;vertical-align: middle;display:inline-block;", attrs: { fill: "none", "stroke-linecap": "round", "stroke-linejoin": "round", "stroke-width": "2", stroke: "currentColor", viewBox: "0 0 24 24" } }, [
	// 						h('path', { attrs: { d: "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" } })
	// 					]),
	// 					h('span', {}, 'Changing the url will break external links. To prevent broken links, '),
	// 					h('a', { attrs: { href: "#" } }, ['set up a redirect'])
	// 				])
	// 				:
	// 				[]
	// 		]
	// 		])
	// 	])
	// },
	data: function () {
		return {
			isNew: true,
			startValue: ''
		}
	},
	mounted: function () {
		this.isNew = (this.modelValue) ? false : true;
		this.startValue = this.modelValue;
	},
	methods: {
		ensureSlashes(str) {
			if (str.length == 0) return '';
			// Collapse multiple slashes to just one throughout the string
			str = str.replace(/\/+/g, "/");
			// Ensure the string starts with a single "/"
			if (!str.startsWith("/")) {
				str = "/" + str;
			}
			return str;
		}
	},
	computed: {
		getPrefix() {
			return this.ensureSlashes(this.prefix)
		},
		base: function () {
			return neon.url(this.prefix, undefined, true).replace(/\/$/, "");
		},
		update: {
			get: function () {
				return this.modelValue
			},
			set: function (value) {
				value = this.ensureSlashes(value);
				let url = value.replace(/\s+/g, '-').toLowerCase();
				// // Always prepend prefix if it's set and not already there
				if (this.getPrefix) {
					if (url.startsWith(this.getPrefix + '/')) {
						// The prefix is there, do normal processing
						// if it does not have a trialing slash then lets add one.
						this.modelValue = url;
					} else {
						// Try to see if it's a backspace in the prefix
						if (this.getPrefix.startsWith(url)) {
							// User tried to backspace into the prefix, reset it to prefix
							this.modelValue = this.getPrefix + '/';
						} else {
							// Prefix is not there, add it
							this.modelValue = this.ensureSlashes(this.getPrefix + '/' + url);
						}
					}
				} else {
					// No prefix, do normal processing
					this.modelValue = url;
				}
			}
		}
	}
});
