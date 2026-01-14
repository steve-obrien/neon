import Cobe from './Cobe.vue';
import Editor from './Editor.vue';
import NeonAdder from './NeonAdder.vue';

// Auto-install when vue is found (eg. in browser via <script> tag)
let GlobalVue = null;
if (typeof window !== 'undefined') {
	GlobalVue = window.Vue;
} else if (typeof global !== 'undefined') {
	GlobalVue = global.Vue;
}
if (GlobalVue) {
	GlobalVue.component('neon-editor', Editor);
	GlobalVue.component('cobe', Cobe);
	GlobalVue.component('NeonAdder', NeonAdder);
}

