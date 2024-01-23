<template>
  <ICustomSelect
    v-model="selectValue"
    size="sm"
    :multiple="true"
    :disabled="readOnly"
    :input-id="'rule-' + rule.id + '-' + index"
    :placeholder="placeholder"
    :label="rule.labelKey"
    :option-key="rule.valueKey"
    :options="options"
  />
</template>

<script setup>
import { computed, ref, toRef, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import isEqual from 'lodash/isEqual'
import map from 'lodash/map'

import { isBlank } from '@/Core/utils'

import propsDefinition from './props'
import { useType } from './useType'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps(propsDefinition)

const { t } = useI18n()

const { updateValue } = useType(
  toRef(props, 'query'),
  toRef(props, 'operator'),
  props.isNullable
)

const selectValue = ref(null)

const options = computed(() => props.rule.options)

const placeholder = computed(() => {
  return t('core::filters.placeholders.choose_with_multiple', {
    label: props.operand ? props.operand.label : props.rule.label,
  })
})

/**
 * Watch the value for change and update actual query value
 */
watch(selectValue, handleChange, { deep: true })

/**
 * Handle change for select to update the value
 *
 * @param  {String|Array|Number|null} value
 *
 * @return {Void}
 */
function handleChange(option) {
  if (isBlank(option)) {
    updateSelectValue([])
  } else {
    updateSelectValue(map(option, props.rule.valueKey))
  }
}

/**
 * Prepare component
 */
function prepareComponent() {
  if (!isBlank(props.query.value)) {
    setInitialValue()
  }
}

/**
 * Set the select initial internal value
 */
function setInitialValue() {
  if (isBlank(props.query.value)) {
    updateSelectValue([])
  } else {
    updateSelectValue(props.query.value)
  }
}

/**
 * Set the select value from the given query builder value
 *
 * @param  {String|Array|Number|null} value
 *
 * @return {Void}
 */
function setSelectValue(value) {
  if (isBlank(value)) {
    selectValue.value = []

    return
  }

  value =
    options.value.filter(
      option => value.indexOf(option[props.rule.valueKey]) > -1
    ) || []

  if (!isEqual(value, selectValue.value)) {
    selectValue.value = value
  }
}

/**
 * Update the current rule query value
 *
 * @param  {String|Array|Number|null} value
 */
function updateSelectValue(value) {
  updateValue(value)

  if (!isEqual(value, selectValue.value)) {
    setSelectValue(value)
  }
}

prepareComponent()
</script>
