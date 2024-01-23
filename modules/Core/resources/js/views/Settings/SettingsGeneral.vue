<template>
  <form @submit.prevent="saveGeneralSettings">
    <!-- General settings -->
    <ICard
      :header="$t('core::settings.general')"
      class="mb-3"
      footer-class="text-right"
      :overlay="!componentReady"
    >
      <p
        v-t="'core::app.logo.dark'"
        class="mb-3 text-sm font-medium text-neutral-700 dark:text-neutral-200"
      />

      <CropsAndUploadsImage
        name="logo_dark"
        :upload-url="`${$scriptConfig('apiURL')}/logo/dark`"
        :image="currentDarkLogo"
        :show-delete="Boolean(form.logo_dark)"
        :cropper-options="{ aspectRatio: null }"
        :choose-text="
          !currentDarkLogo
            ? $t('core::settings.choose_logo')
            : $t('core::app.change')
        "
        @cleared="deleteLogo('dark')"
        @success="refreshPage"
      >
        <template #image="{ src }">
          <img :src="src" class="h-8 w-auto" />
        </template>
      </CropsAndUploadsImage>

      <hr
        class="-mx-7 my-4 border-t border-neutral-200 dark:border-neutral-700"
      />

      <p
        v-t="'core::app.logo.light'"
        class="mb-3 text-sm font-medium text-neutral-700 dark:text-neutral-200"
      />

      <CropsAndUploadsImage
        name="logo_light"
        :show-delete="Boolean(form.logo_light)"
        :upload-url="`${$scriptConfig('apiURL')}/logo/light`"
        :image="currentLightLogo"
        :cropper-options="{ aspectRatio: null }"
        :choose-text="
          !currentLightLogo
            ? $t('core::settings.choose_logo')
            : $t('core::app.change')
        "
        @cleared="deleteLogo('light')"
        @success="refreshPage"
      >
        <template #image="{ src }">
          <img :src="src" class="h-8 w-auto" />
        </template>
      </CropsAndUploadsImage>

      <hr
        class="-mx-7 my-4 border-t border-neutral-200 dark:border-neutral-700"
      />

      <IFormGroup
        :label="$t('core::app.currency')"
        label-for="currency"
        class="w-auto xl:w-1/3"
      >
        <ICustomSelect
          v-model="form.currency"
          input-id="currency"
          :clearable="false"
          :options="currencies"
        />

        <IFormError :error="form.getError('currency')" />
      </IFormGroup>

      <IFormGroup
        :label="$t('core::settings.system_email')"
        label-for="system_email_account_id"
      >
        <div class="w-auto xl:w-1/3">
          <ICustomSelect
            input-id="system_email_account_id"
            :placeholder="
              !systemEmailAccountIsVisibleToCurrentUser &&
              systemEmailAccountIsConfiguredFromOtherUser
                ? $t('core::settings.system_email_configured')
                : ''
            "
            :model-value="systemEmailAccount"
            :disabled="
              !systemEmailAccountIsVisibleToCurrentUser &&
              systemEmailAccountIsConfiguredFromOtherUser
            "
            :options="emailAccounts"
            label="email"
            @option-selected="form.system_email_account_id = $event.id"
            @cleared="form.system_email_account_id = null"
          />
        </div>

        <IFormText
          class="mt-2 max-w-3xl"
          :text="$t('core::settings.system_email_info')"
        />

        <IFormError :error="form.getError('system_email_account_id')" />
      </IFormGroup>

      <IFormGroup
        :label="$t('core::app.allowed_extensions')"
        label-for="allowed_extensions"
        :description="$t('core::app.allowed_extensions_info')"
      >
        <IFormTextarea
          id="allowed_extensions"
          v-model="form.allowed_extensions"
          rows="2"
        />

        <IFormError :error="form.getError('allowed_extensions')" />
      </IFormGroup>

      <hr
        class="-mx-7 my-4 border-t border-neutral-200 dark:border-neutral-700"
      />

      <SettingsToggleItems class="-mx-6">
        <SettingsToggleItem
          v-model="form.require_calling_prefix_on_phones"
          :heading="$t('core::settings.phones.require_calling_prefix')"
          :description="$t('core::settings.phones.require_calling_prefix_info')"
          @change="submit"
        />
      </SettingsToggleItems>

      <hr
        class="-mx-7 my-4 border-t border-neutral-200 dark:border-neutral-700"
      />

      <div class="my-4 block">
        <IAlert
          class="mb-5"
          :text="$t('core::settings.update_user_account_info')"
        />

        <LocalizationFields
          class="w-auto xl:w-1/3"
          :exclude="['timezone', 'locale']"
          :form="form"
          @update:time-format="form.time_format = $event"
          @update:date-format="form.date_format = $event"
        />
      </div>

      <template #footer>
        <IButton
          type="submit"
          :disabled="form.busy"
          :text="$t('core::app.save')"
        />
      </template>
    </ICard>

    <!-- Company information -->
    <ICard
      :header="$t('core::settings.company_information')"
      class="mb-3"
      footer-class="text-right"
      :overlay="!componentReady"
    >
      <IFormGroup
        class="w-auto xl:w-1/3"
        :label="$t('core::app.company.name')"
        label-for="company_name"
      >
        <IFormInput id="company_name" v-model="form.company_name" />
      </IFormGroup>

      <IFormGroup
        class="w-auto xl:w-1/3"
        :label="$t('core::app.company.country')"
        label-for="company_country_id"
      >
        <ICustomSelect
          v-model="country"
          :options="countries"
          label="name"
          input-id="company_country_id"
          @option-selected="form.company_country_id = $event.id"
          @cleared="form.company_country_id = null"
        />
      </IFormGroup>

      <template #footer>
        <IButton
          type="submit"
          :disabled="form.busy"
          :text="$t('core::app.save')"
        />
      </template>
    </ICard>

    <ICard
      :header="$t('core::app.privacy_policy')"
      footer-class="text-right"
      :overlay="!componentReady"
    >
      <Editor v-model="form.privacy_policy" />

      <IFormText
        tabindex="-1"
        class="mt-2"
        :text="
          $t('core::settings.privacy_policy_info', {
            url: $scriptConfig('privacyPolicyUrl'),
          })
        "
      />

      <template #footer>
        <IButton
          type="submit"
          :disabled="form.busy"
          :text="$t('core::app.save')"
        />
      </template>
    </ICard>
  </form>
