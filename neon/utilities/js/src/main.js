import Vue from 'vue'
import App from './App.vue'

Vue.config.productionTip = false

window.utilities = function(props) {
  new Vue({
    render: h => h(App, {props:props}),
  }).$mount('#app')
}

