<template>
  <div class="flex flex-col space-y-1 sm:flex-row sm:space-x-2 sm:space-y-0">
    <IFormRadio
      v-for="taxType in formattedTaxTypes"
      :id="key + '-' + taxType.value"
      :key="taxType.value"
      v-model="model"
      :value="taxType.value"
      name="tax_type"
      :label="taxType.label"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

import { useApp } from '@/Core/composables/useApp'
import { randomString } from '@/Core/utils'

const model = defineModel()

const { t } = useI18n()
const { scriptConfig } = useApp()

const taxTypes = scriptConfig('taxes.types')

// In case included in modal, make sure unique ID is given so the label click works properly
const key = randomString()

const formattedTaxTypes = computed(() =>
  taxTypes.map(type => ({
    value: type,
    label: t('billable::billable.tax_types.' + type),
  }))
)
</script>
