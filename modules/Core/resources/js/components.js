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
import { defineAsyncComponent } from 'vue'
import FileUpload from 'vue-upload-component'
import Notifications from 'notiwind'

import { translate } from '@/Core/i18n'

import AsyncComponentLoader from './components/AsyncComponentLoader.vue'

const SortableDraggable = defineAsyncComponent({
  loader: () => import('vuedraggable'),
  loadingComponent: AsyncComponentLoader,
})

const TextCollapse = defineAsyncComponent({
  loader: () => import('./components/TextCollapse.vue'),
  loadingComponent: AsyncComponentLoader,
})

const CropsAndUploadsImage = defineAsyncComponent({
  loader: () => import('./components/CropsAndUploadsImage.vue'),
  loadingComponent: AsyncComponentLoader,
})

import AuthLogin from '../../../../resources/js/views/Auth/AuthLogin.vue'
import AuthPasswordEmail from '../../../../resources/js/views/Auth/AuthPasswordEmail.vue'
import AuthPasswordReset from '../../../../resources/js/views/Auth/AuthPasswordReset.vue'

import ActionDialog from './components/Actions/ActionDialog.vue'
import ActionBulkEdit from './components/Actions/ActionDialogBulkEdit.vue'
import CardAsyncTable from './components/Cards/CardAsyncTable.vue'
import CardTable from './components/Cards/CardTable.vue'
import PresentationChart from './components/Charts/PresentationChart.vue'
import ProgressionChart from './components/Charts/ProgressionChart.vue'
import DatePicker from './components/DatePicker/DatePicker.vue'
import DateRangePicker from './components/DatePicker/DateRangePicker.vue'
import Editor from './components/Editor'
import MainLayout from './components/MainLayout.vue'
import NavbarItems from './components/NavbarItems.vue'
import NavbarSeparator from './components/NavbarSeparator.vue'
import ResourceTable from './components/ResourceTable/ResourceTable.vue'
import SearchInput from './components/SearchInput.vue'
import TableRowAction from './components/Table/TableRowAction.vue'
import TableRowActions from './components/Table/TableRowActions.vue'
import TextBackground from './components/TextBackground.vue'
import TheFloatingResourceModal from './components/TheFloatingResourceModal.vue'
import TheFloatNotifications from './components/TheFloatNotifications.vue'
import TheNavbar from './components/TheNavbar.vue'
import TheSidebar from './components/TheSidebar.vue'
import IAlert from './components/UI/Alert/IAlert.vue'
import IAlertActions from './components/UI/Alert/IAlertActions.vue'
import IButtonPlugin from './components/UI/Button'
import ICardPlugin from './components/UI/Card'
import ICustomSelect from './components/UI/CustomSelect'
import IDialogPlugin from './components/UI/Dialog'
import IDropdownPlugin from './components/UI/Dropdown'
import IDropdownSelect from './components/UI/DropdownSelect/IDropdownSelect.vue'
import IDropdownSelectWithBackground from './components/UI/DropdownSelect/IDropdownSelectWithBackground.vue'
import IFormPlugin from './components/UI/Form'
import IActionMessage from './components/UI/IActionMessage.vue'
import IAvatar from './components/UI/IAvatar.vue'
import IBadge from './components/UI/IBadge.vue'
import IColorSwatch from './components/UI/IColorSwatch.vue'
import Icon from './components/UI/Icon.vue'
import IEmptyState from './components/UI/IEmptyState.vue'
import IIconPicker from './components/UI/IIconPicker.vue'
import ILink from './components/UI/ILink.vue'
import IOverlay from './components/UI/IOverlay.vue'
import IPopover from './components/UI/IPopover.vue'
import ISpinner from './components/UI/ISpinner.vue'
import ITable from './components/UI/ITable.vue'
import IStepCircle from './components/UI/Step/IStepCircle.vue'
import IStepsCircle from './components/UI/Step/IStepsCircle.vue'
import ITabsPlugin from './components/UI/Tab'
import ITooltipPlugin from './components/UI/Tooltip'
import IVerticalNavigation from './components/UI/VerticalNavigation/IVerticalNavigation.vue'
import IVerticalNavigationItem from './components/UI/VerticalNavigation/IVerticalNavigationItem.vue'
import BaseFormField from './fields/BaseFormField.vue'
import BaseSelectField from './fields/BaseSelectField.vue'
import DetailFieldItem from './fields/DetailFieldItem.vue'
import DetailFields from './fields/DetailFields.vue'
import FieldInlineEdit from './fields/FieldInlineEdit.vue'
import FieldsCollapseButton from './fields/FieldsButtonCollapse.vue'
import FieldsPlaceholder from './fields/FieldsPlaceholder.vue'
import FormFields from './fields/FormFields.vue'
import IndexFieldItem from './fields/IndexFieldItem.vue'
import ActionPanel from './views/ActionPanel.vue'

