<!--
 - @copyright Copyright (c) 2023 Andrey Borysenko <andrey18106x@gmail.com>
 -
 - @copyright Copyright (c) 2023 Alexander Piskun <bigcat88@icloud.com>
 -
 - @author 2023 Andrey Borysenko <andrey18106x@gmail.com>
 -
 - @license AGPL-3.0-or-later
 -
 - This program is free software: you can redistribute it and/or modify
 - it under the terms of the GNU Affero General Public License as
 - published by the Free Software Foundation, either version 3 of the
 - License, or (at your option) any later version.
 -
 - This program is distributed in the hope that it will be useful,
 - but WITHOUT ANY WARRANTY; without even the implied warranty of
 - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 - GNU Affero General Public License for more details.
 -
 - You should have received a copy of the GNU Affero General Public License
 - along with this program. If not, see <http://www.gnu.org/licenses/>.
 -
 -->

<template>
	<div class="users_picker-reference">
		<div class="users_picker-wrapper">
			<NcAvatar :user="richObject.user_id" />
			<div class="link-content">
				<div class="profile-title">
					<a :href="richObject.url" target="_blank">
						<strong>
							{{ richObject.title }}
						</strong>
					</a>
					<span class="profile-subline">
						{{ richObject.subline }}
					</span>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import NcAvatar from '@nextcloud/vue/dist/Components/NcAvatar.js'

export default {
	name: 'UsersPickerReferenceWidget',

	components: {
		NcAvatar,
	},

	props: {
		richObjectType: {
			type: String,
			default: '',
		},
		richObject: {
			type: Object,
			default: null,
		},
		accessible: {
			type: Boolean,
			default: true,
		},
	},

	data() {
		return {
		}
	},

	computed: {
	},
}
</script>

<style scoped lang="scss">
.users_picker-reference {
	width: 100%;
	white-space: normal;
	padding: 12px;

	.users_picker-wrapper {
		display: flex;
		flex-direction: row;
		align-items: center;

		.link-content {
			display: flex;
			flex-direction: column;
			justify-content: center;
			margin-left: 12px;
		}
	}
}
</style>
