<template>
  <form @submit.prevent="submit">
    <ICard
      :header="$t('billable::product.products')"
      footer-class="text-right"
      :overlay="!componentReady"
    >
      <div class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-6">
        <div class="sm:col-span-2">
          <IFormGroup
            :label="$t('billable::product.tax_label')"
            label-for="tax_label"
            required
          >
            <IFormInput id="tax_label" v-model="form.tax_label" />
          </IFormGroup>
        </div>

        <div class="sm:col-span-2">
          <IFormGroup
            :label="$t('billable::product.tax_rate')"
            label-for="tax_rate"
          >
            <IFormNumericInput
              v-model="form.tax_rate"
              :placeholder="$t('billable::product.tax_percent')"
              :precision="3"
              :minus="true"
            >
            </IFormNumericInput>
          </IFormGroup>
        </div>
      </div>

      <IFormGroup
        class="mt-3 space-y-1"
        :label="$t('billable::product.settings.default_tax_type')"
        label-for="tax_type"
      >
        <IFormRadio
          v-for="taxType in taxTypes"
          :id="taxType"
          :key="taxType"
          v-model="form.tax_type"
          :label="$t('billable::billable.tax_types.' + taxType)"
          :value="taxType"
          name="tax_type"
        />
      </IFormGroup>

      <IFormGroup
        :label="$t('billable::product.settings.default_discount_type')"
        label-for="tax_type"
        class="mt-3 space-y-1"
      >
        <IFormRadio
          v-for="discountType in discountTypes"
          :id="discountType.value"
          :key="discountType.value"
          v-model="form.discount_type"
          :label="discountType.label"
          :value="discountType.value"
          name="discount_type"
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
  </form>
</template>

<script setup>
import { useApp } from '@/Core/composables/useApp'
import { useSettings } from '@/Core/composables/useSettings'

const { form, isReady: componentReady, submit } = useSettings()
const { scriptConfig } = useApp()

const taxTypes = scriptConfig('taxes.types')

const discountTypes = [
  { label: scriptConfig('currency.iso_code'), value: 'fixed' },
  { label: '%', value: 'percent' },
]
</script>
