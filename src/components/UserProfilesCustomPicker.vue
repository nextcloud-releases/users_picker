<template>
	<div class="profiles-picker-content">
		<div class="heading">
			<h2>
				{{ t('users_picker', 'Profile picker') }}
			</h2>
			<div class="input-wrapper">
				<NcSelect ref="profiles-search-input"
					v-model="selectedProfile"
					input-id="profiles-search-input"
					:loading="loading"
					:filterable="false"
					:placeholder="t('users_picker', 'Search for a user profile')"
					:clear-search-on-blur="() => false"
					:user-select="true"
					:multiple="false"
					:options="profiles"
					@search="searchForProfile"
					@option:selecting="resolveResult">
					<template #no-options>
						{{ searchQuery ? t('users_picker', 'Not found') : t('users_picker', 'Search for a user profile. Start typing') }}
					</template>
				</NcSelect>
			</div>
			<NcEmptyContent class="empty-content">
				<template #icon>
					<UserIcon />
				</template>
			</NcEmptyContent>
		</div>
		<div class="footer">
			<NcButton v-if="selectedProfile !== null"
				type="primary"
				:aria-label="t('users_picker', 'Submit selected user profile')"
				:disabled="loading || selectedProfile === null"
				@click="submit">
				{{ t('users_picker', 'Send') }}
				<template #icon>
					<ArrowRightIcon />
				</template>
			</NcButton>
		</div>
	</div>
</template>

<script>
import ArrowRightIcon from 'vue-material-design-icons/ArrowRight.vue'
import UserIcon from './icons/UserIcon.vue'

import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'

import axios from '@nextcloud/axios'
import { generateOcsUrl, generateUrl } from '@nextcloud/router'

export default {
	name: 'UserProfilesCustomPicker',

	components: {
		NcSelect,
		NcButton,
		ArrowRightIcon,
		UserIcon,
		NcEmptyContent,
	},

	props: {
		providerId: {
			type: String,
			required: true,
		},
		accessible: {
			type: Boolean,
			default: false,
		},
	},

	data() {
		return {
			searchQuery: '',
			loading: false,
			resultUrl: null,
			reference: null,
			profiles: [],
			selectedProfile: null,
			abortController: null,
		}
	},

	computed: {
	},

	watch: {
	},

	mounted() {
		this.focusOnInput()
	},

	methods: {
		focusOnInput() {
			setTimeout(() => {
				this.$refs['profiles-search-input'].$el.getElementsByTagName('input')[0]?.focus()
			}, 300)
		},
		async searchForProfile(query) {
			this.searchQuery = query
			if (this.searchQuery === '') {
				return
			}
			this.loading = true
			const url = generateOcsUrl('core/autocomplete/get?search={searchQuery}&itemType=%20&itemId=%20&shareTypes[]=0&limit=20', { searchQuery: this.searchQuery })
			await axios.get(url).then(res => {
				this.profiles = res.data.ocs.data.map(userAutocomplete => {
					return {
						user: userAutocomplete.id,
						displayName: userAutocomplete.label,
						icon: userAutocomplete.icon,
						subtitle: userAutocomplete.subline,
						isNoUser: userAutocomplete.source.startsWith('users'),
					}
				})
				this.loading = false
			}).catch(err => {
				console.debug(err)
			})
		},
		submit() {
			this.resultUrl = window.location.origin + generateUrl(`/u/${this.selectedProfile.user.trim().toLowerCase()}`, null, { noRewrite: true })
			this.$emit('submit', this.resultUrl)
		},
		resolveResult(selectedItem) {
			this.loading = true
			this.abortController = new AbortController()
			this.selectedProfile = selectedItem
			this.resultUrl = window.location.origin + generateUrl(`/u/${this.selectedProfile.user.trim().toLowerCase()}`, null, { noRewrite: true })
			axios.get(generateOcsUrl('references/resolve', 2) + '?reference=' + encodeURIComponent(this.resultUrl), {
				signal: this.abortController.signal,
			})
				.then((response) => {
					this.reference = response.data.ocs.data.references[this.resultUrl]
				})
				.catch((error) => {
					console.error(error)
				})
				.then(() => {
					this.loading = false
				})
		},
		clearSelection() {
			console.debug('clearSelection')
			this.selectedProfile = null
			this.resultUrl = null
			this.reference = null
		},
	},
}
</script>

<style scoped lang="scss">
.heading, .select {
	width: 100%;
}

.profiles-picker-content {
	width: 100%;
	min-height: 450px;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: space-between;
	padding: 12px 16px 16px 16px;

	h2 {
		text-align: center;
	}

	.footer {
		width: 100%;
		display: flex;
		align-items: center;
		justify-content: end;
		margin-top: 12px;
		> * {
			margin-left: 4px;
		}
	}
}
</style>
