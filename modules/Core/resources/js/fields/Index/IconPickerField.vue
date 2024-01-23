<template>
  <IndexFieldItem
    v-slot="{ hasValue, value }"
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <Icon v-if="hasValue" :icon="value" class="size-5" />

    <span v-else>&mdash;</span>
  </IndexFieldItem>
</template>

<script setup>
defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
