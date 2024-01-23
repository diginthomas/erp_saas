<template>
  <form @submit.prevent="submitPusherIntegrationSettings">
    <ICard :overlay="!componentReady" footer-class="text-right">
      <template #header>
        <div class="flex items-center">
          <Icon
            v-if="isConfigured && componentReady"
            icon="CheckCircleSolid"
            class="mr-1 size-5 text-success-600"
          />

          <ICardHeading>Pusher</ICardHeading>
        </div>
      </template>

      <template #actions>
        <ILink href="https://dashboard.pusher.com" text="Pusher.com" />
      </template>

      <IAlert
        :show="!isConfigured && componentReady"
        variant="info"
        class="mb-6"
      >
        Receive notifications in real time without the need to manually refresh
        the page, after synchronization, automatically updates the calendar,
        total unread emails and new emails.
      </IAlert>

      <div class="sm:flex sm:space-x-4">
        <IFormGroup class="w-full" label="App ID" label-for="pusher_app_id">
          <IFormInput
            id="pusher_app_id"
            v-model="form.pusher_app_id"
          ></IFormInput>
        </IFormGroup>

        <IFormGroup class="w-full" label="App Key" label-for="pusher_app_key">
          <IFormInput
            id="pusher_app_key"
            v-model="form.pusher_app_key"
          ></IFormInput>
        </IFormGroup>
      </div>

      <div class="sm:flex sm:space-x-4">
        <IFormGroup
          class="w-full"
          label="App Secret"
          label-for="pusher_app_secret"
        >
          <IFormInput
            id="pusher_app_secret"
            v-model="form.pusher_app_secret"
            type="password"
          ></IFormInput>
        </IFormGroup>

        <IFormGroup class="w-full">
          <template #label>
            <div class="flex">
              <div class="mb-1 grow">
                <IFormLabel for="pusher_app_cluster">App Cluster</IFormLabel>
              </div>

              <ILink
                href="https://pusher.com/docs/clusters"
                text="https://pusher.com/docs/clusters"
              />
            </div>
          </template>

          <IFormInput
            id="pusher_app_cluster"
            v-model="form.pusher_app_cluster"
          ></IFormInput>
        </IFormGroup>
      </div>

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
import { computed } from 'vue'

import { useSettings } from '../../../composables/useSettings'

const {
  form,
  submit,
  isReady: componentReady,
  originalSettings,
} = useSettings()

const isConfigured = computed(
  () =>
    originalSettings.value.pusher_app_id &&
    originalSettings.value.pusher_app_key &&
    originalSettings.value.pusher_app_secret
)

function submitPusherIntegrationSettings() {
  submit(() => window.location.reload())
}
</script>
