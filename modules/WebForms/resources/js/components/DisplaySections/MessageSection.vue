<!-- eslint-disable vue/no-v-html -->
<template>
  <div
    class="wysiwyg-text mb-3 text-sm text-neutral-600 dark:text-neutral-300"
    v-html="section.message"
  />
</template>

<script setup>
import propsDefinition from './props'

defineProps(propsDefinition)
</script>
