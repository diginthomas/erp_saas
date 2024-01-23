<template>
  <div v-if="totalOptions > 0" :class="eachOnNewLine ? 'space-y-0.5' : ''">
    <component
      :is="onClickRedirectTo ? ILink : 'span'"
      v-for="(option, index) in options"
      :key="index"
      :class="[
        !eachOnNewLine ? 'mr-1.5 last:mr-0' : '',
        eachOnNewLine ? 'block' : '',
      ]"
      :plain="onClickRedirectTo && !displayAsPills ? false : undefined"
      :to="
        onClickRedirectTo
          ? onClickRedirectTo.replace('{id}', option.id)
          : undefined
      "
    >
      <component
        :is="option.swatch_color && displayAsPills ? TextBackground : 'span'"
        :color="
          option.swatch_color && displayAsPills
            ? option.swatch_color
            : undefined
        "
        :class="[
          'inline-flex items-center justify-center space-x-1.5',
          { 'rounded-md px-2.5 py-px text-sm/5 font-normal': displayAsPills },
          !option.swatch_color && displayAsPills
            ? 'bg-neutral-200 dark:bg-neutral-600'
            : option.swatch_color && displayAsPills
              ? 'dark:!text-white '
              : '',
        ]"
      >
        <Icon
          v-if="option.icon"
          :icon="option.icon"
          class="size-4 shrink-0 text-current"
        />

        <span>
          {{ getOptionLabel(option) }}
        </span>
      </component>
    </component>
  </div>

  <span v-else>&mdash;</span>
</template>

<script setup>
import { computed } from 'vue'
import castArray from 'lodash/castArray'

import TextBackground from '../components/TextBackground.vue'
import ILink from '../components/UI/ILink.vue'

const props = defineProps([
  'value',
  'displayAsPills',
  'eachOnNewLine',
  'onClickRedirectTo',
  'list',
  'labelKey',
  'valueKey',
])

const options = computed(() => (props.value ? castArray(props.value) : []))
const totalOptions = computed(() => options.value.length)

function getOptionLabel(option) {
  if (typeof option === 'string') {
    if (!props.list) {
      return option
    }

    let selectedOptionObject = props.list.find(
      o => o[props.valueKey] === option
    )

    return selectedOptionObject ? selectedOptionObject[props.labelKey] : option
  }

  return option[props.labelKey]
}
</script>
