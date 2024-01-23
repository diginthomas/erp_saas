<template>
  <IDropdownItem
    :active="isActive"
    :text="name"
    :icon="isDefault ? 'Star' : null"
    prepend-icon
    @click="$emit('click', filterId)"
  />
</template>

<script setup>
import { computed } from 'vue'

import { useFilterable } from '../../composables/useFilterable'

const props = defineProps({
  identifier: { required: true, type: String },
  view: { required: true, type: String },
  filterId: { type: Number, required: true },
  name: { type: String, required: true },
})

defineEmits(['click'])

const { activeFilter, currentUserDefaultFilter } = useFilterable(
  props.identifier,
  props.view
)

/**
 * Indicates whether the current filter is active
 */
const isActive = computed(
  () => activeFilter.value && activeFilter.value.id == props.filterId
)

/**
 * Indicates whether the given filter is default for the current view
 */
const isDefault = computed(() => {
  if (!currentUserDefaultFilter.value) {
    return false
  }

  return props.filterId == currentUserDefaultFilter.value.id
})
</script>
