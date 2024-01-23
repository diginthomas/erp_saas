<template>
  <MainLayout>
    <template #actions>
      <NavbarSeparator v-show="hasAccounts" class="hidden lg:block" />

      <IButton
        v-show="hasAccounts"
        variant="primary"
        icon="Inbox"
        :to="{ name: 'inbox' }"
        :text="$t('mailclient::inbox.inbox')"
      />
    </template>

    <ICard
      v-show="hasAccounts"
      :overlay="isLoading"
      class="mx-auto max-w-7xl"
      :header="$t('mailclient::mail.account.accounts')"
      actions-class="w-full sm:w-auto"
      no-body
    >
      <template #actions>
        <div
          class="mt-4 flex w-full flex-col space-y-1 sm:mt-0 sm:w-auto sm:flex-row sm:justify-end sm:space-x-2 sm:space-y-0"
        >
          <span
            v-i-tooltip="
              $t(
                $gate.isRegularUser()
                  ? 'users::user.not_authorized'
                  : 'mailclient::mail.account.create_shared_info'
              )
            "
            class="grid sm:inline"
          >
            <IButton
              variant="white"
              icon="Share"
              :disabled="$gate.isRegularUser()"
              :text="$t('mailclient::mail.account.connect_shared')"
              @click="createShared"
            />
          </span>

          <IButton
            icon="User"
            variant="white"
            :text="$t('mailclient::mail.account.connect_personal')"
            @click="createPersonal"
          />
        </div>
      </template>

      <TransitionGroup
        class="divide-y divide-neutral-200 dark:divide-neutral-700"
        name="flip-list"
        tag="ul"
      >
        <li v-for="account in accounts" :key="account.id">
          <div
            v-if="account.is_sync_stopped || account.requires_auth"
            class="flex items-center justify-between border-b border-warning-100 bg-warning-50 px-4 py-2"
          >
            <div class="text-sm font-medium text-warning-700">
              <p
                v-if="account.requires_auth"
                v-t="'core::oauth.requires_authentication'"
              />

              <p
                v-if="account.is_sync_stopped"
                v-text="account.sync_state_comment"
              />
            </div>

            <IButtonMinimal
              v-if="account.requires_auth"
              variant="warning"
              class="shrink-0 self-start"
              :text="$t('core::oauth.re_authenticate')"
              @click="reAuthenticate(account)"
            />
          </div>

          <div
            :class="[
              'flex items-center px-4 py-4 sm:px-6',
              {
                'opacity-70':
                  account.is_sync_stopped || account.is_sync_disabled,
              },
            ]"
          >
            <div
              class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between"
            >
              <div class="truncate">
                <div class="flex text-sm">
                  <component
                    :is="account.authorizations.update ? 'a' : 'p'"
                    :class="[
                      'truncate font-medium text-primary-600 dark:text-primary-400',
                      account.authorizations.update ? 'cursor-pointer' : '',
                    ]"
                    @click="
                      account.authorizations.update
                        ? redirectToAccountEdit(account)
                        : ''
                    "
                  >
                    {{ account.email }}
                    {{ account.alias_email ? `(${account.alias_email})` : '' }}
                  </component>
                </div>

                <div class="mt-2 flex">
                  <div class="flex items-center space-x-2 text-sm">
                    <GoogleIcon
                      v-if="account.connection_type === 'Gmail'"
                      class="size-5 shrink-0"
                    />

                    <OutlookIcon
                      v-else-if="account.connection_type === 'Outlook'"
                      class="size-5 shrink-0"
                    />

                    <IBadge
                      v-if="account.authorizations.update"
                      variant="info"
                      :text="account.connection_type"
                    />

                    <IBadge
                      :variant="account.is_personal ? 'neutral' : 'info'"
                      :text="$t('mailclient::mail.account.' + account.type)"
                    />

                    <IBadge
                      v-if="account.is_primary || accounts.length === 1"
                      variant="info"
                      :text="$t('mailclient::mail.account.is_primary')"
                    />
                  </div>
                </div>
              </div>

              <div class="mt-4 shrink-0 sm:ml-5 sm:mt-0">
                <div class="flex space-x-3">
                  <IFormToggle
                    v-if="accounts.length > 1"
                    :disabled="account.is_sync_stopped"
                    :model-value="account.is_primary"
                    :label="$t('mailclient::mail.account.is_primary')"
                    @change="togglePrimaryState($event, account)"
                  />

                  <IFormToggle
                    v-show="
                      !account.is_sync_stopped && account.authorizations.update
                    "
                    :model-value="account.is_sync_disabled"
                    :label="$t('mailclient::mail.disable_sync')"
                    @change="toggleDisabledSyncState(account)"
                  />
                </div>
              </div>
            </div>

            <IMinimalDropdown
              v-if="
                account.authorizations.update || account.authorizations.delete
              "
              class="ml-5 shrink-0 self-start sm:self-auto"
            >
              <IDropdownItem
                v-if="account.authorizations.update"
                :text="$t('core::app.edit')"
                @click="redirectToAccountEdit(account)"
              />

              <IDropdownItem
                v-if="account.authorizations.delete"
                :text="$t('core::app.delete')"
                @click="destroy(account.id)"
              />
            </IMinimalDropdown>
          </div>
        </li>
      </TransitionGroup>
    </ICard>

    <IOverlay v-if="!hasAccounts" :show="isLoading" class="m-auto max-w-5xl">
      <div v-show="!isLoading" class="mx-auto max-w-2xl p-4 sm:p-6">
        <h2
          v-t="'mailclient.mail.account.no_accounts_configured'"
          class="text-xl font-medium text-neutral-800 dark:text-neutral-200"
        />

        <p
          v-t="'mailclient.mail.account.no_accounts_configured_info'"
          class="text-sm text-neutral-500 dark:text-neutral-300"
        />

        <ul
          role="list"
          class="mt-6 grid grid-cols-1 gap-6 border-y border-neutral-200 py-6 lg:grid-cols-2 dark:border-neutral-600"
        >
          <li
            v-for="(item, itemIdx) in emptyStateItems"
            :key="itemIdx"
            class="flow-root"
          >
            <div
              class="relative -m-2 flex items-center space-x-4 rounded-xl p-2"
            >
              <div
                class="flex h-12 w-14 shrink-0 items-center justify-center rounded-lg border border-primary-200 bg-primary-50"
              >
                <Icon :icon="item.icon" class="size-6 text-primary-600" />
              </div>

              <div>
                <p
                  class="mt-1 text-sm text-neutral-600 dark:text-neutral-200"
                  v-text="item.description"
                />
              </div>
            </div>
          </li>
        </ul>

        <div class="mt-6 space-y-1 sm:space-x-2 sm:text-right">
          <span
            v-i-tooltip="
              $t(
                $gate.isRegularUser()
                  ? 'users::user.not_authorized'
                  : 'mailclient::mail.account.create_shared_info'
              )
            "
            class="inline-block w-full sm:w-auto"
          >
            <IButton
              variant="primary"
              size="md"
              class="sm:w-justify-around w-full justify-center sm:w-auto"
              :disabled="$gate.isRegularUser()"
              icon="Share"
              :text="$t('mailclient::mail.account.connect_shared')"
              @click="createShared"
            />
          </span>

          <IButton
            variant="primary"
            size="md"
            icon="User"
            class="sm:w-justify-around w-full justify-center sm:w-auto"
            :text="$t('mailclient::mail.account.connect_personal')"
            @click="createPersonal"
          />
        </div>
      </div>
    </IOverlay>

    <RouterView />
  </MainLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import { useStore } from 'vuex'

