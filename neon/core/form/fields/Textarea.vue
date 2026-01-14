<template>
	<neon-core-form-field v-bind="fieldProps" class="neonFieldTextarea">
		<div v-if="isPrintOnly" v-nl2br="value"></div>
		<textarea v-else @focus="onFocus()" @blur="onBlur()"  v-model="modelValue" type="text" class="form-control" :name="inputName" :id="id" :placeholder="placeholder" :readonly="isReadOnly" :disabled="disabled" v-bind="attributes"></textarea>
	</neon-core-form-field>
</template>
		
<script>
export default {
	name: 'neon-core-form-fields-textarea',
	extends: Vue.component('neon-core-form-fields-base')
}
</script>