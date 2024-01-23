<template>
  <IndexFieldItem
    v-slot="{ hasValue, value }"
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <p v-if="hasValue">
      {{ value }}
    </p>

    <span v-else>&mdash;</span>
  </IndexFieldItem>
</template>

<script setup>
defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
