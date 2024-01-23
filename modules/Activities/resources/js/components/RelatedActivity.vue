<template>
  <ICard
    v-show="!activityBeingEdited"
    v-bind="$attrs"
    condensed
    :class="'activity-' + activityId"
    footer-class="inline-flex flex-col w-full"
    no-body
  >
    <template #header>
      <div class="flex">
        <div class="mr-2 mt-px flex shrink-0 self-start">
          <StateChange
            v-memo="[isCompleted]"
            class="ml-px md:mt-px"
            :activity-id="activityId"
            :is-completed="isCompleted"
            :disabled="!authorizations.update"
            @changed="handleActivityStateChanged"
          />
        </div>

        <div
          class="flex grow flex-col space-y-1 md:flex-row md:space-x-3 md:space-y-0"
        >
          <div class="flex grow flex-col items-start">
            <h3
              class="-mb-1 truncate whitespace-normal text-base/6 font-medium text-neutral-700 dark:text-white"
              :class="{ 'line-through': isCompleted }"
              v-text="title"
            />

            <AssociationsPopover
              :associations-count="associationsCount"
              :initial-associateables="relatedResource"
              :resource-id="activityId"
              :resource-name="resourceName"
              :primary-record="relatedResource"
              :primary-resource-name="viaResource"
              @synced="synchronizeResource({ activities: $event })"
            />
          </div>

          <div class="self-start sm:self-center">
            <IDropdownSelectWithBackground
              value-key="id"
              label-key="name"
              :model-value="typeId"
              :disabled="!authorizations.update"
              :items="typesForDropdown"
              :background-color="type.swatch_color"
              @change="updateActivity({ activity_type_id: $event.id })"
            />
          </div>
        </div>

        <IMinimalDropdown class="ml-2 mt-px self-start sm:self-center md:ml-3">
          <IDropdownItem
            v-if="authorizations.update"
            :text="$t('core::app.edit')"
            @click="toggleEdit"
          />

          <IDropdownItem
            :text="$t('activities::activity.download_ics')"
            @click="downloadICS"
          />

          <IDropdownItem
            v-if="authorizations.delete"
            :text="$t('core::app.delete')"
            @click="$confirm(() => destroy(activityId))"
          />
        </IMinimalDropdown>
      </div>
    </template>

    <div
      v-if="isDue"
      :class="[
        '-mt-px flex items-center border-warning-100 bg-warning-50 px-4 py-1.5 text-sm text-warning-700 sm:px-6',
        Boolean(note) ? 'border-t' : 'border-y',
      ]"
    >
      <Icon icon="Clock" class="mr-3 size-5" />

      <span>
        {{
          $t('activities::activity.activity_was_due', {
            date: hasTime(dueDate)
              ? localizedDateTime(dueDate)
              : localizedDate(dueDate),
          })
        }}
      </span>
    </div>

    <div @dblclick="toggleEdit">
      <div v-if="note" class="-mt-px border border-warning-100 bg-warning-50">
        <TextCollapse
          :text="note"
          :length="100"
          class="wysiwyg-text px-4 py-1.5 leading-5 text-warning-700 sm:px-6"
        >
          <template #action="{ collapsed, toggle }">
            <div
              v-show="collapsed"
              class="absolute bottom-1 h-1/2 w-full cursor-pointer bg-gradient-to-t from-warning-50 to-transparent dark:from-warning-100"
              @click="toggle"
            />

            <ILink
              v-show="!collapsed"
              :text="$t('core::app.show_less')"
              variant="warning"
              class="my-2.5 inline-block px-4 text-sm font-medium sm:px-6"
              @click="toggle"
            />
          </template>
        </TextCollapse>
      </div>

      <ICardBody condensed>
        <div class="space-y-4 sm:space-y-6">
          <div v-if="description" class="mb-8">
            <p
              class="mb-2 inline-flex text-sm font-medium text-neutral-800 dark:text-white"
            >
              <Icon
                icon="Bars3BottomLeft"
                class="mr-3 size-5 text-neutral-500 dark:text-neutral-300"
              />

              <span v-t="'activities::activity.description'"></span>
            </p>

            <div class="ml-8">
              <TextCollapse
                :text="description"
                :length="200"
                class="wysiwyg-text leading-5 sm:mb-0 dark:text-neutral-300"
              />
            </div>
          </div>

          <div
            class="-ml-2 flex flex-col flex-wrap space-x-0 space-y-2 align-baseline lg:flex-row lg:space-x-4 lg:space-y-0"
          >
            <div
              v-if="user"
              v-i-tooltip.top="$t('activities::activity.owner')"
              class="self-start sm:self-auto"
            >
              <IDropdownSelect
                v-if="authorizations.update"
                :items="usersForDropdown"
                :model-value="userId"
                value-key="id"
                label-key="name"
                condensed
                @change="updateActivity({ user_id: $event.id })"
              >
                <template #label="{ label }">
                  <IAvatar size="xs" class="mr-2" :src="user.avatar_url" />

                  {{ label }}
                </template>
              </IDropdownSelect>

              <p
                v-else
                class="flex items-center text-sm font-medium text-neutral-800 dark:text-white"
              >
                <IAvatar size="xs" class="mr-2" :src="user.avatar_url" />
                {{ user.name }}
              </p>
            </div>

            <ActivityDateDisplay
              :due-date="dueDate"
              :end-date="endDate"
              :is-due="isDue"
            />
          </div>
        </div>

        <p
          v-if="reminderMinutesBefore && !isReminded"
          class="mt-2 flex items-center text-sm text-neutral-800 sm:mt-3 dark:text-white"
        >
          <Icon
            icon="Bell"
            class="mr-3 size-5 text-neutral-500 dark:text-neutral-300"
          />

          <span>
            {{ reminderText }}
          </span>
        </p>
      </ICardBody>
    </div>

    <div
      class="border-t border-neutral-200 px-4 py-2.5 sm:px-6 dark:border-neutral-700"
    >
      <MediaCard
        :card="false"
        :show="attachmentsAreVisible"
        :wrapper-class="[
          'ml-8',
          {
            'py-4': attachmentsCount === 0,
            'mb-4': attachmentsCount > 0,
          },
        ]"
        class="mt-1"
        :resource-name="resourceName"
        :resource-id="activityId"
        :media="media"
        :authorize-delete="authorizations.update"
        @deleted="handleActivityMediaDeleted"
        @uploaded="handleActivityMediaUploaded"
      >
        <template #heading>
          <ILink
            class="group inline-flex items-center font-medium"
            variant="neutral"
            @click="attachmentsAreVisible = !attachmentsAreVisible"
          >
            <Icon
              icon="PaperClip"
              class="mr-3 size-5 text-neutral-500 group-hover:text-neutral-700 dark:text-neutral-300 dark:group-hover:text-white"
            />

            <span>
              {{ $t('core::app.attachments') }} ({{ attachmentsCount }})
            </span>

            <Icon
              :icon="attachmentsAreVisible ? 'ChevronDown' : 'ChevronRight'"
              class="ml-3 size-4"
            />
          </ILink>
        </template>
      </MediaCard>
    </div>

    <div
      v-show="commentsCount"
      class="border-t border-neutral-200 px-4 py-2.5 sm:px-6 dark:border-neutral-700"
    >
      <CommentsCollapse
        :via-resource="viaResource"
        :via-resource-id="viaResourceId"
        :commentable-id="activityId"
        commentable-type="activities"
        :count="commentsCount"
        :comments="comments"
        list-wrapper-class="ml-8"
        class="mt-1"
        @updated="
          synchronizeResource({
            activities: { id: activityId, comments: $event },
          })
        "
        @deleted="
          synchronizeResource({
            activities: {
              id: activityId,
              comments: { id: $event, _delete: true },
            },
          })
        "
        @update:comments="
          synchronizeResource({
            activities: { id: activityId, comments: $event },
          })
        "
        @update:count="
          synchronizeResource({
            activities: { id: activityId, comments_count: $event },
          })
        "
      />
    </div>

    <template #footer>
      <CommentsAdd
        class="self-end"
        :via-resource="viaResource"
        :via-resource-id="viaResourceId"
        :commentable-id="activityId"
        commentable-type="activities"
        @created="
          (commentsAreVisible = true),
            synchronizeResource({
              activities: {
                id: activityId,
                comments: [$event],
              },
            })
        "
      />
    </template>
  </ICard>

  <EditActivity
    v-if="activityBeingEdited"
    :activity-id="activityId"
    :via-resource="viaResource"
    :via-resource-id="viaResourceId"
    :related-resource="relatedResource"
    @cancelled="activityBeingEdited = false"
    @updated="activityBeingEdited = false"
  />
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import AssociationsPopover from '@/Core/components/AssociationsPopover.vue'
import MediaCard from '@/Core/components/Media/ResourceRecordMediaCard.vue'
import { handleActionResponse } from '@/Core/composables/useAction'
import { useApp } from '@/Core/composables/useApp'
import { useDates } from '@/Core/composables/useDates'
import { useResourceable } from '@/Core/composables/useResourceable'
import {
  determineReminderTypeBasedOnMinutes,
  determineReminderValueBasedOnMinutes,
} from '@/Core/utils'

