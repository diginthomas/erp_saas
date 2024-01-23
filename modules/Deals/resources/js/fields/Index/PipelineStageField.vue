<template>
  <IndexFieldItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <span class="font-medium" v-text="field.value?.name" />

    <span
      :class="[
        'text-xs text-neutral-500 dark:text-neutral-400',
        column.wrap ? 'block' : 'ml-1',
      ]"
      v-text="row.pipeline?.name"
    />
  </IndexFieldItem>
</template>

<script setup>
defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
