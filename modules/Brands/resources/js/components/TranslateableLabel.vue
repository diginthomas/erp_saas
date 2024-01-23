<template>
  <div class="mb-1 flex justify-between">
    <IFormLabel :for="labelFor" :label="label" />

    <IDropdownSelect
      :model-value="locale"
      :items="locales"
      placement="bottom-end"
      @update:model-value="$emit('update:locale', $event)"
    />
  </div>
</template>

<script setup>
import { useApp } from '@/Core/composables/useApp'

defineProps(['labelFor', 'label', 'locale'])

defineEmits('update:locale')

const { locales } = useApp()
</script>
