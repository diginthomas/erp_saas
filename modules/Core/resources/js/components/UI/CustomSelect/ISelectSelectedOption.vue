<template>
  <component
    :is="
      typeof option === 'object' && option.swatch_color
        ? TextBackground
        : 'span'
    "
    v-bind="attributes"
  >
    <slot :label="label">
      {{ label }}
    </slot>

    <DeselectButton
      v-if="multiple && !disabled"
      :deselect="deselect"
      :label="label"
      :option="option"
    />
  </component>
</template>

<script setup>
import { computed } from 'vue'

import TextBackground from '@/Core/components/TextBackground.vue'

import DeselectButton from './ISelectSelectedOptionDeselectButton.vue'

const props = defineProps([
  'option',
  'label',
  'multiple',
  'searching',
  'disabled',
  'deselect',
  'simple',
])

const attributes = computed(() => {
  let attributes = {
    class: 'dark:!text-white',
  }

  if (props.option.swatch_color) {
    attributes.color = props.option.swatch_color
  }

  if (props.multiple || props.option.swatch_color) {
    attributes.class +=
      ' inline-flex items-center justify-center rounded-md px-2.5 text-sm/5 font-normal dark:!text-white'

    if (!props.option.swatch_color) {
      attributes.class += ' bg-neutral-100 dark:bg-neutral-500'
    }

    if (props.multiple) {
      attributes.class += ' mr-2'
    }
  }

  if ((props.disabled && !props.simple) || props.searching) {
    attributes.class += ' opacity-60 dark:opacity-80'
  }

  return attributes
})
</script>
