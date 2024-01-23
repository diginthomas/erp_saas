<template>
  <UrlableLightbox
    v-model="activeLightboxImageIndex"
    :urls="imagesUrlsForLightbox"
  >
    <template #toolbar>
      <a
        v-for="type in ['preview_url', 'download_url']"
        :key="type"
        :href="activeLightboxMedia[type]"
        :target="type === 'preview_url' ? '_blank' : undefined"
        tabindex="-1"
        rel="noopener noreferrer"
        class="toolbar-btn"
      >
        <Icon
          :icon="type == 'preview_url' ? 'Eye' : 'Download'"
          class="size-5"
        />
      </a>
    </template>
  </UrlableLightbox>

  <ul
    class="divide-y divide-neutral-200 dark:divide-neutral-700"
    v-bind="$attrs"
  >
    <li
      v-for="media in items"
      :key="media.id"
      class="group flex items-center space-x-3 py-4"
    >
      <div class="shrink-0">
        <span
          :class="[
            media.was_recently_created
              ? 'bg-success-500 text-white'
              : 'bg-neutral-200 text-neutral-400 dark:bg-neutral-700 dark:text-neutral-300',
          ]"
          class="inline-flex size-8 items-center justify-center rounded-full text-sm"
        >
          <Icon v-if="media.was_recently_created" icon="Check" class="size-5" />

          <span v-else v-text="media.extension"></span>
        </span>
      </div>

      <div class="min-w-0 flex-1 truncate">
        <ILink
          v-if="media.aggregate_type !== 'image'"
          :href="media.view_url"
          class="font-medium"
          tabindex="0"
          variant="neutral"
        >
          {{ media.file_name }}
        </ILink>

        <ILink
          v-else
          :href="media.view_url"
          class="font-medium"
          tabindex="0"
          variant="neutral"
          @click.prevent="
            activeLightboxImageIndex = findIndexForLightbox(media.preview_url)
          "
        >
          {{ media.file_name }}
        </ILink>

        <span
          class="ml-2 text-sm text-neutral-500 dark:text-neutral-300"
          v-text="formatBytes(media.size)"
        />

        <p
          class="text-sm text-neutral-500 dark:text-neutral-300"
          v-text="localizedDateTime(media.created_at)"
        />
      </div>

      <div class="block shrink-0 md:hidden md:group-hover:block">
        <div class="flex items-center space-x-2">
          <ILink :href="media.download_url" variant="neutral" download>
            <Icon icon="Download" class="size-5" />
          </ILink>

          <ILink
            v-if="authorizeDelete"
            variant="danger"
            @click="$emit('deleteRequested', media)"
          >
            <Icon icon="X" class="mt-0.5 size-5" />
          </ILink>
        </div>
      </div>
    </li>
  </ul>
</template>

<script setup>
import { computed, ref } from 'vue'

import { useDates } from '@/Core/composables/useDates'
import { formatBytes } from '@/Core/utils'

import UrlableLightbox from '../Lightbox/UrlableLightbox.vue'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps({
  items: Array,
  authorizeDelete: Boolean,
})

defineEmits(['deleteRequested'])

const activeLightboxImageIndex = ref(null)

const { localizedDateTime } = useDates()

const mediaImages = computed(() =>
  props.items.filter(media => media.aggregate_type === 'image')
)

const imagesUrlsForLightbox = computed(() =>
  mediaImages.value.map(media => media.preview_url)
)

const activeLightboxMedia = computed(() => {
  return mediaImages.value[activeLightboxImageIndex.value]
})

function findIndexForLightbox(previewUrl) {
  return props.items.findIndex(media => media.preview_url === previewUrl)
}
</script>
