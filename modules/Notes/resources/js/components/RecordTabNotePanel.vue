<template>
  <ITabPanel :lazy="!dataLoadedFirstTime" @activated.once="loadData">
    <div
      class="-mt-[20px] mb-3 overflow-hidden rounded-b-md border border-neutral-200 bg-white sm:mb-7 dark:border-neutral-700 dark:bg-neutral-900"
    >
      <div v-show="!showCreateForm" class="px-4 py-5 sm:p-6">
        <div class="sm:flex sm:items-start sm:justify-between">
          <div>
            <h3
              v-t="'notes::note.manage_notes'"
              class="text-base/6 font-medium text-neutral-900 dark:text-white"
            />

            <div
              class="mt-2 max-w-xl text-sm text-neutral-500 dark:text-neutral-200"
            >
              <p v-t="'notes::note.info'" />
            </div>
          </div>

          <div class="mt-5 sm:ml-6 sm:mt-0 sm:flex sm:shrink-0 sm:items-center">
            <IButton
              icon="Plus"
              :text="$t('notes::note.add')"
              @click="showCreateForm = true"
            />
          </div>
        </div>

        <SearchInput
          v-show="hasNotes || search"
          v-model="search"
          class="mt-2"
          @input="performSearch"
        />
      </div>

      <NotesCreate
        v-if="showCreateForm"
        :shadow="false"
        :ring="false"
        :rounded="false"
        :via-resource="resourceName"
        :via-resource-id="resourceId"
        :related-resource-display-name="resource.display_name"
        @cancel="showCreateForm = false"
      />
    </div>

    <div class="space-y-4">
      <div
        v-for="note in notes"
        :key="note.id"
        v-memo="[note.updated_at, note.comments_count]"
      >
        <NotesView
          :note-id="note.id"
          :comments-count="note.comments_count"
          :created-at="note.created_at"
          :body="note.body"
          :user-id="note.user_id"
          :authorizations="note.authorizations"
          :comments="note.comments || []"
          :via-resource="resourceName"
          :via-resource-id="resourceId"
        />
      </div>
    </div>

    <div
      v-show="isSearching && !hasSearchResults"
      v-t="'core::app.no_search_results'"
      class="mt-6 text-center text-neutral-800 dark:text-neutral-200"
    />

    <InfinityLoader
      ref="infinityRef"
      :scroll-element="scrollElement"
      @handle="infiniteHandler($event)"
    />
  </ITabPanel>
</template>

<script setup>
import { computed, inject, ref, toRef } from 'vue'
import { useRoute } from 'vue-router'
import { watchOnce } from '@vueuse/core'
import orderBy from 'lodash/orderBy'

import InfinityLoader from '@/Core/components/InfinityLoader.vue'
import { useRecordTab } from '@/Core/composables/useRecordTab'

import { useComments } from '@/Comments/composables/useComments'

import NotesCreate from './NotesCreate.vue'
import NotesView from './NotesView.vue'

const props = defineProps({
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: [String, Number] },
  resource: { required: true, type: Object },
  scrollElement: { type: String },
})

const synchronizeResource = inject('synchronizeResource')

const route = useRoute()

const timelineRelation = 'notes'
const infinityRef = ref(null)
const showCreateForm = ref(false)

const { commentsAreVisible } = useComments(
  route.query.resourceId,
  timelineRelation
)

const {
  dataLoadedFirstTime,
  focusToAssociateableElement,
  searchResults,
  isSearching,
  hasSearchResults,
  performSearch,
  search,
  loadData,
  infiniteHandler,
} = useRecordTab({
  resourceName: props.resourceName,
  resource: toRef(props, 'resource'),
  scrollElement: props.scrollElement,
  infinityRef,
  synchronizeResource,
  timelineRelation,
})

const notes = computed(() =>
  orderBy(searchResults.value || props.resource.notes, 'created_at', 'desc')
)

const hasNotes = computed(() => notes.value.length > 0)

if (route.query.resourceId && route.query.section === timelineRelation) {
  // Wait till the data is loaded for the first time and the
  // elements are added to the document so we can have a proper scroll
  watchOnce(dataLoadedFirstTime, () => {
    focusToAssociateableElement(route.query.resourceId, 'note').then(() => {
      if (route.query.comment_id) {
        commentsAreVisible.value = true
      }
    })
  })
}
</script>
