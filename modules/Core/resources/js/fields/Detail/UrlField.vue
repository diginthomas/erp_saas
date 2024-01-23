<template>
  <DetailFieldItem
    v-slot="{ hasValue, value }"
    :field="field"
    :is-floating="isFloating"
    :resource="resource"
    :resource-name="resourceName"
    :resource-id="resourceId"
  >
    <ILink v-if="hasValue" :href="value" :text="value" class="break-all" />

    <span v-else>&mdash;</span>
  </DetailFieldItem>
</template>

<script setup>
defineProps(['resource', 'resourceName', 'resourceId', 'field', 'isFloating'])
</script>
