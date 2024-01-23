<template>
  <p class="text-sm text-neutral-500 dark:text-neutral-300">
    <slot>{{ text }}</slot>
  </p>
</template>

<script setup>
defineProps({ text: String })
</script>
