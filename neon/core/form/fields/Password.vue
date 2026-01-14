<template>
	<neon-core-form-field v-if="!isPrintOnly" v-bind="fieldProps" class="neonFieldPassword">
		<div class="has-feedback">
			<span title="Toggle password visibility" @click="showPassword = !showPassword" class="form-control-feedback" style="pointer-events:all; cursor:pointer; top: 50%; transform: translateY(-50%);"><i class="fa" :class="showPassword ? 'fa-eye' : 'fa-eye-slash'"></i></span>
			<input @blur="onBlur()" @focus="onFocus()" v-model="modelValue" :type="showPassword ? 'text' : 'password'" class="form-control" :name="inputName" :id="id" :placeholder="placeholder" :readonly="isReadOnly" v-bind="attributes" />
		</div>
		<el-progress style="margin: 0px 1px 5px 1px;" :percentage="strengthDetails.percent" :color="strengthDetails.color" :show-text="false"></el-progress>
		<div v-show="flags.touched && flags.dirty" class="hint-block">{{length ?  'Password strength: ' + strengthDetails.label : ''}}</div>
		<div v-show="flags.touched && flags.dirty" class="hint-block" v-if="minLength">Your password must be at least <strong>{{minLength}}</strong> characters long. It is currently {{length}} characters.</div>
		<div v-show="flags.touched && flags.dirty" class="hint-block" v-if="hackedTimes != null && checkPasswordDatabases">
			<div v-if="hackedTimes == 'loading'"><i class=" neonSpinner el-icon-loading"></i> Checking...</div>
			<div v-if="hackedTimes == 0 && length > 0" class=" "><i class="fa fa-check"></i> This password has not been compromised.</div>
			<div v-if="hackedTimes > 0" class="" ><i class="fa fa-exclamation"></i> Oh no! This password was found <strong>{{hackedTimes}}</strong> times in compromised passwords databases!</div>
		</div>
	</neon-core-form-field>
</template>

<script>
import FieldBase from './FieldBase.vue';

export default {
	name: 'neon-core-form-fields-password',
	extends: FieldBase,
	props: {
		value: String,
		checkPasswordDatabases: {type: Boolean, default: true}
	},
	data: function() {
		return {
			showPassword: false,
			hackedTimes: null,
			factor: 0
		};
	},
	watch: {
		'value': function(val) {
			this.debouncedPasswordCrackCheck();
		}
	},
	created() {
		var vm = this;
		this.debouncedPasswordCrackCheck = _.debounce(_.throttle(function() {
			return vm.passwordCrackCheck();
		}, 1500, {leading:true}), 200);
	},
	methods: {
		passwordCrackCheck: function() {
			if (!this.checkPasswordDatabases)
				return;
			this.hackedTimes = 'loading';
			var vm = this;
			neon.hibpPasswordCheck(this.value).then(function(timesComprimised) {
				vm.hackedTimes = timesComprimised;
			})
		}
	},
	computed: {
		length: function() {
			return this.value ? this.value.length : 0;
		},
		strength:  function() {
			var length = this.length;
			var lower = /[a-z]/, upper = /[A-Z]/, number = /[0-9]/, special = /[^a-zA-Z0-9]/;
			this.factor = 0;
			if (lower.test(this.value)) this.factor += 26;
			if (upper.test(this.value)) this.factor += 26;
			if (number.test(this.value)) this.factor += 10;
			if (special.test(this.value)) this.factor += 30; // approximation but represents most common special characters
			var strength = Math.pow(this.factor, length);
			return strength ? strength : 0;
		},
		/**
		 * This attempts to approximate how long it would take to crack a password
		 * it does this by simply dividing the number of hashing algorithms a computer
		 * can run per second by the strength of the password, where the strength is given by
		 * the total number of combinations.
		 */
		calculateTimeToCrack: function() {},
		strengthDetails: function() {
			var bad   = 10e+8;
			var weak   = 10e+10;
			var medium = 10e+14; // quintillion
			var high   = 10e+21; // Sextillion
			var strong = 10e+24; // Septillion
			var veryStrong = 10e+30; // Nonillion
			var googol = 10e+100; // Googol - google :-)

			var percent = (this.length * this.factor / 1200) * 100;
			percent = (percent < 100) ? percent : 100;
			var hue = (((percent/100))*120).toString(10);
			var color = ["hsl(",hue,",100%,40%)"].join("");
			var label = (function(strength) {
				if (strength <= bad) return 'Bad';
				if (strength < weak) return 'Bad';
				if (strength < medium) return 'Weak';
				if (strength < high) return 'Medium';
				if (strength < strong) return 'High';
				if (strength < veryStrong) return 'Strong';
				if (strength < googol) return 'Very Strong';
				if (strength > googol) return 'Googoliciously Strong!';
				return 'unknown';
			})(this.strength);
			return {label:label, percent:percent, color:color};
		},
		/**
		 * Attempts to find the min password length from the validator
		 * @returns {Number}
		 */
		minLength: function() {
			// find the string validator
			// - it cuts to root of how validators are managed which is a little messy
			var stringValidator = _.find(this.validators, function(v) {
				var c = v.component || v.class;
				return c == 'neon\\core\\validators\\StringValidator' || c == 'string';
			});
			return _.get(stringValidator,'min');
		}
	}
}
</script>