<template>
  <ITab
    v-memo="[resource.calls_count]"
    :badge="resource.calls_count"
    badge-variant="neutral"
    :title="$t('calls::call.calls')"
    icon="Phone"
  />
</template>

<script setup>
defineProps({
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: [String, Number] },
  resource: { required: true, type: Object },
})
</script>
