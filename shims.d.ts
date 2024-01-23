import Gate from './modules/Core/resources/js/gate'
import { Axios, AxiosRequestConfig, AxiosResponse } from 'axios';
import { App, Component } from 'vue';
import { Composer } from 'vue-i18n';
import { Router } from 'vue-router'
import { Store } from 'vuex'
export {}

interface VueDialogCustomProperty {
    show: (id: string) => void;
    hide: (id: string) => void;
}

interface VueDraggableCustomProperty {
    common: object;
    scrollable: object
}

interface ConfirmationDialogOptions {
    title: false | string;
    message?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'xxl';
    html?: boolean;
    component?: string | Component;
    confirmText?: string;
    cancelText?: string;
    cancelVariant?: string;
    confirmVariant?: string;
    icon?: string;
    iconColorClass?: string;
    iconWrapperColorClass?: string;
}

declare class Innoclapps {
    booting(callback: (this: Innoclapps, app: App, router: Router, store: Store) => void | Array<(this: Innoclapps, app: App, router: Router, store: Store) => void>): Innoclapps;
    resources(): object[];
    resource(name: string): object;
    resourceName(name: string): string;
    scriptConfig(key: string, value?: any): string | number | boolean | object | null | undefined;
    request(urlOrAxiosConfig: string | AxiosRequestConfig, getRequestConfig?: AxiosRequestConfig): Axios | Promise<AxiosResponse>;
    $on(event: string, callback: Function): void;
    $off(event: string, callback?: Function): void;
    $emit(event: string, params: any): void;
    success(message: string, duration?: number, options?: object): void;
    info(message: string, duration?: number, options?: object): void;
    error(message: string, duration?: number, options?: object): void;
    addShortcut(keys: string, callback: Function): void;
    disableShortcut(keys: string): void;
    confirm(options: string | ConfirmationDialogOptions | Function): Promise<any>;
    dialog(): VueDialogCustomProperty;
    timezones(timezones?: Array): Promise<array>
}

declare global {
    declare const Innoclapps: Innoclapps;

    // function useFetch(urlOrAxiosConfig: string | AxiosRequestConfig, getRequestConfig?: AxiosRequestConfig): Axios | Promise<AxiosResponse>;
    // function requireConfirmation(options: string | ConfirmationDialogOptions | Function): Promise<any>;
    // function useI18n(): Composer;
    // function showAlert(type: 'success' | 'error' | 'info', message: string, duration?: number, options?: object): void;
    // function scriptConfig(key: string, value?: any): string | number | boolean | object | null | undefined;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $store: Store
        $gate: Gate
        $dialog: VueDialogCustomProperty
        $draggable: VueDraggableCustomProperty
        $csrfToken: string
        $confirm: (options: string | ConfirmationDialogOptions | Function) => Promise<any>
        $scriptConfig: (key: string, value?: any) => string | number | boolean | object | null | undefined;
    }
}

declare module '@vue/runtime-dom' {
    export interface GlobalComponents {
        RouterLink: typeof import('vue-router')['RouterLink']
        RouterView: typeof import('vue-router')['RouterView']
        SortableDraggable: typeof import('vuedraggable')['default']

        MainLayout: typeof import('./modules/Core/resources/js/components/MainLayout.vue')['default']

        IButton: typeof import('./modules/Core/resources/js/components/UI/Button/IButton.vue')['default']
        IButtonClose: typeof import('./modules/Core/resources/js/components/UI/Button/IButtonClose.vue')['default']
        IButtonMinimal: typeof import('./modules/Core/resources/js/components/UI/Button/IButtonMinimal.vue')['default']
        IButtonCopy: typeof import('./modules/Core/resources/js/components/UI/Button/IButtonCopy.vue')['default']
        IButtonIcon: typeof import('./modules/Core/resources/js/components/UI/Button/IButtonIcon.vue')['default']
        IButtonGroup: typeof import('./modules/Core/resources/js/components/UI/Button/IButtonGroup.vue')['default']

