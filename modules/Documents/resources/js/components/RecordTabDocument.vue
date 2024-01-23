<template>
  <ITab
    v-memo="[badge, badgeVariant]"
    :badge="badge"
    :badge-variant="badgeVariant"
    :title="$t('documents::document.documents')"
    icon="DocumentText"
  />
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: [String, Number] },
  resource: { required: true, type: Object },
})

const badge = computed(() =>
  props.resource.draft_documents_for_user_count > 0
    ? props.resource.draft_documents_for_user_count
    : props.resource.documents_for_user_count
)

const badgeVariant = computed(() => {
  return (props.resource.documents || []).filter(
    document => document.status === 'draft'
  ).length > 0
    ? 'danger'
    : 'neutral'
})
</script>
