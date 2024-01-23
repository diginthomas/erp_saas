<template>
  <div
    :class="['mt-1', totalOptions < 3 ? 'inline-flex space-x-3' : 'space-y-1']"
  >
    <IFormRadio
      v-for="option in rule.options"
      :id="rule.id + '_' + option[rule.valueKey] + '_' + index"
      :key="option[rule.valueKey]"
      v-model="value"
      :value="option[rule.valueKey]"
      :disabled="readOnly"
      :name="rule.id + '_' + option[rule.valueKey]"
      :label="option[rule.labelKey]"
    />
  </div>
</template>

<script setup>
import { computed, toRef } from 'vue'

import propsDefinition from './props'
import { useType } from './useType'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps(propsDefinition)

const value = computed({
  get() {
    return props.query.value
  },
  set(value) {
    updateValue(value)
  },
})

const totalOptions = computed(() => props.rule.options.length)

const { updateValue } = useType(
  toRef(props, 'query'),
  toRef(props, 'operator'),
  props.isNullable
)
</script>
