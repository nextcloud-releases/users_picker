import { registerWidget, registerCustomPickerElement, NcCustomPickerRenderResult } from '@nextcloud/vue/dist/Components/NcRichText.js'
import { linkTo } from '@nextcloud/router'
import { getRequestToken } from '@nextcloud/auth'

__webpack_nonce__ = btoa(getRequestToken()) // eslint-disable-line
__webpack_public_path__ = linkTo('users_picker', 'js/') // eslint-disable-line

registerWidget('users_picker_profile', async (el, { richObjectType, richObject, accessible }) => {
	const { default: Vue } = await import(/* webpackChunkName: "reference-issue-lazy" */'vue')
	const { default: UsersPickerReferenceWidget } = await import(/* webpackChunkName: "reference-issue-lazy" */'./views/ProfilePickerReferenceWidget.vue')
	Vue.mixin({ methods: { t, n } })
	const Widget = Vue.extend(UsersPickerReferenceWidget)
	new Widget({
		propsData: {
			richObjectType,
			richObject,
			accessible,
		},
	}).$mount(el)
})

registerCustomPickerElement('profile_picker', async (el, { providerId, accessible }) => {
	const { default: Vue } = await import(/* webpackChunkName: "vue-lazy" */'vue')
	Vue.mixin({ methods: { t, n } })
	const { default: UserProfilesCustomPicker } = await import(/* webpackChunkName: "image-picker-lazy" */'./components/ProfilesCustomPicker.vue')
	const Element = Vue.extend(UserProfilesCustomPicker)
	const vueElement = new Element({
		propsData: {
			providerId,
			accessible,
		},
	}).$mount(el)
	return new NcCustomPickerRenderResult(vueElement.$el, vueElement)
}, (el, renderResult) => {
	console.debug('Users picker custom picker destroy callback. el', el, 'renderResult:', renderResult)
	renderResult.object.$destroy()
}, 'normal')
