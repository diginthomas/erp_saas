<template>
  <div :class="condensed ? 'px-6 py-3' : 'px-4 py-4 sm:px-6'">
    <slot />
  </div>
</template>

<script setup>
defineProps({ condensed: Boolean })
</script>