        IDropdown: typeof import('./modules/Core/resources/js/components/UI/Dropdown/IDropdown.vue')['default']
        IDropdownItem: typeof import('./modules/Core/resources/js/components/UI/Dropdown/IDropdownItem.vue')['default']
        IMinimalDropdown: typeof import('./modules/Core/resources/js/components/UI/Dropdown/IMinimalDropdown.vue')['default']
        IDropdownButton: typeof import('./modules/Core/resources/js/components/UI/Dropdown/IDropdownButton.vue')['default']
        IDropdownButtonGroup: typeof import('./modules/Core/resources/js/components/UI/Dropdown/IDropdownButtonGroup.vue')['default']

        IFormCheckbox: typeof import('./modules/Core/resources/js/components/UI/Form/IFormCheckbox.vue')['default']
        IFormError: typeof import('./modules/Core/resources/js/components/UI/Form/IFormError.vue')['default']
        IFormGroup: typeof import('./modules/Core/resources/js/components/UI/Form/IFormGroup.vue')['default']
        IFormInput: typeof import('./modules/Core/resources/js/components/UI/Form/IFormInput.vue')['default']
        IFormInputDropdown: typeof import('./modules/Core/resources/js/components/UI/Form/IFormInputDropdown.vue')['default']
        IFormLabel: typeof import('./modules/Core/resources/js/components/UI/Form/IFormLabel.vue')['default']
        IFormNumericInput: typeof import('./modules/Core/resources/js/components/UI/Form/IFormNumericInput.vue')['default']
        IFormRadio: typeof import('./modules/Core/resources/js/components/UI/Form/IFormRadio.vue')['default']
        IFormSelect: typeof import('./modules/Core/resources/js/components/UI/Form/IFormSelect.vue')['default']
        IFormText: typeof import('./modules/Core/resources/js/components/UI/Form/IFormText.vue')['default']
        IFormTextarea: typeof import('./modules/Core/resources/js/components/UI/Form/IFormTextarea.vue')['default']
        IFormToggle: typeof import('./modules/Core/resources/js/components/UI/Form/IFormToggle.vue')['default']

        ICustomSelect: typeof import('./modules/Core/resources/js/components/UI/CustomSelect/ISelect.vue')['default']
        IDropdownSelect: typeof import('./modules/Core/resources/js/components/UI/DropdownSelect/IDropdownSelect.vue')['default']
        IDropdownSelectWithBackground: typeof import('./modules/Core/resources/js/components/UI/DropdownSelect/IDropdownSelectWithBackground.vue')['default']

        IConfirmationDialog: typeof import('./modules/Core/resources/js/components/UI/Dialog/IConfirmationDialog.vue')['default']
        IModal: typeof import('./modules/Core/resources/js/components/UI/Dialog/IModal.vue')['default']
        ISlideover: typeof import('./modules/Core/resources/js/components/UI/Dialog/ISlideover.vue')['default']

        DatePicker: typeof import('./modules/Core/resources/js/components/DatePicker/DatePicker.vue')['default']
        DateRangePicker: typeof import('./modules/Core/resources/js/components/DatePicker/DateRangePicker.vue')['default']
        Editor: typeof import('./modules/Core/resources/js/components/Editor/Editor.vue')['default']
        TextCollapse: typeof import('./modules/Core/resources/js/components/TextCollapse.vue')['default']
        TextBackground: typeof import('./modules/Core/resources/js/components/TextBackground.vue')['default']

        ResourceTable: typeof import('./modules/Core/resources/js/components/ResourceTable/ResourceTable.vue')['default']
        TableRowAction: typeof import('./modules/Core/resources/js/components/Table/TableRowAction.vue')['default']
        TableRowActions: typeof import('./modules/Core/resources/js/components/Table/TableRowActions.vue')['default']

