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
      <IFormInput
        :id="fieldId"
        v-model="model"
        :disabled="isReadonly"
        :name="field.attribute"
        :type="field.inputType || 'text'"
        v-bind="field.attributes"
        @input="searchDuplicateResource"
      />

      <IAlert
        v-if="duplicateResource"
        v-slot="{ variant }"
        dismissible
        class="mt-2"
        @dismissed="duplicateResource = null"
      >
        <I18nT scope="global" :keypath="field.checkDuplicatesWith.lang_keypath">
          <template #display_name>
            <span class="font-medium">
              {{ duplicateResource.display_name }}
            </span>
          </template>
        </I18nT>

        <IAlertActions>
          <IButtonMinimal
            tag="a"
            :href="duplicateResource.path"
            rel="noopener noreferrer"
            target="_blank"
            :variant="variant"
            icon="ExternalLink"
            :text="$t('core::app.view_record')"
          />
        </IAlertActions>
      </IAlert>
    </FormFieldGroup>
  </BaseFormField>
</template>

<script setup>
import { computed, shallowRef } from 'vue'
import debounce from 'lodash/debounce'
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

const duplicateResource = shallowRef(null)

function setInitialValue() {
  emit('setInitialValue', !isNil(props.field.value) ? props.field.value : '')
}

const searchDuplicateResource = debounce(() => {
  if (!checksForDuplicates.value || props.resourceId || !model.value) {
    duplicateResource.value = null

    return
  }

  makeDuplicateCheckRequest(model.value).then(
    duplicate => (duplicateResource.value = duplicate)
  )
}, 700)

const checksForDuplicates = computed(
  () =>
    props.field.checkDuplicatesWith &&
    Object.keys(props.field.checkDuplicatesWith).length > 0
)

async function makeDuplicateCheckRequest(query) {
  const { data } = await Innoclapps.request(
    props.field.checkDuplicatesWith.url,
    {
      params: {
        q: query,
        ...props.field.checkDuplicatesWith.params,
      },
    }
  )

  return data.length > 0 ? data[0] : null
}

setInitialValue()
</script>
