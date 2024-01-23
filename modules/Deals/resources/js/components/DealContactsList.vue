<template>
  <ContactsList
    :contacts="contacts"
    :empty-text="$t('deals::deal.no_contacts_associated')"
  >
    <template #actions="{ contact }">
      <IButtonIcon
        v-show="authorizeDissociate"
        v-i-tooltip.left="$t('contacts::contact.dissociate')"
        icon="X"
        @click="$confirm(() => dissociateContact(contact.id))"
      />
    </template>

    <template #top-actions>
      <IButton
        v-if="showCreateButton"
        v-i-tooltip="$t('contacts::contact.add')"
        class="ml-4"
        size="xs"
        variant="white"
        icon="Plus"
        @click="$emit('createRequested')"
      />
    </template>
  </ContactsList>
</template>

<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'

import ContactsList from '@/Contacts/components/ContactsList.vue'

defineProps({
  contacts: { required: true, type: Array },
  dealId: { required: true, type: Number },
  authorizeDissociate: { required: true, type: Boolean },
  showCreateButton: { required: true, type: Boolean },
})

const emit = defineEmits(['dissociated', 'createRequested'])

const detachResourceAssociations = inject('detachResourceAssociations')

const { t } = useI18n()

async function dissociateContact(id) {
  await detachResourceAssociations({ contacts: [id] })

  emit('dissociated', id)

  Innoclapps.success(t('core::resource.dissociated'))
}
</script>
