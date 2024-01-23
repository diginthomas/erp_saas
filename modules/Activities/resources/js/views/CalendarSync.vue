<template>
  <MainLayout>
    <div class="mx-auto max-w-5xl">
      <ICard :overlay="!componentReady">
        <template #header>
          <ICardHeading class="flex items-center">
            {{ $t('activities::calendar.calendar_sync') }}
            <IBadge
              v-if="
                calendar &&
                !calendar.is_sync_disabled &&
                !calendar.is_sync_stopped
              "
              variant="success"
              wrapper-class="ml-2"
            >
              <Icon icon="Refresh" class="mr-1 size-3" />
              {{
                calendar.is_synchronizing_via_webhook ? 'Webhook' : 'Polling'
              }}
            </IBadge>
          </ICardHeading>
        </template>

        <IAlert
          v-if="oAuthCalendarsFetchError"
          variant="warning"
          dismissible
          class="mb-4"
          :text="oAuthCalendarsFetchError"
          @dismissed="oAuthCalendarsFetchError = null"
        />

        <div
          v-if="
            (!calendar ||
              (calendar && calendar.is_sync_disabled) ||
              !calendar.account) &&
            !accountConnectionInProgress
          "
        >
          <ConnectAccount />

          <div
            v-if="!calendar || (calendar && calendar.is_sync_disabled)"
            class="mb-4 block sm:inline-flex sm:items-center sm:space-x-1"
          >
            <IButton
              v-dialog="'calendarConnectNewAccountModal'"
              variant="secondary"
              :text="$t('core::oauth.add')"
            />

            <span
              v-show="hasOAuthAccounts"
              v-t="'core::oauth.or_choose_existing'"
              class="block text-neutral-800 sm:inline-flex dark:text-neutral-300"
            />
          </div>

          <div v-if="hasOAuthAccounts && !accountConnectionInProgress">
            <OAuthAccount
              v-for="account in oAuthAccounts"
              :key="account.id"
              class="mb-3"
              :account="account"
            >
              <IButton
                variant="secondary"
                class="ml-2"
                :disabled="account.requires_auth"
                :text="$t('core::oauth.connect')"
                @click="connect(account)"
              />
            </OAuthAccount>
          </div>
        </div>

        <div
          v-if="
            accountConnectionInProgress ||
            (calendar && !calendar.is_sync_disabled)
          "
          class="mt-4"
        >
          <OAuthAccount
            v-if="calendar && !calendar.is_sync_disabled && calendar.account"
            class="mb-5"
            :account="calendar.account"
          >
            <IButton
              variant="danger"
              class="ml-2"
              :disabled="syncStopInProgress"
              :loading="syncStopInProgress"
              @click="stopSync"
            >
              {{
                calendar.is_sync_stopped
                  ? $t('core::app.cancel')
                  : $t('core::oauth.stop_syncing')
              }}
            </IButton>

            <template v-if="calendar.sync_state_comment" #after-name>
              <span
                class="text-sm text-danger-500"
                v-text="calendar.sync_state_comment"
              />
            </template>
          </OAuthAccount>

          <div class="mb-3">
            <I18nT
              v-if="!calendar || (calendar && calendar.is_sync_disabled)"
              scope="global"
              keypath="activities::calendar.account_being_connected"
              tag="p"
              class="mb-8 text-neutral-800 dark:text-neutral-100"
            >
              <template #email>
                <span class="font-medium">
                  {{ accountConnectionInProgress.email }}
                </span>
              </template>
            </I18nT>

            <div class="grid grid-cols-12 gap-1 lg:gap-6">
              <div
                class="col-span-12 self-start lg:col-span-3 lg:flex lg:items-center lg:justify-end"
              >
                <p
                  v-t="'activities::calendar.calendar'"
                  class="text-sm font-medium text-neutral-800 dark:text-neutral-100"
                />
              </div>

              <div class="col-span-12 lg:col-span-4">
                <ICustomSelect
                  :options="availableOAuthCalendars"
                  :model-value="selectedCalendar"
                  label="title"
                  :loading="oAuthAccountCalendarsFetchRequestInProgress"
                  :disabled="connectedOAuthAccountRequiresAuthentication"
                  :placeholder="
                    oAuthAccountCalendarsFetchRequestInProgress
                      ? $t('core::app.loading')
                      : ''
                  "
                  :clearable="false"
                  @option-selected="form.calendar_id = $event.id"
                />

                <IFormText
                  :text="$t('activities::calendar.sync_support_only_primary')"
                  class="mt-2"
                />

                <IFormError :error="form.getError('calendar_id')" />
              </div>
            </div>
          </div>

          <div class="mb-3">
            <div class="grid grid-cols-12 gap-1 lg:gap-6">
              <div
                class="col-span-12 self-start lg:col-span-3 lg:flex lg:items-center lg:justify-end"
              >
                <p
                  v-t="'activities::calendar.save_events_as'"
                  class="text-sm font-medium text-neutral-800 dark:text-neutral-100"
                />
              </div>

              <div class="col-span-12 lg:col-span-4">
                <ICustomSelect
                  :options="activityTypesByName"
                  :model-value="selectedActivityTypeValue"
                  label="name"
                  :clearable="false"
                  @option-selected="form.activity_type_id = $event.id"
                />

                <IFormError :error="form.getError('activity_type_id')" />
              </div>
            </div>
          </div>

          <div class="grid grid-cols-12 gap-1 lg:gap-6">
            <div class="col-span-12 lg:col-span-3 lg:text-right">
              <p
                v-t="'activities::calendar.sync_activity_types'"
                class="text-sm font-medium text-neutral-800 dark:text-neutral-100"
              />
            </div>

            <div class="col-span-12 lg:col-span-4">
              <IFormCheckbox
                v-for="activityType in activityTypesByName"
                :key="activityType.id"
                v-model:checked="form.activity_types"
                :value="activityType.id"
                :label="activityType.name"
                name="activity_types"
              />

              <IFormError :error="form.getError('activity_types')" />
            </div>
          </div>
        </div>

        <template
          v-if="
            accountConnectionInProgress ||
            (calendar && !calendar.is_sync_disabled)
          "
          #footer
        >
          <div class="flex flex-col lg:flex-row lg:items-center">
            <div class="mb-2 grow lg:mb-0">
              <span
                v-if="startSyncFromText"
                class="text-sm text-neutral-800 dark:text-neutral-100"
              >
                <Icon
                  icon="ExclamationTriangle"
                  class="-mt-1 mr-1 inline-flex size-5"
                />
                {{ startSyncFromText }}
              </span>
            </div>

            <div class="space-x-2">
              <IButton
                v-if="
                  !calendar ||
                  (calendar && calendar.is_sync_disabled) ||
                  calendar.is_sync_stopped
                "
                :disabled="form.busy"
                :text="$t('core::app.cancel')"
                @click="accountConnectionInProgress = null"
              />

              <IButton
                v-show="!calendar || (calendar && !calendar.is_sync_stopped)"
                variant="secondary"
                :disabled="form.busy"
                :loading="form.busy"
                @click="save"
              >
                {{
                  !calendar ||
                  (calendar && calendar.is_sync_disabled) ||
                  calendar.is_sync_stopped
                    ? $t('core::oauth.start_syncing')
                    : $t('core::app.save')
                }}
              </IButton>

              <IButton
                v-show="calendar && calendar.is_sync_stopped"
                variant="secondary"
                :disabled="
                  form.busy || connectedOAuthAccountRequiresAuthentication
                "
                :loading="form.busy"
                :text="$t('activities::calendar.reconfigure')"
                @click="save"
              />
            </div>
          </div>
        </template>
      </ICard>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import filter from 'lodash/filter'
