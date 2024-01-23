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
      :field-id="fieldId"
      :validation-errors="validationErrors"
      as-paragraph-label
    >
      <IFormCheckbox
        :id="fieldId"
        v-model:checked="model"
        :disabled="isReadonly"
        :name="field.attribute"
        :value="field.trueValue"
        v-bind="field.attributes"
        :unchecked-value="field.falseValue"
      >
        <!-- eslint-disable-next-line vue/no-v-html -->
        <span v-html="field.label"></span>
      </IFormCheckbox>
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
    !isNil(props.field.value) ? props.field.value : props.field.falseValue
  )
}

setInitialValue()
</script>