</template>

<script setup>
import { computed, shallowRef } from 'vue'
import { useStore } from 'vuex'
import find from 'lodash/find'
import map from 'lodash/map'

import LocalizationFields from '@/Core/components/LocalizationFields.vue'
import { useApp } from '@/Core/composables/useApp'

import { useSettings } from '../../composables/useSettings'

import SettingsToggleItem from './SettingsToggleItem.vue'
import SettingsToggleItems from './SettingsToggleItems.vue'

const store = useStore()
const { setting, resetStoreState } = useApp()

const {
  form,
  submit,
  isReady: componentReady,
  originalSettings,
} = useSettings()

const currencies = shallowRef([])
const countries = shallowRef([])
const country = shallowRef(null)

const emailAccounts = computed(() => store.getters['emailAccounts/accounts'])

const currentLightLogo = computed(() => setting('logo_light'))

const currentDarkLogo = computed(() => setting('logo_dark'))

const systemEmailAccount = computed(() =>
  find(emailAccounts.value, ['id', parseInt(form.system_email_account_id)])
)

const originalSystemEmailAccount = computed(() =>
  find(emailAccounts.value, [
    'id',
    parseInt(originalSettings.value.system_email_account_id),
  ])
)

const systemEmailAccountIsVisibleToCurrentUser = computed(
  () =>
    originalSettings.value.system_email_account_id &&
    originalSystemEmailAccount.value
)

const systemEmailAccountIsConfiguredFromOtherUser = computed(() => {
  // If the account cannot be found in the accounts list, this means the account is not visible
  // to the current logged-in user
  return (
    originalSettings.value.system_email_account_id &&
    !originalSystemEmailAccount.value
  )
})

function saveGeneralSettings() {
  submit(() => {
    if (
      form.require_calling_prefix_on_phones !==
      originalSettings.value.require_calling_prefix_on_phones
    ) {
      resetStoreState()
    }

    if (form.currency !== originalSettings.value.currency) {
      // Reload the page as the original currency is stored is in Innoclapps.config object
      refreshPage()
    }
  })
}

function refreshPage() {
  window.location.reload()
}

function deleteLogo(type) {
  const optionName = 'logo_' + type

  if (form[optionName]) {
    Innoclapps.request().delete(`/logo/${type}`).then(refreshPage)
  }
}

function fetchAndSetCurrencies() {
  Innoclapps.request('currencies').then(({ data }) => {
    currencies.value = map(data, (val, code) => code)
  })
}

function fetchAndSetCountries() {
  Innoclapps.request('countries').then(({ data }) => {
    countries.value = data

    if (form.company_country_id) {
      country.value = find(countries.value, [
        'id',
        parseInt(form.company_country_id),
      ])
    }
  })
}

store.dispatch('emailAccounts/fetch')
fetchAndSetCurrencies()
fetchAndSetCountries()
</script>
