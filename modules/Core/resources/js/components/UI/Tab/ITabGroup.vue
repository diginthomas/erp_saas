<template>
  <TabGroup :selected-index="modelValue" @change="handleChangeEvent">
    <slot />
  </TabGroup>
</template>

<script setup>
import { TabGroup } from '@headlessui/vue'

defineProps({ modelValue: Number })

const emit = defineEmits(['update:modelValue'])

function handleChangeEvent(index) {
  emit('update:modelValue', index)
}
</script>
