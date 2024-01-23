<template>
  <div class="hidden sm:relative sm:block sm:px-1">
    <div
      :class="[
        isPinned ? 'bg-warning-300' : 'bg-neutral-200 dark:bg-neutral-700',
        'flex size-8 items-center justify-center rounded-full ring-8 ring-neutral-100 dark:ring-neutral-800',
      ]"
    >
      <Icon
        :icon="icon"
        :class="[
          'size-5',
          isPinned
            ? 'text-warning-900'
            : 'text-neutral-600 dark:text-neutral-100',
        ]"
      />
    </div>
  </div>

  <div class="min-w-0 flex-1 sm:py-1.5">
    <div class="flex justify-between space-x-4 text-sm">
      <div class="flex-1 md:flex md:items-center">
        <p class="mr-2 text-neutral-800 dark:text-white" :class="headingClass">
          <slot name="heading">
            {{ heading }}
          </slot>
        </p>

        <span
          v-once
          class="whitespace-nowrap text-xs text-neutral-500 dark:text-neutral-300"
        >
          <slot name="date">
            <span class="hidden md:inline-block">-</span>
            {{ localizedDateTime(createdAt) }}
          </slot>
        </span>
      </div>

      <TimelineEntryPin
        :resource-name="resourceName"
        :resource-id="resourceId"
        :timeline-subject-key="timelineSubjectKey"
        :timeline-relationship="timelineRelationship"
        :timelineable-key="timelineableKey"
        :timelineable-id="timelineableId"
        :is-pinned="isPinned"
      />
    </div>

    <slot />
  </div>
</template>

<script setup>
import { useDates } from '@/Core/composables/useDates'

import TimelineEntryPin from './RecordTabTimelineEntryPin.vue'

defineProps({
  heading: String,
  headingClass: [String, Object, Array],
  resourceName: { type: String, required: true },
  resourceId: { type: [String, Number], required: true },
  isPinned: { type: Boolean, required: true },
  createdAt: { type: String, required: true },
  timelineRelationship: { type: String, required: true },
  timelineSubjectKey: { type: String, required: true },
  timelineableKey: { type: String, required: true },
  timelineableId: { type: [Number, String], required: true },
  icon: { type: String, required: true },
})

const { localizedDateTime } = useDates()
</script>