import GoogleIcon from '@/Core/components/Icons/GoogleIcon.vue'
import OutlookIcon from '@/Core/components/Icons/OutlookIcon.vue'
import { useLoader } from '@/Core/composables/useLoader'

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const store = useStore()

const { setLoading, isLoading } = useLoader(true)

const accounts = computed(() => store.getters['emailAccounts/accounts'])
const latestAccount = computed(() => store.getters['emailAccounts/latest'])
const hasAccounts = computed(() => accounts.value.length > 0)

const emptyStateItems = [
  {
    description: t('mailclient::mail.account.featured.sync'),
    icon: 'Refresh',
  },
  {
    description: t('mailclient::mail.account.featured.save_time'),
    icon: 'DocumentAdd',
  },
  {
    description: t('mailclient::mail.account.featured.placeholders'),
    icon: 'CodeBracket',
  },
  {
    description: t('mailclient::mail.account.featured.signature'),
    icon: 'Pencil',
  },
  {
    description: t('mailclient::mail.account.featured.associations', {
      resources:
        t('contacts::contact.contacts') +
        ', ' +
        t('contacts::company.companies'),
      resource: t('deals::deal.deals'),
    }),
    icon: 'Mail',
  },
  {
    description: t('mailclient::mail.account.featured.types'),
    icon: 'CheckCircle',
  },
]

async function destroy(id) {
  await Innoclapps.confirm(t('mailclient::mail.account.delete_warning'))
  await Innoclapps.request().delete(`/mail/accounts/${id}`)

  store.commit('emailAccounts/REMOVE', id)
  Innoclapps.success(t('mailclient::mail.account.deleted'))
}

function createShared() {
  Innoclapps.confirm({
    message: t('mailclient::mail.account.create_shared_confirmation_message'),
    title: false,
    icon: 'QuestionMarkCircle',
    iconWrapperColorClass: 'bg-info-100',
    iconColorClass: 'text-info-400',
    html: true,
    confirmText: t('core::app.continue'),
    confirmVariant: 'secondary',
  }).then(() =>
    router.push({
      name: 'create-email-account',
      query: {
        type: 'shared',
      },
    })
  )
}

function createPersonal() {
  router.push({
    name: 'create-email-account',
    query: {
      type: 'personal',
    },
  })
}

function redirectToAccountEdit(account) {
  router.push({
    name: 'edit-email-account',
    params: { id: account.id },
  })
}

function togglePrimaryState(isPrimary, account) {
  if (isPrimary) {
    store.dispatch('emailAccounts/setPrimary', account.id)
  } else {
    store.dispatch('emailAccounts/removePrimary')
  }
}

function toggleDisabledSyncState(account) {
  let action = account.is_sync_disabled ? 'enable' : 'disable'
  store.dispatch(`emailAccounts/${action}Sync`, account.id)
}

function reAuthenticate(account) {
  if (account.connection_type === 'Imap') {
    redirectToAccountEdit(account)
  } else {
    window.location.href =
      store.getters['emailAccounts/OAuthConnectUrl'](
        account.connection_type,
        account.type
      ) + '?re_auth=1'
  }
}

store.dispatch('emailAccounts/fetch').finally(() => {
  setLoading(false)

  if (route.query.viaOAuth) {
    redirectToAccountEdit(latestAccount.value)
  }
})
</script>
