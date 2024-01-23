<template>
  <IFormGroup
    :label="$t('core::fields.custom.type')"
    label-for="field_type"
    required
  >
    <ICustomSelect
      :model-value="fieldType"
      :options="fieldsTypes"
      :clearable="false"
      :disabled="inEditMode"
      @update:model-value="$emit('update:fieldType', $event)"
      @option-selected="!label ? $refs.labelRef.focus() : ''"
    />

    <IFormError :error="form.getError('field_type')" />
  </IFormGroup>

  <IFormGroup label-for="label" :label="$t('core::fields.label')" required>
    <IFormInput
      id="label"
      ref="labelRef"
      :model-value="label"
      @update:model-value="$emit('update:label', $event)"
    />

    <IFormError :error="form.getError('label')" />
  </IFormGroup>

  <FieldOptions
    v-if="isOptionableField"
    :options="options"
    :form="form"
    @update:options="$emit('update:options', $event)"
  />

  <IFormGroup :description="$t('core::fields.custom.id_info')">
    <template #label>
      <div class="flex">
        <IFormLabel
          required
          class="mb-1 grow"
          for="field_id"
          :label="$t('core::fields.custom.id')"
        />

        <IButtonCopy
          v-show="fieldId"
          :text="fieldId"
          :success-message="$t('core::app.copied')"
          tabindex="-1"
          class="link link-primary cursor-pointer"
          tag="a"
        >
          {{ $t('core::app.copy') }}
        </IButtonCopy>
      </div>
    </template>

    <div class="relative">
      <div
        v-if="!inEditMode"
        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm dark:text-white"
      >
        {{ idPrefix }}
      </div>

      <IFormInput
        id="field_id"
        v-model="localFieldId"
        :disabled="inEditMode"
        :class="{ 'pl-8': !inEditMode }"
      />
    </div>

    <IFormError :error="form.getError('field_id')" />
  </IFormGroup>

  <IFormGroup v-if="isUniqueable">
    <IFormCheckbox
      :checked="isUnique"
      :disabled="inEditMode && !isUnique && !isOriginallyUnique"
      :label="$t('core::fields.mark_as_unique')"
      @update:checked="$emit('update:isUnique', $event)"
    />

    <p
      v-if="inEditMode && isUnique === false && isOriginallyUnique"
      v-t="'core::fields.unmark_as_unique_change_info'"
      class="mt-4 text-sm text-danger-600 dark:text-danger-500"
    />

    <IFormText
      v-else-if="!inEditMode || (inEditMode && !isUnique)"
      :text="$t('core::fields.mark_as_unique_change_info')"
      class="mt-1"
    />

    <IFormError :error="form.getError('is_unique')" />
  </IFormGroup>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { watchDebounced } from '@vueuse/core'
import find from 'lodash/find'
import map from 'lodash/map'

import { useApp } from '@/Core/composables/useApp'

import FieldOptions from './CustomFieldsFormOptions.vue'

const props = defineProps({
  form: { required: true, type: Object },
  fieldType: { required: true, type: String },
  label: { required: true, type: String },
  fieldId: { required: true, type: String },
  resourceName: { required: true, type: String },
  isUnique: { required: true },
  options: { required: true },
  inEditMode: Boolean,
})

const emit = defineEmits([
  'update:fieldType',
  'update:label',
  'update:fieldId',
  'update:isUnique',
  'update:options',
])

const { scriptConfig } = useApp()

const resources = Innoclapps.resources()
const customFields = scriptConfig('fields.custom_fields')
const idPrefix = scriptConfig('fields.custom_field_prefix')

const localFieldId = ref(props.fieldId || null)
const isOriginallyUnique = props.isUnique

const fieldsTypes = computed(() => map(customFields, 'type'))

const isOptionableField = computed(() =>
  Boolean(
    find(customFields, {
      type: props.fieldType,
      optionable: true,
    })
  )
)

const isUniqueable = computed(() => {
  return (
    Boolean(
      find(customFields, {
        type: props.fieldType,
        uniqueable: true,
      })
    ) &&
    Boolean(
      find(resources, {
        acceptsUniqueCustomFields: true,
        name: props.resourceName,
      })
    )
  )
})

watch(isUniqueable, newVal => {
  emit(
    'update:isUnique',
    newVal ? (props.isUnique !== null ? props.isUnique : false) : null
  )
})

watchDebounced(
  localFieldId,
  newVal => {
    if (!props.inEditMode) {
      emit('update:fieldId', newVal ? `${idPrefix}${newVal}` : null)
    }
  },
  { debounce: 250 }
)

watch(isOptionableField, (newVal, oldVal) => {
  if (!newVal && oldVal) {
    emit('update:options', null)
  } else if (newVal) {
    emit('update:options', [])
  }
})
</script>
