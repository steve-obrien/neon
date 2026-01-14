


<div class="workbench">
	<header class="workbenchHeader">
		<h1 class="workbenchHeader_title">Cobe CMS</h1>
		<div class="toolbar">
			<a class="btn btn-primary " href="{url route='/cms/editor/index'}"><i class="fa fa-pencil fa-btn" aria-hidden="true"></i> Edit Pages</a>
			<a class="btn btn-primary " href="{url route='/cms/redirects/index'}"><i class="fa fa-pencil fa-btn" aria-hidden="true"></i> Redirects</a>
		</div>
	</header>
	<div class="workbenchBody">
		<div class="workbenchBody_content" id="redirects" style="background:#e1e5ee">
			<div class="container">

				<div class="panel panel-default p-5" style="margin-top:8px;">
					<div class="panel-heading">
						<h3 class="mt-0">Add Redirect</h3>
					</div>
					<div class="">
						<table class="table" style="table-layout: fixed;margin-bottom:0;">
							<thead>
								<tr>
									<th style="width:40%">Url</th>
									<th style="width:40px"></th>
									<th style="width:45%">Redirect</th>
									<th style="width:70px; text-align:right;"></th>
									<th style="width:70px"></th>
								</tr>
							</thead>
							<tr>
								<td style="vertical-align: top;">
									<slug-input :class="{ 'has-error': errors.url.length }" style="width:100%" v-model="newRedirect.url"></slug-input>
									<div v-if="errors.url.length" class="text-danger">[[ errors.url[0] ]]</div>
								</td>
								<td><svg style="width:20px;color:#99a3b5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg></td>
								<td style="vertical-align: top;">
									<slug-input :class="{ 'has-error': errors.redirect.length }" :allow-http="true" style="width:100%" v-model="newRedirect.redirect"></slug-input>
									<div v-if="errors.redirect.length" class="text-danger">[[ errors.redirect[0] ]]</div>
								</td>
								<td></td>
								<td class="text-right" style="vertical-align: top"><button :disabled="status!=='START'" class="btn btn-primary" @click="add">Add</button></td>
							</tr>
						</table>
					</div>
				</div>

				<div class="panel panel-default p-5">
					<div class="panel-heading">
						<h3 class="mt-0">Redirects</h3>
					</div>
					<div class="">
						<table class="table" style="table-layout: fixed;margin-bottom:0;">
							<thead>
								<tr>
									<th style="width:40%;cursor:pointer;" @click="sortBy('url')" >
                                        Url
										<svg v-if="sortKey=='url'" style="display:inline-block; width:10px;" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
											<path v-if="sortOrders['url'] < 0" d="M19 9l-7 7-7-7"></path>
											<path v-if="sortOrders['url'] > 0" d="M5 15l7-7 7 7"></path>
										</svg>
									</th>
									<th style="width:40px"></th>
									<th style="width:45%;cursor:pointer;" @click="sortBy('redirect')">
										Redirect
										<svg v-if="sortKey=='redirect'" style="display:inline-block; width:10px;" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
											<path v-if="sortOrders['redirect'] < 0" d="M19 9l-7 7-7-7"></path>
											<path v-if="sortOrders['redirect'] > 0" d="M5 15l7-7 7 7"></path>
										</svg>
									</th>
									<th style="width:50px"></th>
									<th style="width:50px"></th>
								</tr>
							</thead>
							<thead>
								<tr>
									<th>
										<label style="position:relative;width:100%;">
											<svg style="width:16px; top:5px; left:4px; position:absolute;" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
											<input style="padding:4px 4px 4px 24px;border:0;width:100%;" v-model="search.url" />
										</label>
									</th>
									<th></th>
									<th>
										<label style="position:relative;width:100%;">
											<svg style="width:16px; top:5px; left:4px; position:absolute;" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
											<input style="padding:4px 4px 4px 24px;border:0;width:100%;" v-model="search.redirect" />
										</label>
									</th>
									<th></th>
									<th></th>
								</tr>
							</thead>
							<tr v-for="url in allUrls" :key="url.url">
								<td class="truncate"><a target="urltest" :href="base+url.url" rel="noopener">[[url.url]]</a></td>
								<td><svg style="width:100%;color:#99a3b5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg></td>
								<td class="truncate">[[url.redirect]]</td>
								<td class="text-right ">301</td>
								<td class="text-right" style="vertical-align: top"><button class="btn" @click="deleteRedirect(url.url)"><i class="text-danger fa fa-trash"></i></button></td>
							</tr>
						</table>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>

