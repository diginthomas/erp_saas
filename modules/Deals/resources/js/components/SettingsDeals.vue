<template>
  <div class="space-y-3">
    <PipelinesIndex></PipelinesIndex>

    <ICard no-body :header="$t('deals::deal.deals')" :overlay="!componentReady">
      <SettingsToggleItems>
        <SettingsToggleItem
          v-model="form.allow_lost_reason_enter"
          :heading="$t('deals::deal.settings.allow_lost_reason_enter')"
          :description="$t('deals::deal.settings.allow_lost_reason_enter_info')"
          @change="submit"
        />

        <SettingsToggleItem
          v-model="form.lost_reason_is_required"
          :heading="$t('deals::deal.settings.lost_reason_is_required')"
          :description="$t('deals::deal.settings.lost_reason_is_required_info')"
          @change="submit"
        />
      </SettingsToggleItems>
    </ICard>

    <LostReasonsIndex></LostReasonsIndex>
  </div>
</template>

<script setup>
import { useSettings } from '@/Core/composables/useSettings'
import SettingsToggleItem from '@/Core/views/Settings/SettingsToggleItem.vue'
import SettingsToggleItems from '@/Core/views/Settings/SettingsToggleItems.vue'

import LostReasonsIndex from '../views/DealsLostReasonsIndex.vue'
import PipelinesIndex from '../views/DealsPipelinesIndex.vue'

const { form, submit, isReady: componentReady } = useSettings()
</script>
