<!--
	This could do with some work - Handle country code better 
	Currently can not search by typing number or auto detect when typing number
-->
<template>
	<neon-core-form-field v-bind="fieldProps" class="neonFieldPhone">
		<div v-if="isPrintOnly">{{value}}</div>
		<div v-else>
			<div class="neonInputGroup neonInputEffect" v-show="!countryPicker">
				<div class="neonInputGroup_prepend" @click="selectCountry()">
					<span class="neonPhone_flag">{{country.flag}}</span> {{country.dial}} <i class="fa fa-caret-down"></i>
				</div>
				<input :name="inputName+'[no]'" ref="phone" @blur="onBlur()" @focus="onFocus()" @keydown="onKeyup($event)" v-model="no" type="text" class="form-control" :placeholder="placeholder" :readOnly="isReadOnly">
			</div>
			<neon-select
				v-show="countryPicker" 
				:name="inputName+'[cc]'"
				ref="country_select"
				class-dropdown="neonFieldPhone_dialCode" 
				v-model="cc" 
				:allow-clear="false" 
				:items="countries" 
				:render-option="renderOption" 
				:render-item="renderItem"
				:search-field="['label', 'value', 'dial']"
				@focus="onFocus()"
				@blur="countryPicker=false && onBlur()"
				@input="updateCountry($event)">
			</neon-select>
		</div>
	</neon-core-form-field>
</template>

