<template>
  <DetailFieldItem
    v-slot="{ hasValue, value }"
    :field="field"
    :is-floating="isFloating"
    :resource="resource"
    :resource-name="resourceName"
    :resource-id="resourceId"
  >
    <Icon v-if="hasValue" :icon="value" class="size-5" />

    <span v-else>&mdash;</span>
  </DetailFieldItem>
</template>

<script setup>
defineProps(['resource', 'resourceName', 'resourceId', 'field', 'isFloating'])
</script>
