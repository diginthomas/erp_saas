<template>
  <IndexFieldItem
    v-slot="{ value }"
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    {{ value }}
  </IndexFieldItem>
</template>

<script setup>
defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
