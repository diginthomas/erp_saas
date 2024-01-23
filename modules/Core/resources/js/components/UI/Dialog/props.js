/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.5
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2024 KONKORD DIGITAL
 */
import { randomString } from '@/Core/utils'

export default {
  form: Boolean,
  overlay: { type: Boolean, default: true },
  visible: Boolean, // v-model
  title: String,
  subTitle: String,
  busy: Boolean,
  size: {
    type: String,
    default: 'md',
    validator(value) {
      return ['sm', 'md', 'lg', 'xl', 'xxl'].includes(value)
    },
  },
  id: {
    type: String,
    default() {
      return randomString()
    },
  },
  okText: String,
  okDisabled: Boolean,
  okLoading: Boolean,
  okVariant: { type: String, default: 'primary' },
  okSize: { type: String, default: 'sm' },

  cancelText: String,
  cancelVariant: { type: String, default: 'white' },
  cancelDisabled: Boolean,
  cancelSize: { type: String, default: 'sm' },

  hideFooter: Boolean,
  hideHeader: Boolean,
  hideHeaderClose: Boolean,
  initialFocus: { type: Object, default: null },
  staticBackdrop: Boolean, // prevent dialog close on esc and backdrop click
}