<script>
export default {
	name: 'neon-core-form-fields-phone',
	extends: Vue.component('neon-core-form-fields-base'),
	props: {
		value: [String, Object],
		defaultCountry: {type:String, default:'GB'}
	},
	data() {
		return {countryPicker:false};
	},
	template: `
			
		`,
	created: function() {
		if (!_.isObject(this.modelValue)) {
			neon.form.updateField(this.path, {value: {cc : this.defaultCountry, no: '', dc: this.dial}});
		}
	},
	methods: {
		onKeyup: function($event) {
			if ($event.target.value==='') {
				if ($event.keyCode === 8) {
					this.selectCountry();
				}
			}
		},
		updateCountry: function(value) {
			this.countryPicker=false;
			this.$emit('input', value)
			vm = this;
			Vue.nextTick(function() {$(vm.$refs.phone).focus();});
		},
		selectCountry: function() {
			this.countryPicker = true;
			var vm = this;
			Vue.nextTick(function() {
				var selectize = $(vm.$refs.country_select.$el).find('select')[0].selectize;
				selectize.open();
				selectize.focus();
			})
		},
		renderItem:function(item, escape) {
			return '<div><span class="neonPhone_flag">' + escape(item.flag) + '</span> ' + escape(item.dial) + '</div>';
		},
		renderOption:function(item, escape) {
			return '<div><span class="neonPhone_flag">' + escape(item.flag) + '</span> ' + escape(item.label) + ' ' + escape(item.dial) + '</div>';
		}
	},
	computed: {
		cc: {
			get: function() {
				if (_.isObject(this.modelValue))
					return this.modelValue['cc'];
				return null;
			},
			set: function(val) {
				this.modelValue = {cc: val, no: this.no, dc: this.dial};
			}
		},
		no: {
			get: function() {
				return this.modelValue['no'];
			},
			set: function(val) {
				this.modelValue = {cc: this.cc, no: val, dc: this.dial};
			}
		},
		/**
		 * Get the country object of the currently selected country
		 * @returns {*}
		 */
		country: function() {
			var countryCode = this.cc ? this.cc : this.defaultCountry;
			return _.find(this.countries, { 'value':  countryCode});
		},
		dial: function() {
			return this.country.dial;
		},
		countries: function() {
			return [{"label":"Afghanistan","flag":"🇦🇫","value":"AF","dial":"+93"},{"label":"Åland Islands","flag":"🇦🇽","value":"AX","dial":"+358"},{"label":"Albania","flag":"🇦🇱","value":"AL","dial":"+355"},{"label":"Algeria","flag":"🇩🇿","value":"DZ","dial":"+213"},{"label":"American Samoa","flag":"🇦🇸","value":"AS","dial":"+1684"},{"label":"Andorra","flag":"🇦🇩","value":"AD","dial":"+376"},{"label":"Angola","flag":"🇦🇴","value":"AO","dial":"+244"},{"label":"Anguilla","flag":"🇦🇮","value":"AI","dial":"+1264"},{"label":"Antarctica","flag":"🇦🇶","value":"AQ","dial":"+672"},{"label":"Antigua and Barbuda","flag":"🇦🇬","value":"AG","dial":"+1268"},{"label":"Argentina","flag":"🇦🇷","value":"AR","dial":"+54"},{"label":"Armenia","flag":"🇦🇲","value":"AM","dial":"+374"},{"label":"Aruba","flag":"🇦🇼","value":"AW","dial":"+297"},{"label":"Australia","flag":"🇦🇺","value":"AU","dial":"+61"},{"label":"Austria","flag":"🇦🇹","value":"AT","dial":"+43"},{"label":"Azerbaijan","flag":"🇦🇿","value":"AZ","dial":"+994"},{"label":"Bahamas","flag":"🇧🇸","value":"BS","dial":"+1242"},{"label":"Bahrain","flag":"🇧🇭","value":"BH","dial":"+973"},{"label":"Bangladesh","flag":"🇧🇩","value":"BD","dial":"+880"},{"label":"Barbados","flag":"🇧🇧","value":"BB","dial":"+1246"},{"label":"Belarus","flag":"🇧🇾","value":"BY","dial":"+375"},{"label":"Belgium","flag":"🇧🇪","value":"BE","dial":"+32"},{"label":"Belize","flag":"🇧🇿","value":"BZ","dial":"+501"},{"label":"Benin","flag":"🇧🇯","value":"BJ","dial":"+229"},{"label":"Bermuda","flag":"🇧🇲","value":"BM","dial":"+1441"},{"label":"Bhutan","flag":"🇧🇹","value":"BT","dial":"+975"},{"label":"Bolivia, Plurinational State of bolivia","flag":"🇧🇴","value":"BO","dial":"+591"},{"label":"Bosnia and Herzegovina","flag":"🇧🇦","value":"BA","dial":"+387"},{"label":"Botswana","flag":"🇧🇼","value":"BW","dial":"+267"},{"label":"Bouvet Island","flag":"🇧🇻","value":"BV","dial":"+47"},{"label":"Brazil","flag":"🇧🇷","value":"BR","dial":"+55"},{"label":"British Indian Ocean Territory","flag":"🇮🇴","value":"IO","dial":"+246"},{"label":"Brunei Darussalam","flag":"🇧🇳","value":"BN","dial":"+673"},{"label":"Bulgaria","flag":"🇧🇬","value":"BG","dial":"+359"},{"label":"Burkina Faso","flag":"🇧🇫","value":"BF","dial":"+226"},{"label":"Burundi","flag":"🇧🇮","value":"BI","dial":"+257"},{"label":"Cambodia","flag":"🇰🇭","value":"KH","dial":"+855"},{"label":"Cameroon","flag":"🇨🇲","value":"CM","dial":"+237"},{"label":"Canada","flag":"🇨🇦","value":"CA","dial":"+1"},{"label":"Cape Verde","flag":"🇨🇻","value":"CV","dial":"+238"},{"label":"Cayman Islands","flag":"🇰🇾","value":"KY","dial":"+345"},{"label":"Central African Republic","flag":"🇨🇫","value":"CF","dial":"+236"},{"label":"Chad","flag":"🇹🇩","value":"TD","dial":"+235"},{"label":"Chile","flag":"🇨🇱","value":"CL","dial":"+56"},{"label":"China","flag":"🇨🇳","value":"CN","dial":"+86"},{"label":"Christmas Island","flag":"🇨🇽","value":"CX","dial":"+61"},{"label":"Cocos (Keeling) Islands","flag":"🇨🇨","value":"CC","dial":"+61"},{"label":"Colombia","flag":"🇨🇴","value":"CO","dial":"+57"},{"label":"Comoros","flag":"🇰🇲","value":"KM","dial":"+269"},{"label":"Congo","flag":"🇨🇬","value":"CG","dial":"+242"},{"label":"Congo, The Democratic Republic of the Congo","flag":"🇨🇩","value":"CD","dial":"+243"},{"label":"Cook Islands","flag":"🇨🇰","value":"CK","dial":"+682"},{"label":"Costa Rica","flag":"🇨🇷","value":"CR","dial":"+506"},{"label":"Cote d'Ivoire","flag":"🇨🇮","value":"CI","dial":"+225"},{"label":"Croatia","flag":"🇭🇷","value":"HR","dial":"+385"},{"label":"Cuba","flag":"🇨🇺","value":"CU","dial":"+53"},{"label":"Cyprus","flag":"🇨🇾","value":"CY","dial":"+357"},{"label":"Czech Republic","flag":"🇨🇿","value":"CZ","dial":"+420"},{"label":"Denmark","flag":"🇩🇰","value":"DK","dial":"+45"},{"label":"Djibouti","flag":"🇩🇯","value":"DJ","dial":"+253"},{"label":"Dominica","flag":"🇩🇲","value":"DM","dial":"+1767"},{"label":"Dominican Republic","flag":"🇩🇴","value":"DO","dial":"+1849"},{"label":"Ecuador","flag":"🇪🇨","value":"EC","dial":"+593"},{"label":"Egypt","flag":"🇪🇬","value":"EG","dial":"+20"},{"label":"El Salvador","flag":"🇸🇻","value":"SV","dial":"+503"},{"label":"Equatorial Guinea","flag":"🇬🇶","value":"GQ","dial":"+240"},{"label":"Eritrea","flag":"🇪🇷","value":"ER","dial":"+291"},{"label":"Estonia","flag":"🇪🇪","value":"EE","dial":"+372"},{"label":"Ethiopia","flag":"🇪🇹","value":"ET","dial":"+251"},{"label":"Falkland Islands (Malvinas)","flag":"🇫🇰","value":"FK","dial":"+500"},{"label":"Faroe Islands","flag":"🇫🇴","value":"FO","dial":"+298"},{"label":"Fiji","flag":"🇫🇯","value":"FJ","dial":"+679"},{"label":"Finland","flag":"🇫🇮","value":"FI","dial":"+358"},{"label":"France","flag":"🇫🇷","value":"FR","dial":"+33"},{"label":"French Guiana","flag":"🇬🇫","value":"GF","dial":"+594"},{"label":"French Polynesia","flag":"🇵🇫","value":"PF","dial":"+689"},{"label":"French Southern Territories","flag":"🇹🇫","value":"TF","dial":"+262"},{"label":"Gabon","flag":"🇬🇦","value":"GA","dial":"+241"},{"label":"Gambia","flag":"🇬🇲","value":"GM","dial":"+220"},{"label":"Georgia","flag":"🇬🇪","value":"GE","dial":"+995"},{"label":"Germany","flag":"🇩🇪","value":"DE","dial":"+49"},{"label":"Ghana","flag":"🇬🇭","value":"GH","dial":"+233"},{"label":"Gibraltar","flag":"🇬🇮","value":"GI","dial":"+350"},{"label":"Greece","flag":"🇬🇷","value":"GR","dial":"+30"},{"label":"Greenland","flag":"🇬🇱","value":"GL","dial":"+299"},{"label":"Grenada","flag":"🇬🇩","value":"GD","dial":"+1473"},{"label":"Guadeloupe","flag":"🇬🇵","value":"GP","dial":"+590"},{"label":"Guam","flag":"🇬🇺","value":"GU","dial":"+1671"},{"label":"Guatemala","flag":"🇬🇹","value":"GT","dial":"+502"},{"label":"Guernsey","flag":"🇬🇬","value":"GG","dial":"+44"},{"label":"Guinea","flag":"🇬🇳","value":"GN","dial":"+224"},{"label":"Guinea-Bissau","flag":"🇬🇼","value":"GW","dial":"+245"},{"label":"Guyana","flag":"🇬🇾","value":"GY","dial":"+592"},{"label":"Haiti","flag":"🇭🇹","value":"HT","dial":"+509"},{"label":"Heard Island and Mcdonald Islands","flag":"🇭🇲","value":"HM","dial":"+672"},{"label":"Holy See (Vatican City State)","flag":"🇻🇦","value":"VA","dial":"+379"},{"label":"Honduras","flag":"🇭🇳","value":"HN","dial":"+504"},{"label":"Hong Kong","flag":"🇭🇰","value":"HK","dial":"+852"},{"label":"Hungary","flag":"🇭🇺","value":"HU","dial":"+36"},{"label":"Iceland","flag":"🇮🇸","value":"IS","dial":"+354"},{"label":"India","flag":"🇮🇳","value":"IN","dial":"+91"},{"label":"Indonesia","flag":"🇮🇩","value":"ID","dial":"+62"},{"label":"Iran, Islamic Republic of Persian Gulf","flag":"🇮🇷","value":"IR","dial":"+98"},{"label":"Iraq","flag":"🇮🇶","value":"IQ","dial":"+964"},{"label":"Ireland","flag":"🇮🇪","value":"IE","dial":"+353"},{"label":"Isle of Man","flag":"🇮🇲","value":"IM","dial":"+44"},{"label":"Israel","flag":"🇮🇱","value":"IL","dial":"+972"},{"label":"Italy","flag":"🇮🇹","value":"IT","dial":"+39"},{"label":"Jamaica","flag":"🇯🇲","value":"JM","dial":"+1876"},{"label":"Japan","flag":"🇯🇵","value":"JP","dial":"+81"},{"label":"Jersey","flag":"🇯🇪","value":"JE","dial":"+44"},{"label":"Jordan","flag":"🇯🇴","value":"JO","dial":"+962"},{"label":"Kazakhstan","flag":"🇰🇿","value":"KZ","dial":"+7"},{"label":"Kenya","flag":"🇰🇪","value":"KE","dial":"+254"},{"label":"Kiribati","flag":"🇰🇮","value":"KI","dial":"+686"},{"label":"Korea, Democratic People's Republic of Korea","flag":"🇰🇵","value":"KP","dial":"+850"},{"label":"Korea, Republic of South Korea","flag":"🇰🇷","value":"KR","dial":"+82"},{"label":"Kosovo","flag":"🇽🇰","value":"XK","dial":"+383"},{"label":"Kuwait","flag":"🇰🇼","value":"KW","dial":"+965"},{"label":"Kyrgyzstan","flag":"🇰🇬","value":"KG","dial":"+996"},{"label":"Laos","flag":"🇱🇦","value":"LA","dial":"+856"},{"label":"Latvia","flag":"🇱🇻","value":"LV","dial":"+371"},{"label":"Lebanon","flag":"🇱🇧","value":"LB","dial":"+961"},{"label":"Lesotho","flag":"🇱🇸","value":"LS","dial":"+266"},{"label":"Liberia","flag":"🇱🇷","value":"LR","dial":"+231"},{"label":"Libyan Arab Jamahiriya","flag":"🇱🇾","value":"LY","dial":"+218"},{"label":"Liechtenstein","flag":"🇱🇮","value":"LI","dial":"+423"},{"label":"Lithuania","flag":"🇱🇹","value":"LT","dial":"+370"},{"label":"Luxembourg","flag":"🇱🇺","value":"LU","dial":"+352"},{"label":"Macao","flag":"🇲🇴","value":"MO","dial":"+853"},{"label":"Macedonia","flag":"🇲🇰","value":"MK","dial":"+389"},{"label":"Madagascar","flag":"🇲🇬","value":"MG","dial":"+261"},{"label":"Malawi","flag":"🇲🇼","value":"MW","dial":"+265"},{"label":"Malaysia","flag":"🇲🇾","value":"MY","dial":"+60"},{"label":"Maldives","flag":"🇲🇻","value":"MV","dial":"+960"},{"label":"Mali","flag":"🇲🇱","value":"ML","dial":"+223"},{"label":"Malta","flag":"🇲🇹","value":"MT","dial":"+356"},{"label":"Marshall Islands","flag":"🇲🇭","value":"MH","dial":"+692"},{"label":"Martinique","flag":"🇲🇶","value":"MQ","dial":"+596"},{"label":"Mauritania","flag":"🇲🇷","value":"MR","dial":"+222"},{"label":"Mauritius","flag":"🇲🇺","value":"MU","dial":"+230"},{"label":"Mayotte","flag":"🇾🇹","value":"YT","dial":"+262"},{"label":"Mexico","flag":"🇲🇽","value":"MX","dial":"+52"},{"label":"Micronesia, Federated States of Micronesia","flag":"🇫🇲","value":"FM","dial":"+691"},{"label":"Moldova","flag":"🇲🇩","value":"MD","dial":"+373"},{"label":"Monaco","flag":"🇲🇨","value":"MC","dial":"+377"},{"label":"Mongolia","flag":"🇲🇳","value":"MN","dial":"+976"},{"label":"Montenegro","flag":"🇲🇪","value":"ME","dial":"+382"},{"label":"Montserrat","flag":"🇲🇸","value":"MS","dial":"+1664"},{"label":"Morocco","flag":"🇲🇦","value":"MA","dial":"+212"},{"label":"Mozambique","flag":"🇲🇿","value":"MZ","dial":"+258"},{"label":"Myanmar","flag":"🇲🇲","value":"MM","dial":"+95"},{"label":"Namibia","flag":"🇳🇦","value":"NA","dial":"+264"},{"label":"Nauru","flag":"🇳🇷","value":"NR","dial":"+674"},{"label":"Nepal","flag":"🇳🇵","value":"NP","dial":"+977"},{"label":"Netherlands","flag":"🇳🇱","value":"NL","dial":"+31"},{"label":"Netherlands Antilles","flag":"","value":"AN","dial":"+599"},{"label":"New Caledonia","flag":"🇳🇨","value":"NC","dial":"+687"},{"label":"New Zealand","flag":"🇳🇿","value":"NZ","dial":"+64"},{"label":"Nicaragua","flag":"🇳🇮","value":"NI","dial":"+505"},{"label":"Niger","flag":"🇳🇪","value":"NE","dial":"+227"},{"label":"Nigeria","flag":"🇳🇬","value":"NG","dial":"+234"},{"label":"Niue","flag":"🇳🇺","value":"NU","dial":"+683"},{"label":"Norfolk Island","flag":"🇳🇫","value":"NF","dial":"+672"},{"label":"Northern Mariana Islands","flag":"🇲🇵","value":"MP","dial":"+1670"},{"label":"Norway","flag":"🇳🇴","value":"NO","dial":"+47"},{"label":"Oman","flag":"🇴🇲","value":"OM","dial":"+968"},{"label":"Pakistan","flag":"🇵🇰","value":"PK","dial":"+92"},{"label":"Palau","flag":"🇵🇼","value":"PW","dial":"+680"},{"label":"Palestinian Territory, Occupied","flag":"🇵🇸","value":"PS","dial":"+970"},{"label":"Panama","flag":"🇵🇦","value":"PA","dial":"+507"},{"label":"Papua New Guinea","flag":"🇵🇬","value":"PG","dial":"+675"},{"label":"Paraguay","flag":"🇵🇾","value":"PY","dial":"+595"},{"label":"Peru","flag":"🇵🇪","value":"PE","dial":"+51"},{"label":"Philippines","flag":"🇵🇭","value":"PH","dial":"+63"},{"label":"Pitcairn","flag":"🇵🇳","value":"PN","dial":"+64"},{"label":"Poland","flag":"🇵🇱","value":"PL","dial":"+48"},{"label":"Portugal","flag":"🇵🇹","value":"PT","dial":"+351"},{"label":"Puerto Rico","flag":"🇵🇷","value":"PR","dial":"+1939"},{"label":"Qatar","flag":"🇶🇦","value":"QA","dial":"+974"},{"label":"Romania","flag":"🇷🇴","value":"RO","dial":"+40"},{"label":"Russia","flag":"🇷🇺","value":"RU","dial":"+7"},{"label":"Rwanda","flag":"🇷🇼","value":"RW","dial":"+250"},{"label":"Reunion","flag":"🇷🇪","value":"RE","dial":"+262"},{"label":"Saint Barthelemy","flag":"🇧🇱","value":"BL","dial":"+590"},{"label":"Saint Helena, Ascension and Tristan Da Cunha","flag":"🇸🇭","value":"SH","dial":"+290"},{"label":"Saint Kitts and Nevis","flag":"🇰🇳","value":"KN","dial":"+1869"},{"label":"Saint Lucia","flag":"🇱🇨","value":"LC","dial":"+1758"},{"label":"Saint Martin","flag":"🇲🇫","value":"MF","dial":"+590"},{"label":"Saint Pierre and Miquelon","flag":"🇵🇲","value":"PM","dial":"+508"},{"label":"Saint Vincent and the Grenadines","flag":"🇻🇨","value":"VC","dial":"+1784"},{"label":"Samoa","flag":"🇼🇸","value":"WS","dial":"+685"},{"label":"San Marino","flag":"🇸🇲","value":"SM","dial":"+378"},{"label":"Sao Tome and Principe","flag":"🇸🇹","value":"ST","dial":"+239"},{"label":"Saudi Arabia","flag":"🇸🇦","value":"SA","dial":"+966"},{"label":"Senegal","flag":"🇸🇳","value":"SN","dial":"+221"},{"label":"Serbia","flag":"🇷🇸","value":"RS","dial":"+381"},{"label":"Seychelles","flag":"🇸🇨","value":"SC","dial":"+248"},{"label":"Sierra Leone","flag":"🇸🇱","value":"SL","dial":"+232"},{"label":"Singapore","flag":"🇸🇬","value":"SG","dial":"+65"},{"label":"Slovakia","flag":"🇸🇰","value":"SK","dial":"+421"},{"label":"Slovenia","flag":"🇸🇮","value":"SI","dial":"+386"},{"label":"Solomon Islands","flag":"🇸🇧","value":"SB","dial":"+677"},{"label":"Somalia","flag":"🇸🇴","value":"SO","dial":"+252"},{"label":"South Africa","flag":"🇿🇦","value":"ZA","dial":"+27"},{"label":"South Sudan","flag":"🇸🇸","value":"SS","dial":"+211"},{"label":"South Georgia and the South Sandwich Islands","flag":"🇬🇸","value":"GS","dial":"+500"},{"label":"Spain","flag":"🇪🇸","value":"ES","dial":"+34"},{"label":"Sri Lanka","flag":"🇱🇰","value":"LK","dial":"+94"},{"label":"Sudan","flag":"🇸🇩","value":"SD","dial":"+249"},{"label":"Suriname","flag":"🇸🇷","value":"SR","dial":"+597"},{"label":"Svalbard and Jan Mayen","flag":"🇸🇯","value":"SJ","dial":"+47"},{"label":"Swaziland","flag":"🇸🇿","value":"SZ","dial":"+268"},{"label":"Sweden","flag":"🇸🇪","value":"SE","dial":"+46"},{"label":"Switzerland","flag":"🇨🇭","value":"CH","dial":"+41"},{"label":"Syrian Arab Republic","flag":"🇸🇾","value":"SY","dial":"+963"},{"label":"Taiwan","flag":"🇹🇼","value":"TW","dial":"+886"},{"label":"Tajikistan","flag":"🇹🇯","value":"TJ","dial":"+992"},{"label":"Tanzania, United Republic of Tanzania","flag":"🇹🇿","value":"TZ","dial":"+255"},{"label":"Thailand","flag":"🇹🇭","value":"TH","dial":"+66"},{"label":"Timor-Leste","flag":"🇹🇱","value":"TL","dial":"+670"},{"label":"Togo","flag":"🇹🇬","value":"TG","dial":"+228"},{"label":"Tokelau","flag":"🇹🇰","value":"TK","dial":"+690"},{"label":"Tonga","flag":"🇹🇴","value":"TO","dial":"+676"},{"label":"Trinidad and Tobago","flag":"🇹🇹","value":"TT","dial":"+1868"},{"label":"Tunisia","flag":"🇹🇳","value":"TN","dial":"+216"},{"label":"Turkey","flag":"🇹🇷","value":"TR","dial":"+90"},{"label":"Turkmenistan","flag":"🇹🇲","value":"TM","dial":"+993"},{"label":"Turks and Caicos Islands","flag":"🇹🇨","value":"TC","dial":"+1649"},{"label":"Tuvalu","flag":"🇹🇻","value":"TV","dial":"+688"},{"label":"Uganda","flag":"🇺🇬","value":"UG","dial":"+256"},{"label":"Ukraine","flag":"🇺🇦","value":"UA","dial":"+380"},{"label":"United Arab Emirates","flag":"🇦🇪","value":"AE","dial":"+971"},{"label":"United Kingdom","flag":"🇬🇧","value":"GB","dial":"+44"},{"label":"United States","flag":"🇺🇸","value":"US","dial":"+1"},{"label":"Uruguay","flag":"🇺🇾","value":"UY","dial":"+598"},{"label":"Uzbekistan","flag":"🇺🇿","value":"UZ","dial":"+998"},{"label":"Vanuatu","flag":"🇻🇺","value":"VU","dial":"+678"},{"label":"Venezuela, Bolivarian Republic of Venezuela","flag":"🇻🇪","value":"VE","dial":"+58"},{"label":"Vietnam","flag":"🇻🇳","value":"VN","dial":"+84"},{"label":"Virgin Islands, British","flag":"🇻🇬","value":"VG","dial":"+1284"},{"label":"Virgin Islands, U.S.","flag":"🇻🇮","value":"VI","dial":"+1340"},{"label":"Wallis and Futuna","flag":"🇼🇫","value":"WF","dial":"+681"},{"label":"Yemen","flag":"🇾🇪","value":"YE","dial":"+967"},{"label":"Zambia","flag":"🇿🇲","value":"ZM","dial":"+260"},{"label":"Zimbabwe","flag":"🇿🇼","value":"ZW","dial":"+263"}]
		}
	}
}
</script>