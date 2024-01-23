<template>
  <ICard
    no-body
    :header="$t('activities::activity.activities')"
    class="mb-3"
    :overlay="!componentReady"
  >
    <SettingsToggleItems>
      <SettingsToggleItem
        v-model="form.send_contact_attends_to_activity_mail"
        :heading="$t('activities::activity.settings.send_contact_email')"
        :description="
          $t('activities::activity.settings.send_contact_email_info')
        "
        @change="submit"
      />
    </SettingsToggleItems>

    <div
      class="border-y border-neutral-200 px-4 py-4 sm:px-6 dark:border-neutral-700"
    >
      <IFormGroup
        :label="$t('activities::activity.type.default_type')"
        class="mb-0"
        label-for="default_activity_type"
      >
        <ICustomSelect
          v-model="defaultType"
          input-id="default_activity_type"
          class="xl:w-1/3"
          :clearable="false"
          label="name"
          :options="types"
          @option-selected="handleActivityTypeInputEvent"
        >
        </ICustomSelect>
      </IFormGroup>
    </div>
  </ICard>

  <ActivitiesTypesIndex></ActivitiesTypesIndex>
</template>

<script setup>
import { ref } from 'vue'
import { watchOnce } from '@vueuse/core'

import { useApp } from '@/Core/composables/useApp'
import { useSettings } from '@/Core/composables/useSettings'
import SettingsToggleItem from '@/Core/views/Settings/SettingsToggleItem.vue'
import SettingsToggleItems from '@/Core/views/Settings/SettingsToggleItems.vue'

import { useActivityTypes } from '../composables/useActivityTypes'
import ActivitiesTypesIndex from '../views/ActivitiesTypesIndex.vue'

const { resetStoreState } = useApp()
const { form, submit, isReady: componentReady } = useSettings()

const defaultType = ref(null)

const { typesByName: types } = useActivityTypes()

function handleActivityTypeInputEvent(e) {
  form.default_activity_type = e.id
  submit(resetStoreState)
}

watchOnce(componentReady, () => {
  defaultType.value = types.value.find(
    type => type.id == form.default_activity_type
  )
})
</script>
