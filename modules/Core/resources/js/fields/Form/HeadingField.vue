<template>
  <div class="mb-1 mt-3">
    <h5
      class="text-base font-medium text-neutral-800 dark:text-white"
      :class="[field.titleIcon ? 'inline-flex items-center' : '']"
    >
      <Icon
        v-if="field.titleIcon"
        :icon="field.titleIcon"
        class="mr-1.5 size-4"
      />

      <span v-text="field.title"></span>
    </h5>
    <!-- eslint-disable -->
    <p
      class="text-sm text-neutral-600 dark:text-neutral-300"
      v-html="field.message"
    />
    <!-- eslint-enable -->
  </div>
</template>

<script setup>
defineOptions({
  inheritAttrs: false,
})

defineProps({
  field: { type: Object, require: true },
})
</script>
