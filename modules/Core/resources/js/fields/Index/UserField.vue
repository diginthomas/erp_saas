<template>
  <IndexFieldItem
    v-slot="{ hasValue, value }"
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <span v-if="hasValue" class="inline-flex items-center">
      <IAvatar
        :src="value.avatar_url"
        :alt="value.name"
        size="xs"
        class="mr-1 self-start"
      />
      {{ value.name }}
    </span>

    <span v-else>&mdash;</span>
  </IndexFieldItem>
</template>

<script setup>
defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
