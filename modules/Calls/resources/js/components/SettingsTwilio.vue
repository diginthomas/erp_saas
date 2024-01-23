<template>
  <ICard :overlay="!componentReady">
    <template #header>
      <div class="flex items-center">
        <Icon
          v-if="
            isConfigured && componentReady && !numbersRetrievalRequestInProgress
          "
          :icon="
            numbers.length === 0 ||
            selectedNumberHasNoVoiceCapabilities ||
            !isSecure
              ? 'XCircleSolid'
              : 'CheckCircleSolid'
          "
          :class="[
            'mr-1 size-5',
            numbers.length === 0 ||
            selectedNumberHasNoVoiceCapabilities ||
            !isSecure
              ? 'text-danger-500'
              : 'text-success-600',
          ]"
        />

        <ICardHeading>Twilio</ICardHeading>

        <IStepsCircle class="pointer-events-none ml-4">
          <IStepCircle :status="!showNumberConfig ? 'current' : 'complete'" />

          <IStepCircle :status="form.twilio_number ? 'complete' : ''" />

          <IStepCircle
            :status="isConfigured && form.twilio_number ? 'complete' : ''"
            is-last
          />
        </IStepsCircle>
      </div>
    </template>

    <template #actions>
      <IButton
        v-show="isConfigured"
        variant="danger"
        :text="$t('calls::twilio.disconnect')"
        @click="disconnect"
      />
    </template>

    <div class="lg:flex lg:space-y-4">
      <div class="w-full">
        <IAlert
          v-slot="{ variant }"
          :show="showAppUrlWarning"
          class="mb-10"
          variant="warning"
        >
          Your Twilio application URL does match your installation URL.

          <IAlertActions>
            <IButtonMinimal
              :variant="variant"
              text="Update URL"
              @click="updateTwiMLAppURL"
            />
          </IAlertActions>
        </IAlert>

        <IAlert class="mb-10" :show="!isSecure" variant="warning">
          Application must be served over HTTPS URL in order to use the Twilio
          integration.
        </IAlert>

        <div class="grid grid-cols-12 gap-2 lg:gap-4">
          <div class="col-span-12 lg:col-span-6">
            <IFormGroup>
              <template #label>
                <div class="mb-1 flex">
                  <div class="grow">
                    <IFormLabel for="twilio_account_sid">
                      Account SID
                    </IFormLabel>
                  </div>

                  <ILink href="https://www.twilio.com/console">
                    https://www.twilio.com/console
                  </ILink>
                </div>
              </template>

              <IFormInput
                id="twilio_account_sid"
                v-model="form.twilio_account_sid"
                autocomplete="off"
              />
            </IFormGroup>
          </div>

          <div class="col-span-12 lg:col-span-6">
            <IFormGroup>
              <template #label>
                <div class="mb-1 flex">
                  <div class="grow">
                    <IFormLabel for="twilio_auth_token">Auth Token</IFormLabel>
                  </div>

                  <ILink href="https://www.twilio.com/console">
                    https://www.twilio.com/console
                  </ILink>
                </div>
              </template>

              <IFormInput
                id="twilio_auth_token"
                v-model="form.twilio_auth_token"
                type="password"
                autocomplete="off"
              />
            </IFormGroup>
          </div>
        </div>

        <div
          class="mt-2 border-t border-neutral-200 pt-5 dark:border-neutral-600"
          :class="{
            'pointer-events-none opacity-50 blur-sm': !showNumberConfig,
          }"
        >
          <IFormLabel :label="$t('calls::twilio.number')" />

          <IAlert
            :show="selectedNumberHasNoVoiceCapabilities"
            class="my-3"
            variant="danger"
          >
            This phone number does not have enabled voice capabilities.
          </IAlert>

          <div class="mt-1 flex rounded-md shadow-sm">
            <div class="relative flex grow items-stretch focus-within:z-10">
              <div
                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
              >
                <Icon icon="Phone" class="size-5 text-neutral-400" />
              </div>

              <IFormSelect
                v-model="form.twilio_number"
                :disabled="!numbers.length"
                rounded="left"
                class="pl-10"
              >
                <option value=""></option>

                <option
                  v-for="number in numbers"
                  :key="number.phoneNumber"
                  :value="number.phoneNumber"
                >
                  {{ number.phoneNumber }}
                </option>
              </IFormSelect>
            </div>

            <IButton
              class="-ml-px shadow-sm"
              rounded="right"
              variant="white"
              :loading="numbersRetrievalRequestInProgress"
              :disabled="numbersRetrievalRequestInProgress"
              :text="$t('calls::twilio.retrieve_numbers')"
              @click="retrieveNumbers"
            />
          </div>
        </div>

        <div
          class="mt-5 border-t border-neutral-200 pt-5 dark:border-neutral-600"
          :class="{
            'pointer-events-none opacity-50 blur-sm': !showTwilioAppConfig,
          }"
        >
          <IFormLabel :label="$t('calls::twilio.app')" />

          <div class="mt-1 flex rounded-md shadow-sm">
            <IFormInput
              v-model="form.twilio_app_sid"
              class="grow focus:z-10"
              :disabled="true"
              rounded="left"
            />

            <IButton
              class="shrink-0 shadow-sm"
              :rounded="!hasAppSid ? 'right' : false"
              :disabled="
                appIsBeingCreated ||
                hasAppSid ||
                selectedNumberHasNoVoiceCapabilities
              "
              :text="$t('calls::twilio.create_app')"
              @click="createTwiMLApp"
            />

            <IButton
              v-if="hasAppSid"
              class="shadow-sm"
              variant="danger"
              rounded="right"
              icon="Trash"
              @click="deleteTwiMLApp"
            />
          </div>
        </div>
      </div>
    </div>

    <template v-if="isConfigured" #footer>
      <IButton
        class="mb-2"
        :disabled="selectedNumberHasNoVoiceCapabilities"
        :text="$t('core::app.save')"
        @click="save"
      />
    </template>
  </ICard>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import find from 'lodash/find'

