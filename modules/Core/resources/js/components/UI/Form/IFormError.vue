<template>
  <p class="mt-2 text-sm text-danger-600 dark:text-danger-500">
    <slot>{{ error }}</slot>
  </p>
</template>

<script setup>
defineProps({ error: String })
</script>
