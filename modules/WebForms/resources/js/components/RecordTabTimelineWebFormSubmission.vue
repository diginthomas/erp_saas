<template>
  <BaseRecordTabTimelineItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :created-at="log.created_at"
    :is-pinned="log.is_pinned"
    :timelineable-id="log.id"
    :timeline-relationship="log.timeline_relation"
    :timeline-subject-key="resource.timeline_subject_key"
    :timelineable-key="log.timeline_key"
    :heading="$t('webforms::form.submission')"
    heading-class="font-medium"
    icon="Bars3BottomLeft"
  >
    <ICard v-once class="mt-2" body-class="space-y-4">
      <p
        class="mb-2 text-sm font-semibold text-neutral-900 dark:text-neutral-100"
        v-text="log.description"
      />

      <div v-for="(property, index) in log.properties" :key="index">
        <div
          class="flex justify-start space-x-1 text-sm font-semibold text-neutral-800 dark:text-neutral-200"
        >
          <span>{{ resources[property.resourceName].singularLabel }} /</span>
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span class="font-medium" v-html="property.label" />
        </div>

        <div class="text-sm text-neutral-600 dark:text-neutral-400">
          <span v-if="property.value === null" v-text="'/'" />

          <span
            v-else
            v-text="localizeIfDate(property.value) || property.value"
          />
        </div>
      </div>
    </ICard>
  </BaseRecordTabTimelineItem>
</template>

<script setup>
import { useDates } from '@/Core/composables/useDates'
import BaseRecordTabTimelineItem from '@/Core/views/Timeline/BaseRecordTabTimelineItem.vue'

defineProps({
  log: { type: Object, required: true },
  resourceName: { type: String, required: true },
  resourceId: { type: [String, Number], required: true },
  resource: { type: Object, required: true },
})

const { localizeIfDate } = useDates()

const resources = Innoclapps.resources()
</script>
