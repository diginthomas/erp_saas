<template>
  <TableDataFloatingSeparator :class="condensed ? 'left-8' : 'left-12'" />
</template>

<script setup>
import TableDataFloatingSeparator from './TableDataFloatingSeparator.vue'

defineProps({ condensed: { type: Boolean, required: true } })
</script>
