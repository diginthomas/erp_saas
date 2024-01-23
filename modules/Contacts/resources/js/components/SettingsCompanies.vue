<template>
  <ICard
    :header="$t('contacts::company.companies')"
    no-body
    :overlay="!componentReady"
  >
    <SettingsToggleItems>
      <SettingsToggleItem
        v-model="form.auto_associate_company_to_contact"
        :heading="
          $t('contacts::company.settings.automatically_associate_with_contacts')
        "
        :description="
          $t(
            'contacts::company.settings.automatically_associate_with_contacts_info'
          )
        "
        @change="submit"
      />
    </SettingsToggleItems>
  </ICard>
</template>

<script setup>
import { useSettings } from '@/Core/composables/useSettings'
import SettingsToggleItem from '@/Core/views/Settings/SettingsToggleItem.vue'
import SettingsToggleItems from '@/Core/views/Settings/SettingsToggleItems.vue'

const { form, submit, isReady: componentReady } = useSettings()
</script>
