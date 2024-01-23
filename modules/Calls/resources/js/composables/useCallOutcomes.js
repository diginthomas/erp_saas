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

const callOutcomes = ref([])

export const useCallOutcomes = () => {
  const { setLoading, isLoading: outcomesAreBeingFetched } = useLoader()
  const { scriptConfig } = useApp()

  callOutcomes.value = [...(scriptConfig('calls.outcomes') || [])]

  watchEffect(() => {
    scriptConfig('calls.outcomes', [...callOutcomes.value])
  })

  const outcomesByName = computed(() => orderBy(callOutcomes.value, 'name'))

  function setCallOutcomes(outcomes) {
    callOutcomes.value = outcomes
  }

  function fetchCallOutcomes() {
    setLoading(true)

    Innoclapps.request('/call-outcomes', {
      params: {
        per_page: 100,
      },
    })
      .then(({ data }) => (callOutcomes.value = data.data))
      .finally(() => setLoading(false))
  }

  return {
    callOutcomes,
    outcomesByName,
    outcomesAreBeingFetched,

    setCallOutcomes,
    fetchCallOutcomes,
  }
}
