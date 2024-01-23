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
import { computed } from 'vue'
import { computedAsync } from '@vueuse/core'

let _placeholders = null

export function useMessagePlaceholders() {
  const placeholders = computedAsync(
    async () => {
      if (_placeholders !== null) {
        return _placeholders
      }

      const { data } = await Innoclapps.request('/placeholders', {
        params: { resources: ['contacts', 'companies', 'deals'] },
      })

      _placeholders = data

      return _placeholders
    },
    [],
    { lazy: true }
  )

  const allPlaceholders = computed(() => {
    let list = []

    Object.keys(placeholders.value).forEach(group => {
      placeholders.value[group].placeholders.forEach(placeholder => {
        list.push(placeholder)
      })
    })

    return list
  })

  const allPlaceholdersInterpolations = computed(() => {
    return allPlaceholders.value.reduce((tags, p) => {
      const tag = [p.interpolation_start, p.interpolation_end]

      if (tags.length === 0) {
        tags.push(tag)
      } else {
        tags.forEach(i => {
          if (i[0] !== tag[0] && i[1] !== tag[1]) {
            tags.push(tag)
          }
        })
      }

      return tags
    }, [])
  })

  async function makeParsePlaceholdersRequest(resources, content, type) {
    if (!content) {
      return content
    }

    let { data } = await Innoclapps.request().post(`/placeholders/${type}`, {
      resources: resources,
      content: content,
    })

    return data
  }

  return {
    placeholders,
    allPlaceholders,
    allPlaceholdersInterpolations,
    makeParsePlaceholdersRequest,
  }
}
