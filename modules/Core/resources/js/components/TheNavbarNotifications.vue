<template>
  <span class="relative">
    <span
      v-if="hasUnread"
      v-i-tooltip.bottom="totalUnread"
      class="absolute -right-1 -top-4 z-10"
    >
      <span class="relative flex size-3">
        <span
          class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary-400 opacity-75"
        />

        <span class="relative inline-flex size-3 rounded-full bg-primary-500" />
      </span>
    </span>

    <IDropdown
      ref="dropdownRef"
      placement="bottom-end"
      items-class="max-w-xs sm:max-w-sm"
    >
      <template #toggle="{ toggle }">
        <IButton
          class="p-1.5"
          variant="white"
          rounded="full"
          icon="Bell"
          icon-class="size-6"
          :size="false"
          @click="toggle(), markAllRead()"
        />
      </template>

      <div
        :class="[
          'flex items-center px-4 py-3 sm:p-4',
          { 'border-b border-neutral-200 dark:border-neutral-700': total > 0 },
        ]"
      >
        <div
          v-t="
            total > 0
              ? 'core::notifications.notifications'
              : 'core::notifications.no_notifications'
          "
          :class="[
            'grow text-neutral-800 dark:text-white',
            { 'font-medium': total > 0, 'sm:text-sm': total === 0 },
          ]"
        />

        <span
          v-i-tooltip="$t('core::settings.settings')"
          class="ml-2 inline-block"
        >
          <ILink
            :to="{ name: 'profile', hash: '#notifications' }"
            @click="() => $refs.dropdownRef.hide()"
          >
            <Icon icon="Cog" class="size-5" />
          </ILink>
        </span>
      </div>

      <div
        class="max-h-96 divide-y divide-neutral-200 overflow-y-auto dark:divide-neutral-700"
      >
        <IDropdownItem
          v-for="notification in notifications"
          :key="notification.id"
          :title="localize(notification)"
          :to="notification.data.path"
        >
          <p
            class="truncate text-neutral-800 dark:text-neutral-100"
            v-text="localize(notification)"
          />

          <span
            class="text-xs text-neutral-500 dark:text-neutral-300"
            v-text="localizedDateTime(notification.created_at)"
          />
        </IDropdownItem>
      </div>

      <div
        v-show="total > 0"
        class="flex items-center justify-end border-t border-neutral-200 bg-neutral-50 px-4 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-700"
      >
        <ILink
          :to="{ name: 'notifications' }"
          :text="$t('core::app.see_all')"
          @click="() => $refs.dropdownRef.hide()"
        />
      </div>
    </IDropdown>
  </span>
</template>

<script setup>
import { computed } from 'vue'
import { useStore } from 'vuex'

import { useApp } from '@/Core/composables/useApp'
import { useDates } from '@/Core/composables/useDates'

const { currentUser } = useApp()
const { localizedDateTime } = useDates()

const store = useStore()

const total = computed(() => store.getters['users/totalNotifications'])
const hasUnread = computed(() => store.getters['users/hasUnreadNotifications'])
const notifications = computed(() => currentUser.value.notifications.latest)
const totalUnread = computed(() => currentUser.value.notifications.unread_count)

const localize = store.getters['users/localizeNotification']

function markAllRead() {
  store.dispatch('users/markAllNotificationsAsRead')
}
</script>
