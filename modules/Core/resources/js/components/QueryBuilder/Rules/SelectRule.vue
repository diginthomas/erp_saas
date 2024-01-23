<template>
  <ICustomSelect
    v-model="selectValue"
    size="sm"
    :multiple="isMultiSelect"
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
import find from 'lodash/find'
import isEqual from 'lodash/isEqual'

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

const isMultiSelect = computed(() => {
  return props.rule.type === 'multi-select'
})

const placeholder = computed(() => {
  return t('core::filters.placeholders.choose', {
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
  let value = null

  if (option && !isBlank(option[props.rule.valueKey])) {
    // Allows zero in the value
    value = option[props.rule.valueKey]
  }

  updateSelectValue(value)
}

/**
 * Prepare component
 */
function prepareComponent() {
  // First option selected by default
  if (isBlank(props.query.value)) {
    updateSelectValue(
      options.value[0] ? options.value[0][props.rule.valueKey] : null
    )
  } else {
    setInitialValue()
  }
}

/**
 * Set the select initial internal value
 */
function setInitialValue() {
  let value = find(options.value, [props.rule.valueKey, props.query.value])

  updateSelectValue(value ? value[props.rule.valueKey] : null)
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
    selectValue.value = null

    return
  }

  selectValue.value = find(options.value, [props.rule.valueKey, value]) || null
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