import { useComments } from '@/Comments/composables/useComments'

import { useActivityTypes } from '../composables/useActivityTypes'

import ActivityDateDisplay from './ActivityDateDisplay.vue'
import StateChange from './ActivityStateChange.vue'
import EditActivity from './RelatedActivityEdit.vue'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps({
  activityId: { required: true, type: Number },
  title: { required: true, type: String },
  commentsCount: { required: true, type: Number },
  isCompleted: { required: true, type: Boolean },
  isReminded: { required: true, type: Boolean },
  isDue: { required: true, type: Boolean },
  typeId: { required: true, type: Number },
  userId: { required: true, type: Number },
  note: { required: true },
  description: { required: true },
  reminderMinutesBefore: { required: true },
  dueDate: { required: true },
  endDate: { required: true },
  attachmentsCount: { required: true, type: Number },
  media: { required: true, type: Array },
  authorizations: { required: true, type: Object },
  comments: { required: true, type: Array },
  associationsCount: { required: true, type: Number },
  viaResource: { required: true, type: String },
  viaResourceId: { required: true, type: [String, Number] },
  relatedResource: { required: true, type: Object },
})

const resourceName = Innoclapps.resourceName('activities')

const synchronizeResource = inject('synchronizeResource')
const incrementResourceCount = inject('incrementResourceCount')
const decrementResourceCount = inject('decrementResourceCount')

