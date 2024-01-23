<template>
  <div v-once class="mail-text all-revert">
    <div class="font-sans text-sm leading-[initial] dark:text-white">
      <HtmlableLightbox :html="visibleText" />

      <HiddenText :text="hiddenText" />
    </div>
  </div>
</template>

<script setup>
import HtmlableLightbox from '@/Core/components/Lightbox/HtmlableLightbox.vue'

import HiddenText from './MessageHiddenText.vue'

defineProps({
  visibleText: null,
  hiddenText: null,
})
</script>
