
Vue components that can be used on a builder.
They need a class or component key with the name of their component.
The property editor will look up the component properties and build a suitable form.

```js
// display a simple banner with a background image
// title and body text
Vue.component('neon-banner', {
	props: {
		id: {type: String, editor: false},

		onClick: {type: Function, editor: false},

		image: { type: String, editor: { component: 'neon-core-form-fields-image' }},

		title: {type: String},

		body: {type: String}
	}
});
```

Notice the editor property.