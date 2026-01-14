<template>
	<input type="text" @blur="trim" @input="update = $event.target.value" :value="update">
</template>

<script>
	export default {
		name: "Slug",
		props: {
			value: {type:String, default:''}
		},
		mounted: function() {
			this.isNew = (this.modelValue) ? false : true;
			this.startValue = this.modelValue;
		},
		methods: {
			trim: function() {
				if (this.value.endsWith('/')) {
					this.update = this.value.slice(0, -1);
				}
			}
		},
		computed: {
			update: {
				get: function() {
					return this.value;
				},
				set: function(value) {
					if (value[0] !== '/') value = '/'+value;
					this.$emit('input', value.replace(/\s+/g, '-').toLowerCase());
				}
			}
		}
	}
</script>

<style scoped>

</style>
