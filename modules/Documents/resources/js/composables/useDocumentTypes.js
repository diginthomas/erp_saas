/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.5
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2024 KONKORD DIGITAL
 */
import { computed, ref, watchEffect } from 'vue'
import orderBy from 'lodash/orderBy'

import { useApp } from '@/Core/composables/useApp'
import { useLoader } from '@/Core/composables/useLoader'

const documentTypes = ref([])

export const useDocumentTypes = () => {
  const { setLoading, isLoading: typesAreBeingFetched } = useLoader()
  const { scriptConfig } = useApp()

  documentTypes.value = [...(scriptConfig('documents.types') || [])]

  watchEffect(() => {
    scriptConfig('documents.types', [...documentTypes.value])
  })

  const typesByName = computed(() => orderBy(documentTypes.value, 'name'))

  function findTypeById(id) {
    return typesByName.value.find(t => t.id == id)
  }

  function setDocumentTypes(types) {
    documentTypes.value = types
  }

  function fetchDocumentTypes() {
    setLoading(true)

    Innoclapps.request('/document-types', {
      params: {
        per_page: 100,
      },
    })
      .then(({ data }) => (documentTypes.value = data.data))
      .finally(() => setLoading(false))
  }

  return {
    documentTypes,
    typesByName,
    typesAreBeingFetched,

    findTypeById,
    setDocumentTypes,
    fetchDocumentTypes,
  }
}
