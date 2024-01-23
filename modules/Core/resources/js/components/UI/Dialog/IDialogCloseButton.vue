<template>
  <button
    type="button"
    class="rounded-md text-neutral-400 hover:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:text-neutral-300 dark:hover:text-neutral-400"
    @click="$emit('click')"
  >
    <Icon icon="X" class="size-6" />
  </button>
</template>

<script setup>
defineEmits(['click'])
</script>