        Icon: typeof import('./modules/Core/resources/js/components/UI/Icon.vue')['default']
        IOverlay: typeof import('./modules/Core/resources/js/components/UI/IOverlay.vue')['default']
        IPopover: typeof import('./modules/Core/resources/js/components/UI/IPopover.vue')['default']
        ISpinner: typeof import('./modules/Core/resources/js/components/UI/ISpinner.vue')['default']
        ITable: typeof import('./modules/Core/resources/js/components/UI/ITable.vue')['default']
        IColorSwatch: typeof import('./modules/Core/resources/js/components/UI/IColorSwatch.vue')['default']
        IAlert: typeof import('./modules/Core/resources/js/components/UI/Alert/IAlert.vue')['default']
        IAlertActions: typeof import('./modules/Core/resources/js/components/UI/Alert/IAlertActions.vue')['default']
        IBadge: typeof import('./modules/Core/resources/js/components/UI/IBadge.vue')['default']
        IAvatar: typeof import('./modules/Core/resources/js/components/UI/IAvatar.vue')['default']
        IActionMessage: typeof import('./modules/Core/resources/js/components/UI/IActionMessage.vue')['default']
        IEmptyState: typeof import('./modules/Core/resources/js/components/UI/IEmptyState.vue')['default']
        IIconPicker: typeof import('./modules/Core/resources/js/components/UI/IIconPicker.vue')['default']
        IStepCircle: typeof import('./modules/Core/resources/js/components/UI/IStepCircle.vue')['default']
        IStepsCircle: typeof import('./modules/Core/resources/js/components/UI/IStepsCircle.vue')['default']

        ITab: typeof import('./modules/Core/resources/js/components/UI/Tab/ITab.vue')['default']
        ITabGroup: typeof import('./modules/Core/resources/js/components/UI/Tab/ITabGroup.vue')['default']
        ITabList: typeof import('./modules/Core/resources/js/components/UI/Tab/ITabList.vue')['default']
        ITabPanel: typeof import('./modules/Core/resources/js/components/UI/Tab/ITabPanel.vue')['default']
        ITabPanels: typeof import('./modules/Core/resources/js/components/UI/Tab/ITabPanels.vue')['default']

        ICard: typeof import('./modules/Core/resources/js/components/UI/Card/ICard.vue')['default']
        ICardBody: typeof import('./modules/Core/resources/js/components/UI/Card/ICardBody.vue')['default']
        ICardFooter: typeof import('./modules/Core/resources/js/components/UI/Card/ICardFooter.vue')['default']
        ICardHeading: typeof import('./modules/Core/resources/js/components/UI/Card/ICardHeading.vue')['default']

        IVerticalNavigation: typeof import('./modules/Core/resources/js/components/UI/VerticalNavigation/IVerticalNavigation.vue')['default']
        IVerticalNavigationItem: typeof import('./modules/Core/resources/js/components/UI/VerticalNavigation/IVerticalNavigationItem.vue')['default']

        ILink: typeof import('./modules/Core/resources/js/components/UI/ILink.vue')['default']

        FormFields: typeof import('./modules/Core/resources/js/fields/FormFields.vue')['default']
        DetailFields: typeof import('./modules/Core/resources/js/fields/DetailFields.vue')['default']
        DetailFieldItem: typeof import('./modules/Core/resources/js/fields/DetailFieldItem.vue')['default']
        IndexFieldItem: typeof import('./modules/Core/resources/js/fields/IndexFieldItem.vue')['default']
        FieldsCollapseButton: typeof import('./modules/Core/resources/js/fields/FieldsCollapseButton.vue')['default']
        FieldInlineEdit: typeof import('./modules/Core/resources/js/fields/FieldInlineEdit.vue')['default']
        BaseFormField: typeof import('./modules/Core/resources/js/fields/BaseFormField.vue')['default']
        BaseSelectField: typeof import('./modules/Core/resources/js/fields/BaseSelectField.vue')['default']
        FieldsPlaceholder: typeof import('./modules/Core/resources/js/fields/FieldsPlaceholder.vue')['default']
    }
}
