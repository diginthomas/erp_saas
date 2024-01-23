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
    >
      <div class="relative">
        <div
          class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
        >
          <Icon
            icon="Globe"
            class="size-5 text-neutral-500 dark:text-neutral-300"
          />
        </div>

        <IFormInput
          :id="fieldId"
          v-model="model"
          :name="field.attribute"
          :disabled="isReadonly"
          v-bind="field.attributes"
          class="pl-11"
          @blur="parseDomain"
        />
      </div>
    </FormFieldGroup>
  </BaseFormField>
</template>

<script setup>
import parse_url from 'locutus/php/url/parse_url'
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

function parseDomain() {
  let value = model.value

  if (value && !value.startsWith('http')) {
    value = `https://${value}`
  }

  model.value = parse_url(value).host || null
}

function setInitialValue() {
  emit('setInitialValue', !isNil(props.field.value) ? props.field.value : '')
}

setInitialValue()
</script>
