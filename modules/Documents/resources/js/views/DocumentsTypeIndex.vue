<template>
  <SimpleResourceCRUD
    :resource-name="resourceName"
    :with-cancel="false"
    @created="fetchDocumentTypes"
    @updated="fetchDocumentTypes"
    @deleted="fetchDocumentTypes"
  >
    <template #header>
      <ICardHeading>{{ $t('documents::document.type.types') }}</ICardHeading>
    </template>
  </SimpleResourceCRUD>
</template>

<script setup>
import SimpleResourceCRUD from '@/Core/components/SimpleResourceCRUD.vue'

import { useDocumentTypes } from '../composables/useDocumentTypes'

const resourceName = Innoclapps.resourceName('document-types')

const { fetchDocumentTypes } = useDocumentTypes()
</script>