import orderBy from 'lodash/orderBy'

import { useApp } from '@/Core/composables/useApp'
import { useDates } from '@/Core/composables/useDates'
import { useForm } from '@/Core/composables/useForm'
import OAuthAccount from '@/Core/views/OAuth/OAuthAccount.vue'

import { useActivityTypes } from '../composables/useActivityTypes'

import ConnectAccount from './CalendarSyncConnectAccount.vue'

const { t } = useI18n()
const route = useRoute()

const { localizedDateTime } = useDates()
const { scriptConfig } = useApp()

const componentReady = ref(false)
const oAuthAccountCalendarsFetchRequestInProgress = ref(false)
const oAuthCalendarsFetchError = ref(null)
const accountConnectionInProgress = ref(null)
const calendar = ref(null)
const syncStopInProgress = ref(false)
const oAuthAccounts = ref([])
const availableOAuthCalendars = ref([])

const { form } = useForm()

const { typesByName: activityTypesByName } = useActivityTypes()

const hasOAuthAccounts = computed(() => oAuthAccounts.value.length > 0)

const selectedActivityTypeValue = computed(() =>
  activityTypesByName.value.find(type => type.id == form.activity_type_id)
)

const selectedCalendar = computed(() =>
  availableOAuthCalendars.value.find(
    calendar => calendar.id == form.calendar_id
  )
)

const connectedOAuthAccountRequiresAuthentication = computed(() => {
  if (!calendar.value || !calendar.value.account) {
    return false
  }

  return calendar.value.account.requires_auth
})