{css}
	.p-5 { padding:  20px; }
	.truncate {
		overflow: hidden!important;
		text-overflow: ellipsis!important;
		white-space: nowrap!important;
	}
{/css}

{js}
<script>
	var urls = {json_encode($urls)};

	Vue.component('slug-input', {
		delimiters: ['[[', ']]'],
		props: {
			allowHttp: false,
			value: '',
		},
		template: `<div>
			<input class="form-control" v-model="update" />
			<div>
				<span v-if="!isHttp(value)" style="color:#666">[[base]]</span><strong>[[value]]</strong>
			</div>
		</div>
		`,
		methods: {
			isHttp: function(value) {
				if (_.isString(value) && this.allowHttp)
					return value.match(new RegExp("(^[/]?http[s]?://)"));
				return false;
			}
		},
		computed: {
			base: function () {
				if (this.isHttp(this.value))
					return this.value;
				return neon.url('', undefined, true).replace(/\/$/, "");
			},
			update: {
				get: function () {
					return this.value;
				},
				set: function (value) {
					if (this.isHttp(value)) {
						value = value.replace(new RegExp("^[/]"), "");
					} else {
						value = value.replace(new RegExp("^[/]+"), ""); // trim multiple / from the beginning of the url
						value = '/' + value; // add a starting / if one does not exist.
						value = value.replace(new RegExp("//+"), "/"); // collapse slashes
					}
					this.$emit('input', value);
				}
			}
		}
	});

	var STATES = {
		START: 'START',
		ADDING: 'ADDING',
	};

	new Vue({
		delimiters: ['[[', ']]'],
		el: '#redirects',
		data:function() {
			return {
				status: STATES.START,
				urls: urls,
				sortKey: 'url',
				sortOrders: { url: 1, redirect: 1, hits: 1 },
				search: { url: '', redirect: '', },
				newRedirect: { url: '', redirect:'', },
				errors: {
					url: [],
					redirect: []
				},
			}
		},
		computed: {
			base () {
				return neon.base(true);
			},
			allUrls () {
				let filtered = this.urls;
				let filter = (filterable, fieldName) => {
					return _.filter(filterable, item => {
						return item[fieldName].toLowerCase().indexOf(this.search[fieldName]) > -1
					});
				};
				if (!_.isEmpty(this.search.url)) filtered = filter(filtered, 'url');
				if (!_.isEmpty(this.search.redirect)) filtered = filter(filtered, 'redirect');

				// sort
				if (this.sortKey)
					filtered = _.orderBy(filtered, this.sortKey, (this.sortOrders[this.sortKey] > 0 ? 'asc' : 'desc'))
				return filtered;
			}
		},
		mounted() {
			window.v = this;
		},
		methods: {
			sortBy: function(key) {
				this.sortKey = key;
				this.sortOrders[key] = this.sortOrders[key] * -1;
			},
			validate: function () {
				if (_.isEmpty(this.newRedirect.url))
					this.errors.url.push('A url is required');
				else
					this.errors.url = [];
				if (_.isEmpty(this.newRedirect.redirect))
					this.errors.redirect.push('A redirect is required');
				else
					this.errors.redirect = [];
				return this.errors.url.length === 0 && this.errors.redirect.length === 0
			},
			add: function () {
				if (this.status !== STATES.START) return;

				this.status = STATES.ADDING;

				if (!this.validate()) {
					this.status = STATES.START;
					return;
				}

				$.post(neon.url('/cms/redirects/add'), { url: this.newRedirect.url, redirect: this.newRedirect.redirect })
					.done(url => {
						this.urls.push(url);
						this.newRedirect.url = '';
						this.newRedirect.redirect = '';
					})
					.fail(response => {
						if (response.status===400) {
							_.each(response.responseJSON, (error, key) => {
								this.errors[key] = error;
							})
						}
					})
					.always(() => {
						this.status = STATES.START;
					});
			},
			deleteRedirect: function(url) {
				$.post(neon.url('/cms/redirects/delete'), { url: url }).done(() => {
					this.urls.splice(_.findIndex(v.urls, { url: url }), 1);
				})
			}
		},
	});
</script>
{/js}
