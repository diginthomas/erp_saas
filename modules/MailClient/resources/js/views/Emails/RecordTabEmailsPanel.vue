<template>
  <ITabPanel :lazy="!dataLoadedFirstTime" @activated.once="handleActivation">
    <div
      class="-mt-[20px] mb-3 rounded-b-md border border-neutral-200 bg-white sm:mb-7 dark:border-neutral-700 dark:bg-neutral-900"
    >
      <div class="px-4 py-5 sm:p-6">
        <div class="sm:flex sm:items-start sm:justify-between">
          <div>
            <h3
              v-t="'mailclient.mail.manage_emails'"
              class="text-base/6 font-medium text-neutral-900 dark:text-white"
            />

            <div
              class="mt-2 max-w-xl text-sm text-neutral-500 dark:text-neutral-200"
            >
              <p v-t="'mailclient.mail.info'" />
            </div>
          </div>

          <div class="mt-5 sm:ml-6 sm:mt-0 sm:flex sm:shrink-0 sm:items-center">
            <div
              v-i-tooltip="
                !hasAccountsConfigured
                  ? $t('mailclient::mail.account.integration_not_configured')
                  : null
              "
            >
              <IButton
                icon="Plus"
                :disabled="!hasAccountsConfigured"
                :text="$t('mailclient::mail.create')"
                @click="compose(true)"
              />
            </div>
          </div>
        </div>

        <div class="mt-3 space-x-3 text-sm font-medium">
          <ILink
            :to="{ name: 'email-accounts-index' }"
            :text="$t('mailclient::mail.account.connect')"
          />

          <ILink
            v-show="countScheduledEmails && countScheduledEmails > 0"
            @click="scheduledEmailsVisible = true"
          >
            {{ $t('mailclient::schedule.scheduled_emails') }}
            ({{ countScheduledEmails }})
          </ILink>
        </div>

        <SearchInput
          v-show="hasEmails || search"
          v-model="search"
          class="mt-2"
          @input="performSearch"
        />
      </div>
    </div>

    <ComposeMessage
      ref="composeRef"
      :visible="isComposing"
      :resource-name="resourceName"
      :resource-id="resourceId"
      :related-resource="resource"
      :associations="newMessageAdditionalAssociations"
      :to="to"
      @hidden="compose(false)"
    />

    <ScheduledEmailsModalTable
      v-model:visible="scheduledEmailsVisible"
      :request-query-string="{
        via_resource: resourceName,
        via_resource_id: resourceId,
      }"
      @sent="handleScheduledSent"
      @deleted="retrieveCountOfScheduledEmails"
    />

    <div class="space-y-3">
      <RelatedEmail
        v-for="email in emails"
        :key="email.id"
        :email="email"
        :via-resource="resourceName"
        :via-resource-id="resourceId"
        :related-resource="resource"
      />
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
import { computed, inject, nextTick, ref, toRef } from 'vue'
import { useRoute } from 'vue-router'
import { useStore } from 'vuex'
import { watchOnce } from '@vueuse/core'
import orderBy from 'lodash/orderBy'

import InfinityLoader from '@/Core/components/InfinityLoader.vue'
import { useGlobalEventListener } from '@/Core/composables/useGlobalEventListener'
import { useRecordTab } from '@/Core/composables/useRecordTab'

import ScheduledEmailsModalTable from '../../components/ScheduledEmailsModalTable.vue'
import { useMessageAssociations } from '../../composables/useMessageAssociations'

import ComposeMessage from './ComposeMessage.vue'
import RelatedEmail from './RelatedEmail.vue'

const props = defineProps({
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: [String, Number] },
  resource: { required: true, type: Object },
  scrollElement: { type: String },
})

const synchronizeResource = inject('synchronizeResource')

const store = useStore()
const route = useRoute()

const isComposing = ref(false)
const scheduledEmailsVisible = ref(false)
const infinityRef = ref(null)
const composeRef = ref(null)
const timelineRelation = 'emails'
const countScheduledEmails = ref(null)

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
  refresh,
} = useRecordTab({
  resourceName: props.resourceName,
  resource: toRef(props, 'resource'),
  scrollElement: props.scrollElement,
  infinityRef,
  synchronizeResource,
  timelineRelation,
})

const { newMessageAdditionalAssociations } = useMessageAssociations(
  toRef(props, 'resourceName'),
  toRef(props, 'resource')
)

const hasAccountsConfigured = computed(
  () => store.getters['emailAccounts/hasConfigured']
)

const emails = computed(() =>
  orderBy(searchResults.value || props.resource.emails, 'date', 'desc')
)

const hasEmails = computed(() => emails.value.length > 0)

const to = computed(() => {
  // First check if there is an email property in the resource data
  if (props.resource.email) {
    return createToArrayFromResource(props.resource, props.resourceName)
  }

  // Vue 3, before navigating, it's hitting this computed but there is no data
  // TODO, research more
  if (Object.keys(props.resource).length === 0) {
    return []
  }

  // Next we will try to provide associations and email to send email from
  // the related resources, e.q. when viewing contact and the contact has no email
  // we will try to provide an email from the contact latest company and so on.
  if (props.resourceName === 'contacts') {
    if (props.resource.companies[0]) {
      return createToArrayFromResource(props.resource.companies[0], 'companies')
    }
  } else if (props.resourceName === 'companies') {
    if (props.resource.contacts[0]) {
      return createToArrayFromResource(props.resource.contacts[0], 'contacts')
    }
  } else if (props.resourceName === 'deals') {
    if (props.resource.contacts[0]) {
      return createToArrayFromResource(props.resource.contacts[0], 'contacts')
    } else if (props.resource.companies[0]) {
      return createToArrayFromResource(props.resource.companies[0], 'companies')
    }
  }

  return []
})

function handleScheduledSent() {
  retrieveCountOfScheduledEmails()
  refresh()
}

async function retrieveCountOfScheduledEmails() {
  const { data: scheduledData } = await Innoclapps.request(
    '/emails/scheduled/count',
    {
      params: {
        via_resource: props.resourceName,
        via_resource_id: props.resourceId,
      },
    }
  )

  countScheduledEmails.value = scheduledData.count
}

function handleActivation() {
  loadData()

  if (countScheduledEmails.value === null) {
    retrieveCountOfScheduledEmails()
  }
}

function createToArrayFromResource(resource, resourceName) {
  return resource.email
    ? [
        {
          address: resource.email,
          name: resource.display_name,
          id: resource.id,
          resourceName,
        },
      ]
    : []
}

function compose(state = true) {
  isComposing.value = state

  if (state) {
    nextTick(() => composeRef.value.subjectRef.focus())
  }
}

function handleSent(email) {
  synchronizeResource({ [timelineRelation]: [email] })
}

if (route.query.resourceId && route.query.section === timelineRelation) {
  // Wait till the data is loaded for the first time and the
  // elements are added to the document so we can have a proper scroll
  watchOnce(dataLoadedFirstTime, () => {
    focusToAssociateableElement(route.query.resourceId, 'email')
  })
}

useGlobalEventListener('email-accounts-sync-finished', refresh)
useGlobalEventListener('email-sent', handleSent)
useGlobalEventListener('email-scheduled', retrieveCountOfScheduledEmails)
</script>
