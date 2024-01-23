<template>
  <MainLayout>
    <div class="mx-auto max-w-5xl">
      <ICard no-body :header="$t('core::notifications.notifications')">
        <template #actions>
          <IButton
            v-show="countNotifications > 0"
            variant="white"
            :loading="requestInProgress"
            :disabled="!hasUnreadNotifications"
            :text="$t('core::notifications.mark_all_as_read')"
            @click="markAllRead"
          />
        </template>

        <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
          <li
            v-for="(notification, index) in notifications"
            :key="notification.id"
          >
            <ILink
              class="block hover:bg-neutral-50 dark:hover:bg-neutral-700/60"
              :to="notification.data.path"
              plain
            >
              <div class="flex items-center px-4 py-4 sm:px-6">
                <div
                  class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between"
                >
                  <div class="truncate">
                    <p
                      class="truncate text-sm font-medium text-neutral-800 dark:text-neutral-100"
                      v-text="localize(notification)"
                    />

                    <p
                      class="mt-2 text-sm text-neutral-500 dark:text-neutral-300"
                      v-text="localizedDateTime(notification.created_at)"
                    />
                  </div>
                </div>

                <IButtonIcon
                  icon="Trash"
                  class="ml-5 shrink-0 self-start"
                  @click.stop="destroy(index)"
                />
              </div>
            </ILink>
          </li>
        </ul>

        <InfinityLoader @handle="loadHandler" />

        <ICardBody v-show="countNotifications === 0" class="text-center">
          <Icon icon="EmojiSad" class="mx-auto size-12 text-neutral-400" />

          <h3
            v-t="'core::notifications.no_notifications'"
            class="mt-2 text-sm font-medium text-neutral-800 dark:text-white"
          />
        </ICardBody>

        <p
          v-show="noMoreResults"
          v-t="'core::notifications.no_more_notifications'"
          class="p-3 text-center text-neutral-600"
        />
      </ICard>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed, nextTick, ref, shallowReactive } from 'vue'
import { useStore } from 'vuex'

import InfinityLoader from '@/Core/components/InfinityLoader.vue'
import { useApp } from '@/Core/composables/useApp'
import { useDates } from '@/Core/composables/useDates'
import { useGlobalEventListener } from '@/Core/composables/useGlobalEventListener'

const store = useStore()
const { currentUser } = useApp()
const { localizedDateTime } = useDates()

const notifications = shallowReactive([])
const noMoreResults = ref(false)
const nextPage = ref(2)
const requestInProgress = ref(false)

const hasUnreadNotifications = computed(
  () => store.getters['users/hasUnreadNotifications']
)

const localize = store.getters['users/localizeNotification']

const countNotifications = computed(() => notifications.length)

function markAllRead() {
  requestInProgress.value = true

  store
    .dispatch('users/markAllNotificationsAsRead')
    .finally(() => (requestInProgress.value = false))
}

function destroy(index) {
  store
    .dispatch('users/destroyNotification', notifications[index])
    .then(() => notifications.splice(index, 1))
}

function addNotifications(newNotifications) {
  // Filter out notifications that already exist
  const uniqueNotifications = newNotifications.filter(
    newNotification =>
      !notifications.some(
        existingNotification => existingNotification.id === newNotification.id
      )
  )

  // Add the unique notifications to the existing array
  notifications.push(...uniqueNotifications)
}

async function loadHandler($state) {
  let { data } = await loadMore()

  addNotifications(data.data)

  await nextTick()

  if (data.total === countNotifications.value) {
    noMoreResults.value = true
    $state.complete()
  }

  nextPage.value += 1
  $state.loaded()
}

function loadMore() {
  return Innoclapps.request(store.state.users.notificationsEndpoint, {
    params: {
      page: nextPage.value,
    },
  })
}

function handleNewNotificationBroadcasted(notification) {
  notifications.unshift(notification)
}

// Set the initial notifications from the current user, as it's the first page
currentUser.value.notifications.latest.forEach(notification =>
  notifications.push(notification)
)

// Push new notification when new notification is broadcasted/added to update this list too
// Useful when the user is at the all notifications route,
// will update all notifications and the dropdown notifications too
useGlobalEventListener(
  'new-notification-added',
  handleNewNotificationBroadcasted
)
</script>
