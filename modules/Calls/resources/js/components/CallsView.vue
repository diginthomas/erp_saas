<template>
  <ICard
    v-show="!callBeingEdited"
    v-bind="$attrs"
    condensed
    :class="'call-' + callId"
    footer-class="inline-flex flex-col w-full"
  >
    <template #header>
      <div class="flex items-center">
        <ILink
          v-if="hasTextToCollapse"
          variant="neutral"
          class="mr-2 mt-0.5 shrink-0 self-start md:mt-0 md:self-auto"
          @click="collapsed = !collapsed"
        >
          <Icon
            :icon="collapsed ? 'ChevronRight' : 'ChevronDown'"
            class="size-4"
          />
        </ILink>

        <IAvatar
          v-once
          size="xs"
          class="mr-1.5 shrink-0 self-start md:self-auto"
          :src="user.avatar_url"
        />

        <div
          class="flex grow flex-col space-y-1 md:flex-row md:items-center md:space-x-3 md:space-y-0"
        >
          <I18nT
            scope="global"
            keypath="calls::call.info_created"
            tag="span"
            class="grow text-sm text-neutral-700 dark:text-white"
          >
            <template #user>
              <span class="font-medium">
                {{ user.name }}
              </span>
            </template>

            <template #date>
              <span class="font-medium" v-text="localizedDateTime(callDate)" />
            </template>
          </I18nT>

          <div class="self-start sm:self-center">
            <IDropdownSelectWithBackground
              v-memo="[outcomeId]"
              value-key="id"
              label-key="name"
              :model-value="outcome"
              :disabled="!authorizations.update"
              :items="outcomesForDropdown"
              :background-color="outcome.swatch_color"
              @change="update({ call_outcome_id: $event.id })"
            />
          </div>
        </div>

        <IMinimalDropdown
          v-if="authorizations.update && authorizations.delete"
          class="ml-2 self-start sm:self-center md:ml-3"
        >
          <IDropdownItem
            v-show="authorizations.update"
            :text="$t('core::app.edit')"
            @click="toggleEdit"
          />

          <IDropdownItem
            v-show="authorizations.delete"
            :text="$t('core::app.delete')"
            @click="$confirm(() => destroy(callId))"
          />
        </IMinimalDropdown>
      </div>
    </template>

    <TextCollapse
      v-if="collapsable"
      v-model:collapsed="collapsed"
      :text="body"
      :length="250"
      class="wysiwyg-text"
      @has-text-to-collapse="hasTextToCollapse = $event"
      @dblclick="toggleEdit"
    />

    <!-- eslint-disable-next-line vue/no-v-html -->
    <div v-else class="wysiwyg-text" @dblclick="toggleEdit" v-html="body" />

    <CommentsCollapse
      class="mt-6"
      :via-resource="viaResource"
      :via-resource-id="viaResourceId"
      :commentable-id="callId"
      commentable-type="calls"
      :count="commentsCount"
      :comments="comments"
      @updated="
        synchronizeResource({
          calls: { id: callId, comments: $event },
        })
      "
      @deleted="
        synchronizeResource({
          calls: { id: callId, comments: { id: $event, _delete: true } },
        })
      "
      @update:comments="
        synchronizeResource({
          calls: { id: callId, comments: $event },
        })
      "
      @update:count="
        synchronizeResource({
          calls: { id: callId, comments_count: $event },
        })
      "
    />

    <template #footer>
      <CommentsAdd
        class="self-end"
        :via-resource="viaResource"
        :via-resource-id="viaResourceId"
        :commentable-id="callId"
        commentable-type="calls"
        @created="
          (commentsAreVisible = true),
            synchronizeResource({
              calls: {
                id: callId,
                comments: [$event],
              },
            })
        "
      />
    </template>
  </ICard>

  <CallsEdit
    v-if="callBeingEdited"
    :via-resource="viaResource"
    :via-resource-id="viaResourceId"
    :call-id="callId"
    @cancelled="callBeingEdited = false"
    @updated="callBeingEdited = false"
  />
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import { useApp } from '@/Core/composables/useApp'
import { useDates } from '@/Core/composables/useDates'
import { useResourceable } from '@/Core/composables/useResourceable'

import { useComments } from '@/Comments/composables/useComments'

import { useCallOutcomes } from '../composables/useCallOutcomes'

import CallsEdit from './CallsEdit.vue'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps({
  callId: { required: true, type: Number },
  commentsCount: { required: true, type: Number },
  callDate: { required: true, type: String },
  body: { required: true, type: String },
  userId: { required: true, type: Number },
  outcomeId: { required: true, type: Number },
  viaResource: { required: true, type: String },
  viaResourceId: { required: true, type: [String, Number] },
  authorizations: { required: true, type: Object },
  comments: { required: true, type: Array },
  collapsable: Boolean,
})

const synchronizeResource = inject('synchronizeResource')
const decrementResourceCount = inject('decrementResourceCount')

const { t } = useI18n()
const { localizedDateTime } = useDates()
const { outcomesByName } = useCallOutcomes()
const { findUserById } = useApp()

const { updateResource, deleteResource } = useResourceable(
  Innoclapps.resourceName('calls')
)

const outcome = computed(() =>
  outcomesByName.value.find(o => o.id == props.outcomeId)
)

const outcomesForDropdown = computed(() =>
  outcomesByName.value.map(outcome => ({
    id: outcome.id,
    name: outcome.name,
  }))
)

const user = computed(() => findUserById(props.userId))

const { commentsAreVisible } = useComments(props.callId, 'calls')

const callBeingEdited = ref(false)
const hasTextToCollapse = ref(false)
const collapsed = ref(true)

async function update(payload = {}) {
  let call = await updateResource(
    {
      call_outcome_id: props.outcomeId,
      date: props.callDate,
      body: props.body,
      via_resource: props.viaResource,
      via_resource_id: props.viaResourceId,
      ...payload,
    },
    props.callId
  )

  synchronizeResource({ calls: call })
}

async function destroy(id) {
  await deleteResource(id)

  synchronizeResource({ calls: { id, _delete: true } })
  decrementResourceCount('calls_count')

  Innoclapps.success(t('calls::call.deleted'))
}

function toggleEdit(e) {
  // The double click to edit should not work while in edit mode
  if (e.type == 'dblclick' && callBeingEdited.value) return
  // For double click event
  if (!props.authorizations.update) return

  callBeingEdited.value = !callBeingEdited.value
}
</script>
