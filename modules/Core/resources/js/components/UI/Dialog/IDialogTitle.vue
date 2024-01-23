<template>
  <div>
    <DialogTitle class="text-lg/6 font-medium text-neutral-700 dark:text-white">
      <slot :title="title">
        {{ title }}
      </slot>
    </DialogTitle>

    <p
      v-if="subTitle"
      class="mt-0.5 text-sm text-neutral-500 dark:text-neutral-300"
      v-text="subTitle"
    />
  </div>
</template>

<script setup>
defineProps({ title: String, subTitle: String })
import { DialogTitle } from '@headlessui/vue'
</script>
