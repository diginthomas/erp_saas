<template>
  <div
    :class="[
      'border-t border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900',
      condensed ? 'px-6 py-3' : 'px-4 py-4 sm:px-6',
    ]"
  >
    <slot />
  </div>
</template>

<script setup>
defineProps({ condensed: Boolean })
</script>
