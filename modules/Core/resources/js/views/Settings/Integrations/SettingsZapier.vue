<template>
  <ICard>
    <template #header>
      <ICardHeading>
        Zapier
        <IBadge variant="info" wrapper-class="ml-2">Beta</IBadge>
      </ICardHeading>
    </template>

    <div class="p-3 text-center">
      <img
        src="https://cdn.zapier.com/zapier/images/logos/zapier-logo.png"
        class="mx-auto h-16 w-auto"
      />

      <p class="mt-5 text-sm text-neutral-700 dark:text-white">
        Zapier integration is at <b>"Invite Only"</b>
        and
        <b>"Testing"</b> stage, we are inviting you to test the integration
        before it's available for everyone.
      </p>

      <p class="mt-1 text-sm text-neutral-700 dark:text-white">
        Before this, <b>we need to verify your purchase key</b> and after that
        we will share the Zapier invite link with you can try it!
      </p>
    </div>

    <div class="m-auto max-w-2xl">
      <div class="mt-5 flex justify-center rounded-md shadow-sm">
        <IFormInput
          id="purchase-key"
          v-model="purchaseKey"
          class="grow focus:z-10"
          rounded="left"
          placeholder="Enter your purchase key here"
        />

        <IButton
          class="-ml-px shrink-0 shadow-sm"
          variant="white"
          rounded="right"
          @click="getLink"
        >
          Get Integration Link
        </IButton>
      </div>
    </div>

    <div
      v-if="link"
      class="mt-6 flex items-center justify-center text-neutral-800 dark:text-neutral-300"
    >
      <span class="select-all font-medium">{{ link }}</span>

      <IButtonCopy
        v-i-tooltip="$t('core::app.copy')"
        class="ml-3"
        :text="link"
        :success-message="$t('core::app.copied')"
      />
    </div>
  </ICard>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

import { useApp } from '@/Core/composables/useApp'

const { scriptConfig } = useApp()

const purchaseKey = ref(scriptConfig('purchase_key'))
const link = ref(null)

/**
 * Get the Zapier Link
 *
 * Uses separate axios instance to prevent collision
 * with application error codes alerts and redirects
 */
function getLink() {
  axios
    .get(`https://www.concordcrm.com/zapier-link/${purchaseKey.value}`, {
      withCredentials: true,
    })
    .then(({ data }) => {
      link.value = data.link
    })
    .catch(error => {
      Innoclapps.error(error.response.data.error)
    })
}
</script>