const startSyncFromText = computed(() => {
  // No connection nor calendar, do nothing
  if (
    (!accountConnectionInProgress.value && !calendar.value) ||
    (calendar.value && calendar.value.is_sync_stopped)
  ) {
    return ''
  }

  // If the calendar is not yet created, this means that we don't have any
  // sync history and just will show that only future events will be synced for the selected calendar
  if (!calendar.value) {
    return t('activities::calendar.only_future_events_will_be_synced')
  }

  // Let's try to find if the current selected calendar was previously configured
  // as calendar to sync, if found, the initial start_sync_from will be used to actual start syncing the events
  // in case if there were previously synced events and then the user changed the calendar and now want to get back again // on this calendar, we need to sync the previously synced events as well
  const previouslyUsedCalendar = filter(calendar.value.previously_used, [
    'calendar_id',
    form.calendar_id,
  ])

  // User does not want to sync and he is just looking at the configuration screen
  // for a configured account to sync, in this case, we will just show from what date the events are synced
  if (
    calendar.value.calendar_id === form.calendar_id &&
    !accountConnectionInProgress.value
  ) {
    return t('activities::calendar.events_being_synced_from', {
      date: localizedDateTime(calendar.value.start_sync_from),
    })
  }

  // Finally, we will check if there is account connection in progress or the actual form
  // calendar id is not equal with the currrent calendar id that the user selected
  if (
    accountConnectionInProgress.value ||
    calendar.value.calendar_id !== form.calendar_id
  ) {
    // If history found, use the start_sync_from date from the history
    if (previouslyUsedCalendar.length > 0) {
      return t('activities::calendar.events_will_sync_from', {
        date: localizedDateTime(previouslyUsedCalendar[0].start_sync_from),
      })
    } else if (calendar.value.calendar_id === form.calendar_id) {
      // Otherwise the user has selected a calendar that was first time selected and now we will just use
      // the start_sync_from date from the first time when the calendar was connected
      return t('activities::calendar.events_will_sync_from', {
        date: localizedDateTime(calendar.value.start_sync_from),
      })
    } else {
      return t('activities::calendar.only_future_events_will_be_synced')
    }
  }

  return ''
})

function getLatestCreatedOAuthAccount() {
  return orderBy(oAuthAccounts.value, account => new Date(account.created_at), [
    'desc',
  ])[0]
}

function setInitialForm() {
  form.clear().set({
    access_token_id: null,
    activity_type_id: scriptConfig('activities.default_activity_type_id'),
    activity_types: activityTypesByName.value.map(type => type.id),
    calendar_id: null,
  })
}

/**
 * Start account sync connection
 */
function connect(account) {
  accountConnectionInProgress.value = account
  form.fill('access_token_id', account.id)

  retrieveOAuthAccountCalendars(account.id).then(oAuthCalendars => {
    form.set('calendar_id', oAuthCalendars[0].id)
  })
}

function save() {
  form.post('/calendar/account').then(connectedCalendar => {
    calendar.value = connectedCalendar
    accountConnectionInProgress.value = null
  })
}

function stopSync() {
  syncStopInProgress.value = true

  Innoclapps.request()
    .delete('/calendar/account')
    .then(({ data }) => {
      calendar.value = data
      accountConnectionInProgress.value = null
      setInitialForm()
    })
    .finally(() => (syncStopInProgress.value = false))
}

async function retrieveOAuthAccountCalendars(id) {
  oAuthAccountCalendarsFetchRequestInProgress.value = true
  oAuthCalendarsFetchError.value = null

  try {
    let { data } = await Innoclapps.request(`/calendars/${id}`)
    availableOAuthCalendars.value = data

    return data
  } catch (error) {
    oAuthCalendarsFetchError.value = error.response.data.message
    throw error
  } finally {
    oAuthAccountCalendarsFetchRequestInProgress.value = false
  }
}

function fillForm(forCalendar) {
  form.set({
    activity_type_id: forCalendar.activity_type_id,
    activity_types: forCalendar.activity_types,
    // Perhaps account deleted?
    access_token_id: forCalendar.account ? forCalendar.account.id : null,
  })
}

setInitialForm()

Promise.all([
  Innoclapps.request('oauth/accounts'),
  Innoclapps.request('calendar/account'),
]).then(values => {
  oAuthAccounts.value = values[0].data
  calendar.value = values[1].data

  if (calendar.value) {
    fillForm(calendar.value)
  }

  if (route.query.viaOAuth) {
    connect(getLatestCreatedOAuthAccount())
  } else if (
    calendar.value.account &&
    !connectedOAuthAccountRequiresAuthentication.value
  ) {
    // perhaps deleted or requires auth?
    retrieveOAuthAccountCalendars(calendar.value.account.id).then(() => {
      form.set('calendar_id', calendar.value.calendar_id)
    })
  }

  componentReady.value = true
})
</script>
