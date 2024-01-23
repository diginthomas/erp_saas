<template>
  <button
    type="button"
    :disabled="disabled"
    class="ml-1 text-current focus:outline-none"
    :title="`Deselect ${label}`"
    :aria-label="`Deselect ${label}`"
    @click.prevent.stop="deselect(option)"
  >
    <Icon icon="X" class="size-4" />
  </button>
</template>

<script setup>
defineProps({
  option: { required: true },
  deselect: { type: Function, required: true },
  disabled: Boolean,
  label: { type: [String, Number], required: true },
})
</script>
