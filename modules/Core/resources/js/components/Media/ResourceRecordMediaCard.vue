<template>
  <div
    :class="{
      'rounded-lg border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900':
        card,
    }"
  >
    <div :class="{ 'px-4 py-5 sm:px-6': card }">
      <slot name="heading">
        <h2 class="font-medium text-neutral-800 dark:text-white">
          {{ $t('core::app.attachments') }}
          <span
            v-if="total > 0"
            class="text-sm font-normal text-neutral-400"
            v-text="'(' + total + ')'"
          />
        </h2>
      </slot>

      <div v-if="show" :class="wrapperClass">
        <MediaItemsList
          :items="localMedia"
          :authorize-delete="authorizeDelete"
          @delete-requested="$confirm(() => destroy($event))"
        />

        <p
          v-show="!hasMedia"
          v-t="'core::app.no_attachments'"
          class="text-sm text-neutral-500 dark:text-neutral-300"
        />

        <div class="mt-3">
          <MediaUpload
            :input-id="
              'media-' +
              resourceName +
              '-' +
              resourceId +
              (isFloating ? '-floating' : '')
            "
            :action-url="`${$scriptConfig(
              'apiURL'
            )}/${resourceName}/${resourceId}/media`"
            @file-uploaded="uploadedEventHandler"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import orderBy from 'lodash/orderBy'

import MediaItemsList from './MediaItemsList.vue'
import MediaUpload from './MediaUpload.vue'

const props = defineProps({
  show: { default: true, type: Boolean },
  resourceName: { type: String, required: true },
  resourceId: { type: Number, required: true },
  media: { type: Array, required: true },
  authorizeDelete: { required: true, type: Boolean },
  isFloating: { type: Boolean, required: false },
  automaticUpload: { default: true, type: Boolean },
  card: { default: true, type: Boolean },
  wrapperClass: [String, Array, Object],
})

const emit = defineEmits(['deleted', 'uploaded'])

const localMedia = computed(() => {
  return orderBy(props.media, media => new Date(media.created_at), ['desc'])
})

const total = computed(() => {
  return props.media.length
})

const hasMedia = computed(() => total.value > 0)

function uploadedEventHandler(media) {
  emit('uploaded', media)
}

async function destroy(media) {
  await Innoclapps.request().delete(
    `/${props.resourceName}/${props.resourceId}/media/${media.id}`
  )

  emit('deleted', media)
}
</script>