import { useApp } from '@/Core/composables/useApp'
import { useForm } from '@/Core/composables/useForm'
import { isBlank } from '@/Core/utils'

const { t } = useI18n()
const { form } = useForm()
const { scriptConfig } = useApp()

const numbers = ref([])
const componentReady = ref(false)
const appIsBeingCreated = ref(false)
const numbersRetrievalRequestInProgress = ref(false)
const showAppUrlWarning = ref(false)

const isSecure = scriptConfig('is_secure')

const hasAuthToken = computed(() => !isBlank(form.twilio_auth_token))

const hasAccountSid = computed(() => !isBlank(form.twilio_account_sid))

const hasAppSid = computed(() => !isBlank(form.twilio_app_sid))

const showNumberConfig = computed(
  () => hasAuthToken.value && hasAccountSid.value
)

const showTwilioAppConfig = computed(() => !isBlank(form.twilio_number))

const isConfigured = computed(
  () => hasAuthToken.value && hasAccountSid.value && hasAppSid.value
)

const selectedNumber = computed(() =>
  find(numbers.value, ['phoneNumber', form.twilio_number])
)

const selectedNumberHasNoVoiceCapabilities = computed(() => {
  if (!selectedNumber.value) {
    return false
  }

  return selectedNumber.value.capabilities.voice === false
})

function save() {
  form.post('settings').then(() => {
    Innoclapps.success(t('core::settings.updated'))
    window.location.reload()
  })
}

function disconnect() {
  Innoclapps.request()
    .delete('/twilio')
    .then(() => {
      window.location.reload()
    })
}

function updateTwiMLAppURL() {
  Innoclapps.request()
    .put(`/twilio/app/${form.twilio_app_sid}`, {
      voiceUrl: scriptConfig('voip.endpoints.call'),
    })
    .then(() => {
      window.location.reload()
    })
}

function retrieveNumbers() {
  numbersRetrievalRequestInProgress.value = true

  Innoclapps.request('/twilio/numbers', {
    params: {
      account_sid: form.twilio_account_sid,
      auth_token: form.twilio_auth_token,
    },
  })
    .then(({ data }) => {
      numbers.value = data
    })
    .finally(() => (numbersRetrievalRequestInProgress.value = false))
}

/**
 * Get the TwiML app associated with the integration
 *
 * @return {Object}
 */
async function getTwiMLApp() {
  let { data } = await Innoclapps.request(`/twilio/app/${form.twilio_app_sid}`)

  return data
}

function createTwiMLApp() {
  appIsBeingCreated.value = true

  Innoclapps.request()
    .post('/twilio/app', {
      number: form.twilio_number,
      account_sid: form.twilio_account_sid,
      auth_token: form.twilio_auth_token,
      voiceMethod: 'POST',
      voiceUrl: scriptConfig('voip.endpoints.call'),
      friendlyName: 'Concord CRM',
    })
    .then(({ data }) => {
      form.twilio_app_sid = data.app_sid
    })
    .finally(() => (appIsBeingCreated.value = false))
}

function deleteTwiMLApp() {
  Innoclapps.confirm().then(deleteTwiMLAppWithoutConfirmation)
}

function deleteTwiMLAppWithoutConfirmation() {
  Innoclapps.request()
    .delete(`/twilio/app/${form.twilio_app_sid}`, {
      params: {
        account_sid: form.twilio_account_sid,
        auth_token: form.twilio_auth_token,
      },
    })
    .then(() => {
      form.twilio_app_sid = null
    })
}

function prepareComponent() {
  Innoclapps.request('/settings').then(({ data }) => {
    form.set({
      twilio_account_sid: data.twilio_account_sid,
      twilio_auth_token: data.twilio_auth_token,
      twilio_app_sid: data.twilio_app_sid,
      twilio_number: data.twilio_number,
    })

    componentReady.value = true

    if (hasAuthToken.value && hasAccountSid.value) {
      retrieveNumbers()

      if (hasAppSid.value) {
        getTwiMLApp()
          .then(app => {
            if (app.voiceUrl !== scriptConfig('voip.endpoints.call')) {
              showAppUrlWarning.value = true
            }
          })
          .catch(e => {
            // This means that the app does not exists in Twilio, in this case,
            // we will delete the app from the installation to forget the apps sid, see the TwilioAppController destroy method
            if (e.response.data.deleted) {
              deleteTwiMLAppWithoutConfirmation()
            } else {
              console.error(e)
            }
          })
      }
    }
  })
}

prepareComponent()
</script>
