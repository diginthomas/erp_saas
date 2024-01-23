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
import IDropdownComponent from './IDropdown.vue'
import IDropdownButtonGroupComponent from './IDropdownButtonGroup.vue'
import IDropdownItemComponent from './IDropdownItem.vue'
import IMinimalDropdownComponent from './IMinimalDropdown.vue'

const IDropdownPlugin = {
  install(app) {
    app.component('IDropdown', IDropdownComponent)
    app.component('IDropdownItem', IDropdownItemComponent)
    app.component('IMinimalDropdown', IMinimalDropdownComponent)
    app.component('IDropdownButtonGroup', IDropdownButtonGroupComponent)
  },
}

// Components
export const IDropdown = IDropdownComponent
export const IDropdownItem = IDropdownItemComponent
export const IDropdownButtonGroup = IDropdownButtonGroupComponent
export const IMinimalDropdown = IMinimalDropdownComponent

// Plugin
export default IDropdownPlugin
