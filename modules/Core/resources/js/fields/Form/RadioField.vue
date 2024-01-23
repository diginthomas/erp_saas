<template>
  <BaseFormField
    v-slot="{ isReadonly, fieldId }"
    :resource-name="resourceName"
    :field="field"
    :value="model"
    :is-floating="isFloating"
  >
    <FormFieldGroup
      :field="field"
      :label="field.label"
      :field-id="fieldId"
      :validation-errors="validationErrors"
      as-paragraph-label
    >
      <div
        :class="{
          'flex items-center space-x-2': field.inline,
          'space-y-1': !field.inline,
        }"
      >
        <IFormRadio
          v-for="option in field.options"
          :id="'radio-' + option[field.valueKey]"
          :key="option[field.valueKey]"
          v-bind="field.attributes"
          v-model="model"
          :name="field.attribute"
          :value="option[field.valueKey]"
          :disabled="isReadonly"
          :swatch-color="option.swatch_color"
          :label="option[field.labelKey]"
        />
      </div>
    </FormFieldGroup>
  </BaseFormField>
</template>

<script setup>
import isNil from 'lodash/isNil'

import FormFieldGroup from '../FormFieldGroup.vue'

const props = defineProps({
  field: { type: Object, required: true },
  resourceName: String,
  resourceId: [String, Number],
  validationErrors: Object,
  isFloating: Boolean,
})

const emit = defineEmits(['setInitialValue'])

const model = defineModel()

function setInitialValue() {
  emit(
    'setInitialValue',
    (function () {
      if (!isNil(props.field.value)) {
        if (typeof props.field.value === 'object') {
          return props.field.value[props.field.valueKey]
        } else {
          return props.field.value
        }
      }

      return ''
    })()
  )
}

setInitialValue()
</script>
