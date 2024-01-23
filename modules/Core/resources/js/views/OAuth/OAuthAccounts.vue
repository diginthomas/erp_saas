<template>
  <MainLayout>
    <div class="mx-auto max-w-5xl">
      <ICard
        :header="$t('core::oauth.connected_accounts')"
        :overlay="isLoading"
      >
        <div v-if="hasAccounts" class="space-y-3">
          <OAuthAccount
            v-for="account in accounts"
            :key="account.id"
            :account="account"
          >
            <IButton
              :text="$t('core::oauth.re_authenticate')"
              @click="reAuthenticate(account)"
            />

            <IButton
              v-if="account.authorizations.delete"
              class="ml-1"
              variant="danger"
              icon="Trash"
              @click="destroy(account.id)"
            />
          </OAuthAccount>
        </div>

        <div v-else v-show="!isLoading" class="text-center">
          <Icon icon="EmojiSad" class="mx-auto size-12 text-neutral-400" />

          <h3
            v-t="'core::oauth.no_accounts'"
            class="mt-2 text-sm font-medium text-neutral-800 dark:text-white"
          />
        </div>
      </ICard>
    </div>

    <RouterView />
  </MainLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import { useStore } from 'vuex'
import findIndex from 'lodash/findIndex'

import { useApp } from '@/Core/composables/useApp'
import { useLoader } from '@/Core/composables/useLoader'

import OAuthAccount from './OAuthAccount.vue'

const store = useStore()
const route = useRoute()
const { t } = useI18n()
const { appUrl } = useApp()
const { setLoading, isLoading } = useLoader()

const accounts = ref([])
const hasAccounts = computed(() => accounts.value.length > 0)

function reAuthenticate(account) {
  window.location.href = `${appUrl}/${account.type}/connect`
}

async function destroy(id) {
  await Innoclapps.confirm(t('core::oauth.delete_warning'))

  await Innoclapps.request().delete(`oauth/accounts/${id}`)

  const accountIndex = findIndex(accounts.value, ['id', parseInt(id)])
  accounts.value.splice(accountIndex, 1)

  store.commit('emailAccounts/RESET')
  Innoclapps.success(t('core::oauth.deleted'))
}

async function fetch() {
  setLoading(true)

  try {
    const { data } = await Innoclapps.request('oauth/accounts')

    accounts.value = data

    if (route.query.reconnect) {
      reAuthenticate(data.find(account => account.id == route.query.reconnect))
    }
  } finally {
    setLoading(false)
  }
}

fetch()
</script>
