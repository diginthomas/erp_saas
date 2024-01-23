<template>
  <MediaCard
    v-memo="[resource.media.map(m => m.updated_at)]"
    :resource-name="resourceName"
    :resource-id="resourceId"
    :media="resource.media"
    :authorize-delete="resource.authorizations.update"
    @deleted="synchronizeResource({ media: { id: $event.id, _delete: true } })"
    @uploaded="synchronizeResource({ media: [$event] })"
  />
</template>

<script setup>
import { inject } from 'vue'

import MediaCard from '@/Core/components/Media/ResourceRecordMediaCard.vue'

defineProps({
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: [String, Number] },
  resource: { required: true, type: Object },
})

const synchronizeResource = inject('synchronizeResource')
</script>
