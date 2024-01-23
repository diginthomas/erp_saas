<template>
  <IPopover
    v-if="canBeInlineEdited"
    ref="popoverRef"
    :class="widthClass"
    :title="$t('core::fields.update_field')"
    :disabled="Boolean(editAction)"
    :portal="false"
    shift
    flip
    adaptive-width
    @show="handlePopoverShowEvent"
    @hide="handlePopoverHideEvent"
  >
    <IButton
      size="xs"
      variant="white"
      :class="[
        'absolute right-2 z-20 my-auto -mt-0.5 mr-4 ',
        { '!block': popoverVisible },
      ]"
      v-bind="$attrs"
      icon="Pencil"
      @click="editAction"
    />

    <template #popper>
      <form @submit.prevent="save">
        <div class="p-4">
          <IOverlay :show="!hasFields">
            <FormFields
              v-if="inlineEditReady"
              :fields="fields"
              :form="form"
              :resource-name="resourceName"
              :resource-id="resourceId"
              :is-floating="isFloating"
              :collapsed="false"
              @update-field-value="form.fill($event.attribute, $event.value)"
              @set-initial-value="form.set($event.attribute, $event.value)"
            />

            <slot
              name="after-inline-edit-form-fields"
              :hide-popover="hidePopover"
            />
          </IOverlay>
        </div>

        <div
          class="border-t border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800"
        >
          <div class="flex items-center justify-end space-x-1 px-4 py-2.5">
            <IButton
              variant="white"
              :text="$t('core::app.cancel')"
              :disabled="!inlineEditReady"
              @click="cancel"
            />

            <IButton
              :text="$t('core::app.save')"
              :disabled="form.busy || !inlineEditReady"
              :loading="form.busy"
              @click="save"
            />
          </div>
        </div>
      </form>
    </template>
  </IPopover>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useTimeoutFn } from '@vueuse/core'
import castArray from 'lodash/castArray'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'
import isFunction from 'lodash/isFunction'

import { CancelToken } from '@/Core/services/HTTP'

import { useForm } from '../composables/useForm'
import { useResourceable } from '../composables/useResourceable'
import { useResourceFields } from '../composables/useResourceFields'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps([
  'field',
  'fieldFetcher',
  'resource',
  'resourceName',
  'resourceId',
  'editAction',
  'isFloating',
  'width',
])

const emit = defineEmits(['updated'])

const popoverRef = ref(null)
const inlineEditReady = ref(false)
const popoverVisible = ref(false)
let saveCancelToken = null

const inlineEditField = ref({})

const { form } = useForm()
const { updateResource } = useResourceable(props.resourceName)
const { fields, hasFields, hydrateFields } = useResourceFields()

const widthClass = computed(() => {
  switch (props.field.inlineEditPanelWidth) {
    case 'medium':
      return 'w-screen sm:w-80'
    case 'large':
      return 'w-screen sm:w-96'
    default:
      return ''
  }
})

function hidePopover() {
  popoverRef.value.hide()
}

function cancel() {
  if (saveCancelToken) {
    saveCancelToken()
    saveCancelToken = null
  }

  hidePopover()
}

async function getFieldsForInlineEdit() {
  let field = await new Promise(resolve => {
    return isFunction(props.fieldFetcher)
      ? resolve(props.fieldFetcher())
      : resolve(props.field)
  })

  inlineEditField.value = field

  if (field.inlineEditWith !== null) {
    field = field.inlineEditWith
  }

  return field
}

async function handlePopoverShowEvent() {
  popoverVisible.value = true
  let availableFields = cloneDeep(castArray(await getFieldsForInlineEdit()))
  availableFields.forEach(f => (f.width = 'full'))

  fields.value = availableFields
  hydrateFields(props.resource)

  inlineEditReady.value = true
}

function handlePopoverHideEvent() {
  form.errors.clear()
  inlineEditReady.value = false
  fields.value = []

  useTimeoutFn(() => {
    popoverVisible.value = false
  }, 300)
}

async function save() {
  const updatedResource = await updateResource(form, props.resourceId, {
    cancelToken: new CancelToken(token => (saveCancelToken = token)),
  })

  hidePopover()
  emit('updated', updatedResource)
}

const canBeInlineEdited = computed(() => {
  return (
    props.resource.authorizations.update &&
    props.field.inlineEditDisabled !== true &&
    !get(props.resource, `_edit_disabled.${props.field.attribute}`) &&
    (props.field.inlineEditWith !== null || props.field.applicableForUpdate)
  )
})
</script>
