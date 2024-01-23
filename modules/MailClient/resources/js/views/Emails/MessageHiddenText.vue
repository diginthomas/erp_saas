<template>
  <div v-show="text">
    <IButton
      v-i-tooltip="$t('mailclient::mail.trimmed_content')"
      :size="false"
      variant="white"
      class="mt-4 px-1.5 py-0"
      icon="DotsHorizontal"
      @click="showHiddenText = !showHiddenText"
    />

    <div v-show="showHiddenText" class="mt-5">
      <HtmlableLightbox :html="text" />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

import HtmlableLightbox from '@/Core/components/Lightbox/HtmlableLightbox.vue'

defineProps({
  text: { required: true },
})

const showHiddenText = ref(false)
</script>
