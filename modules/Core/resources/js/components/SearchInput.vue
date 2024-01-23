<template>
  <div
    class="relative rounded-md text-neutral-400 shadow-sm focus-within:text-neutral-600 dark:focus-within:text-neutral-200"
  >
    <div
      class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
    >
      <Icon icon="SearchSolid" class="size-5" />
    </div>

    <IFormInput
      v-bind="$attrs"
      class="pl-10"
      type="search"
      :model-value="modelValue"
      :placeholder="placeholder || $t('core::app.search')"
      @click="$emit('click', $event)"
      @keydown.enter="emitEvent($event)"
      @input="emitEvent($event)"
    />
  </div>
</template>

<script setup>
import debounce from 'lodash/debounce'

defineOptions({
  inheritAttrs: false,
})

defineProps({
  modelValue: {},
  placeholder: String,
})

const emit = defineEmits(['click', 'input', 'update:modelValue'])

const emitEvent = debounce(function (value) {
  if (value instanceof KeyboardEvent) {
    value = value.target.value
  }

  emit('update:modelValue', value)
  emit('input', value)
}, 650)
</script>
