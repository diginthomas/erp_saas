<template>
  <IModal
    id="calendarConnectNewAccountModal"
    size="sm"
    hide-footer
    :title="$t('core::oauth.connect_new_account')"
  >
    <div class="py-4">
      <p
        v-t="'activities::calendar.choose_oauth_account'"
        class="mb-5 text-center text-neutral-800 dark:text-neutral-200"
      />

      <div class="flex justify-center space-x-2">
        <div
          class="flex cursor-pointer flex-col items-center space-y-1 rounded-lg border border-neutral-200 px-5 py-3 shadow-sm hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-800"
          @click="connectOAuthAccount('google')"
        >
          <IPopover
            ref="googlePopoverRef"
            class="max-w-xs sm:max-w-sm"
            :disabled="isGoogleApiConfigured()"
            placement="top"
          >
            <GoogleIcon
              @click="
                isGoogleApiConfigured()
                  ? connectOAuthAccount('google')
                  : undefined
              "
            />

            <template #popper>
              <div class="p-4 text-sm">
                <p class="whitespace-pre-wrap">
                  {{ $t('activities::calendar.missing_google_integration') }}
                </p>

                <ILink
                  v-if="$gate.isSuperAdmin()"
                  :text="$t('core::settings.go_to_settings')"
                  class="mt-2 block text-right"
                  :to="{ name: 'settings-integrations-google' }"
                />
              </div>
            </template>
          </IPopover>

          <span
            class="text-sm font-medium text-neutral-600 dark:text-neutral-300"
          >
            Google Calendar
          </span>
        </div>

        <div
          class="flex cursor-pointer flex-col items-center space-y-1 rounded-lg border border-neutral-200 px-5 py-3 shadow-sm hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-800"
          @click="connectOAuthAccount('microsoft')"
        >
          <IPopover
            ref="microsoftPopoverRef"
            placement="top"
            class="max-w-xs sm:max-w-sm"
            :disabled="isMicrosoftGraphConfigured()"
          >
            <OutlookIcon
              @click="
                isMicrosoftGraphConfigured()
                  ? connectOAuthAccount('microsoft')
                  : undefined
              "
            />

            <template #popper>
              <div class="p-4 text-sm">
                <p class="whitespace-pre-wrap">
                  {{ $t('activities::calendar.missing_outlook_integration') }}
                </p>

                <ILink
                  v-if="$gate.isSuperAdmin()"
                  :text="$t('core::settings.go_to_settings')"
                  class="mt-2 block text-right"
                  :to="{ name: 'settings-integrations-microsoft' }"
                />
              </div>
            </template>
          </IPopover>

          <span
            class="text-sm font-medium text-neutral-600 dark:text-neutral-300"
          >
            Outlook Calendar
          </span>
        </div>
      </div>
    </div>
  </IModal>
</template>

<script setup>
import { ref } from 'vue'

import GoogleIcon from '@/Core/components/Icons/GoogleIcon.vue'
import OutlookIcon from '@/Core/components/Icons/OutlookIcon.vue'
import { useApp } from '@/Core/composables/useApp'

const { isGoogleApiConfigured, isMicrosoftGraphConfigured, scriptConfig } =
  useApp()

const googlePopoverRef = ref(null)
const microsoftPopoverRef = ref(null)

function connectOAuthAccount(provider) {
  if (provider === 'google' && !isGoogleApiConfigured()) {
    googlePopoverRef.value.show()

    return
  } else if (provider === 'microsoft' && !isMicrosoftGraphConfigured()) {
    microsoftPopoverRef.value.show()

    return
  }

  window.location.href = `${scriptConfig(
    'url'
  )}/calendar/sync/${provider}/connect`
}
</script>
