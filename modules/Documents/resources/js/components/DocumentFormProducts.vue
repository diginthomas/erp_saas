<template>
  <div v-show="visible" class="mx-auto max-w-5xl">
    <h3
      v-t="'documents::document.document_products'"
      class="mb-6 text-base font-medium text-neutral-800 dark:text-neutral-100"
    />

    <div
      :class="{
        'pointer-events-none opacity-70': document.status === 'accepted',
      }"
    >
      <BillableFormTaxTypes v-model="form.billable.tax_type" class="mb-4" />

      <BillableFormTableProducts
        v-model:products="form.billable.products"
        v-model:removed-products="form.billable.removed_products"
        :tax-type="form.billable.tax_type"
      >
        <template #after-product-select="{ index }">
          <IFormError
            :error="form.getError('billable.products.' + index + '.name')"
          />
        </template>
      </BillableFormTableProducts>
    </div>
  </div>
</template>

<script setup>
import BillableFormTableProducts from '@/Billable/components/BillableFormTableProducts.vue'
import BillableFormTaxTypes from '@/Billable/components/BillableFormTaxTypes.vue'

import propsDefinition from './formSectionProps'

defineProps(propsDefinition)
</script>
