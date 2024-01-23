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
import IButtonComponent from './IButton.vue'
import IButtonCopyComponent from './IButtonCopy.vue'
import IButtonGroupComponent from './IButtonGroup.vue'
import IButtonIconComponent from './IButtonIcon.vue'
import IButtonMinimalComponent from './IButtonMinimal.vue'

const IButtonPlugin = {
  install(app) {
    app.component('IButton', IButtonComponent)
    app.component('IButtonMinimal', IButtonMinimalComponent)
    app.component('IButtonCopy', IButtonCopyComponent)
    app.component('IButtonIcon', IButtonIconComponent)
    app.component('IButtonGroup', IButtonGroupComponent)
  },
}

// Components
export const IButton = IButtonComponent
export const IButtonCopy = IButtonCopyComponent
export const IButtonGroup = IButtonGroupComponent
export const IButtonIcon = IButtonIconComponent
export const IButtonMinimal = IButtonMinimalComponent

// Plugin
export default IButtonPlugin
