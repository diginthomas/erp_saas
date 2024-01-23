<template>
  <BaseSelectField
    v-slot="{
      isReadonly,
      fieldId,
      lazyLoadingOptions,
      filterable,
      onSearch,
      onDropdownOpen,
      noOptionsText,
      headerText,
      selectValue,
      handleSelectInputChange,
    }"
    :field="field"
    :value="modelValue"
    :options="stages"
    :is-floating="isFloating"
  >
    <FormFieldGroup
      :field="field"
      :label="field.label"
      :field-id="fieldId"
      :validation-errors="validationErrors"
    >
      <IAlert
        v-if="!hasStages"
        class="mb-2"
        :text="$t('deals::deal.pipeline.missing_stages')"
      />

      <ICustomSelect
        :model-value="selectValue"
        :class="!hasStages ? 'hidden' : ''"
        :input-id="fieldId"
        :disabled="isReadonly"
        :filterable="filterable"
        :options="stages"
        :loading="lazyLoadingOptions"
        :name="field.attribute"
        :label="field.labelKey"
        v-bind="field.attributes"
        @search="onSearch"
        @open="onDropdownOpen"
        @update:model-value="
          updateModelValue($event), handleSelectInputChange($event)
        "
      >
        <template #no-options>{{ noOptionsText }}</template>

        <template #header>
          <span
            v-show="headerText"
            class="block px-3 py-2 text-sm text-neutral-500 dark:text-neutral-200"
            v-text="headerText"
          />
        </template>
      </ICustomSelect>
    </FormFieldGroup>
  </BaseSelectField>
</template>

<script setup>
import { nextTick } from 'vue'
import { computed, ref } from 'vue'

import { useGlobalEventListener } from '@/Core/composables/useGlobalEventListener'
import FormFieldGroup from '@/Core/fields/FormFieldGroup.vue'

const props = defineProps({
  field: { type: Object, required: true },
  modelValue: {},
  resourceName: String,
  resourceId: [String, Number],
  validationErrors: Object,
  isFloating: Boolean,
})

const emit = defineEmits(['update:modelValue', 'setInitialValue'])

const selectedPipelineId = ref(props.field.value?.pipeline_id || null)

const stages = computed(() => getOnlyRelatedStages(selectedPipelineId.value))

const hasStages = computed(() => stages.value.length > 0)

function getOnlyRelatedStages(pipelineId) {
  return props.field.options.filter(
    stage => stage[props.field.dependsOn] == pipelineId
  )
}

async function pipelineIdValueChangedHandler(pipelineId) {
  selectedPipelineId.value = pipelineId

  // Set the first stage as selected by default
  nextTick(() => updateModelValue(stages.value[0]))
}

function updateModelValue(value) {
  emit('update:modelValue', (value && value[props.field.valueKey]) || null)
}

function setInitialValue() {
  emit('setInitialValue', props.field.value?.id)
}

useGlobalEventListener(
  'field-pipeline_id-value-changed',
  pipelineIdValueChangedHandler
)

setInitialValue()
</script>