const { t } = useI18n()
const { localizedDateTime, localizedDate, hasTime } = useDates()
const { updateResource, deleteResource } = useResourceable(resourceName)
const { users, findUserById } = useApp()

const usersForDropdown = computed(() =>
  users.value.map(user => ({ id: user.id, name: user.name }))
)

const { commentsAreVisible } = useComments(props.activityId, 'activities')

const activityBeingEdited = ref(false)
const attachmentsAreVisible = ref(false)

const { typesByName: types, findTypeById } = useActivityTypes()

const typesForDropdown = computed(() =>
  types.value.map(type => ({
    id: type.id,
    name: type.name,
    icon: type.icon,
  }))
)

const type = computed(() => findTypeById(props.typeId))

const user = computed(() => findUserById(props.userId))

const reminderText = computed(() => {
  return t('core::app.reminder_set_for', {
    value: determineReminderValueBasedOnMinutes(props.reminderMinutesBefore),
    type: t(
      'core::dates.' +
        determineReminderTypeBasedOnMinutes(props.reminderMinutesBefore)
    ),
  })
})

/**
 * Download ICS file for the activity.
 */
function downloadICS() {
  Innoclapps.request({
    method: 'post',
    data: {
      ids: [props.activityId],
    },
    responseType: 'blob',
    url: '/activities/actions/download-ics-file/run',
  }).then(handleActionResponse)
}

/**
 * Update the current activity.
 */
function updateActivity(payload = {}) {
  updateResource(
    {
      via_resource: props.viaResource,
      via_resource_id: props.viaResourceId,
      ...payload,
    },
    props.activityId
  ).then(updatedActivity =>
    synchronizeResource({ activities: updatedActivity })
  )
}

/**
 * Delete activity from storage.
 */
async function destroy(id) {
  await deleteResource(id)

  synchronizeResource({ activities: { id, _delete: true } })
  decrementResourceCount('incomplete_activities_for_user_count')

  Innoclapps.success(t('activities::activity.deleted'))
}

/**
 * Activity state changed.
 */
function handleActivityStateChanged(activity) {
  synchronizeResource({ activities: activity })

  if (activity.is_completed) {
    decrementResourceCount('incomplete_activities_for_user_count')
  } else {
    incrementResourceCount('incomplete_activities_for_user_count')
  }
}

/**
 * Toggle edit.
 */
function toggleEdit(e) {
  // The double click to edit should not work while in edit mode
  if (e.type == 'dblclick' && activityBeingEdited.value) return
  // For double click event
  if (!props.authorizations.update) return

  activityBeingEdited.value = !activityBeingEdited.value
}

/**
 * Handle activity media uploaded.
 */
function handleActivityMediaUploaded(media) {
  synchronizeResource({
    activities: {
      id: props.activityId,
      media: [media],
    },
  })
}

/**
 * Handle activity media deleted.
 */
function handleActivityMediaDeleted(media) {
  synchronizeResource({
    activities: {
      id: props.activityId,
      media: { id: media.id, _delete: true },
    },
  })
}
</script>
