import {} from '@nextcloud/vue-richtext'
import { registerWidget } from '@nextcloud/vue/dist/Components/NcRichText.js'

__webpack_nonce__ = btoa(OC.requestToken) // eslint-disable-line
__webpack_public_path__ = OC.linkTo('users_picker', 'js/') // eslint-disable-line

registerWidget('users_picker_profile', async (el, { richObjectType, richObject, accessible }) => {
	const { default: Vue } = await import(/* webpackChunkName: "reference-issue-lazy" */'vue')
	const { default: UsersPickerReferenceWidget } = await import(/* webpackChunkName: "reference-issue-lazy" */'./views/UsersPickerReferenceWidget.vue')
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
