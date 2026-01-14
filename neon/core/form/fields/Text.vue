<template>
	<neon-core-form-field v-bind="fieldProps" class="neonFieldText">
		<div :name="inputName" v-if="isPrintOnly">{{value}}</div>
		<input v-else @focus="onFocus()" @blur="onBlur()" v-model="modelValue" type="text" class="form-control" :name="inputName" :id="id" :placeholder="placeholder" :readOnly="isReadOnly" :disabled="disabled" v-bind="attributes" />
	</neon-core-form-field>
</template>

<script>

export default {
	name: 'neon-core-form-fields-text',
	extends: Vue.component('neon-core-form-fields-base')
}
</script>