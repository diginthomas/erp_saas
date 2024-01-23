<template>
  <ICard
    :header="$t('core::settings.security.security')"
    no-body
    :overlay="!componentReady"
  >
    <SettingsToggleItems>
      <SettingsToggleItem
        v-model="form.disable_password_forgot"
        :heading="$t('core::settings.security.disable_password_forgot')"
        :description="
          $t('core::settings.security.disable_password_forgot_info')
        "
        @change="submit"
      />

      <SettingsToggleItem
        v-model="form.block_bad_visitors"
        :heading="$t('core::settings.security.block_bad_visitors')"
        :description="$t('core::settings.security.block_bad_visitors_info')"
        @change="submit"
      />
    </SettingsToggleItems>
  </ICard>
</template>

<script setup>
import SettingsToggleItem from '@/Core/views/Settings/SettingsToggleItem.vue'
import SettingsToggleItems from '@/Core/views/Settings/SettingsToggleItems.vue'

import { useSettings } from '../../../composables/useSettings'

const { form, submit, isReady: componentReady } = useSettings()
</script>
