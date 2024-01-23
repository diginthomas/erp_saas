<template>
  <DealsList
    :deals="deals"
    :empty-text="$t('contacts::company.no_deals_associated')"
  >
    <template #actions="{ deal }">
      <IButtonIcon
        v-show="authorizeDissociate"
        v-i-tooltip.left="$t('deals::deal.dissociate')"
        icon="X"
        @click="$confirm(() => dissociateDeal(deal.id))"
      />
    </template>

    <template #top-actions>
      <IButton
        v-if="showCreateButton"
        v-i-tooltip="$t('deals::deal.add')"
        class="ml-4"
        size="xs"
        variant="white"
        icon="Plus"
        @click="$emit('createRequested')"
      />
    </template>
  </DealsList>
</template>

<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'

import DealsList from '@/Deals/components/DealsList.vue'

defineProps({
  deals: { required: true, type: Array },
  companyId: { required: true, type: Number },
  authorizeDissociate: { required: true, type: Boolean },
  showCreateButton: { required: true, type: Boolean },
})

const emit = defineEmits(['dissociated', 'createRequested'])

const detachResourceAssociations = inject('detachResourceAssociations')

const { t } = useI18n()

async function dissociateDeal(id) {
  await detachResourceAssociations({ deals: [id] })

  emit('dissociated', id)

  Innoclapps.success(t('core::resource.dissociated'))
}
</script>