export default function (app) {
  app
    .use(Notifications)
    .use(IButtonPlugin)
    .use(ICardPlugin)
    .use(IDropdownPlugin)
    .use(IFormPlugin)
    .use(ITabsPlugin)
    .use(ITooltipPlugin)
    .use(IDialogPlugin, {
      dialog: {
        labels: {
          cancelText: translate('core::app.cancel'),
          okText: 'Ok',
        },
      },
      confirmation: {
        labels: {
          title: translate('core::actions.confirmation_message'),
          confirmText: translate('core::app.confirm'),
          cancelText: translate('core::app.cancel'),
        },
      },
    })

  app
    .component('SortableDraggable', SortableDraggable)
    .component('FileUpload', FileUpload)

    .component('AuthLogin', AuthLogin)
    .component('AuthPasswordEmail', AuthPasswordEmail)
    .component('AuthPasswordReset', AuthPasswordReset)

    .component('IActionMessage', IActionMessage)
    .component('IAvatar', IAvatar)
    .component('ITable', ITable)
    .component('ICustomSelect', ICustomSelect)
    .component('IOverlay', IOverlay)
    .component('IPopover', IPopover)
    .component('IEmptyState', IEmptyState)
    .component('IIconPicker', IIconPicker)
    .component('ISpinner', ISpinner)
    .component('IStepsCircle', IStepsCircle)
    .component('IStepCircle', IStepCircle)
    .component('IColorSwatch', IColorSwatch)
    .component('IVerticalNavigation', IVerticalNavigation)
    .component('IVerticalNavigationItem', IVerticalNavigationItem)
    .component('ILink', ILink)
    .component('IDropdownSelect', IDropdownSelect)
    .component('IDropdownSelectWithBackground', IDropdownSelectWithBackground)
    .component('IAlert', IAlert)
    .component('IAlertActions', IAlertActions)
    .component('IBadge', IBadge)

    .component('MainLayout', MainLayout)
    .component('TheNavbar', TheNavbar)
    .component('NavbarItems', NavbarItems)
    .component('NavbarSeparator', NavbarSeparator)
    .component('TheSidebar', TheSidebar)

    .component('TheFloatNotifications', TheFloatNotifications)
    .component('TheFloatingResourceModal', TheFloatingResourceModal)

    .component('DatePicker', DatePicker)
    .component('DateRangePicker', DateRangePicker)

    .component('ActionPanel', ActionPanel)
    .component('TextBackground', TextBackground)

    .component('Icon', Icon)

    .component('ActionDialog', ActionDialog)
    .component('ActionBulkEdit', ActionBulkEdit)

    .component('ProgressionChart', ProgressionChart)
    .component('PresentationChart', PresentationChart)

    .component('CardTable', CardTable)
    .component('CardAsyncTable', CardAsyncTable)

    .component('ResourceTable', ResourceTable)
    .component('TableRowAction', TableRowAction)
    .component('TableRowActions', TableRowActions)

    .component('CropsAndUploadsImage', CropsAndUploadsImage)
    .component('TextCollapse', TextCollapse)

    .component('Editor', Editor)

    .component('BaseFormField', BaseFormField)
    .component('BaseSelectField', BaseSelectField)
    .component('FormFields', FormFields)
    .component('DetailFields', DetailFields)
    .component('DetailFieldItem', DetailFieldItem)
    .component('IndexFieldItem', IndexFieldItem)
    .component('FieldsCollapseButton', FieldsCollapseButton)
    .component('FieldsPlaceholder', FieldsPlaceholder)
    .component('FieldInlineEdit', FieldInlineEdit)

    .component('SearchInput', SearchInput)
}
