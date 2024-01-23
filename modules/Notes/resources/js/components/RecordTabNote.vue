<template>
  <ITab
    v-memo="[resource.notes_count]"
    :badge="resource.notes_count"
    badge-variant="neutral"
    :title="$t('notes::note.notes')"
    icon="PencilAlt"
  />
</template>

<script setup>
defineProps({
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: [String, Number] },
  resource: { required: true, type: Object },
})
</script>
