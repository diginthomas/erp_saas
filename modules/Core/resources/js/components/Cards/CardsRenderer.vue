<template>
  <div class="flex flex-wrap space-x-0 lg:flex-nowrap lg:space-x-4">
    <template v-if="!componentReady">
      <div v-for="p in totalPlaceholders" :key="p" class="w-full lg:w-1/2">
        <CardPlaceholder pulse class="mb-4" />
      </div>
    </template>

    <div
      v-for="card in cards"
      :key="card.uriKey"
      :class="card.width === 'half' ? 'w-full lg:w-1/2' : 'w-full'"
    >
      <component :is="card.component" class="mb-4" :card="card" />
    </div>
  </div>
</template>

<script setup>
import { ref, shallowRef } from 'vue'

import CardPlaceholder from './CardPlaceholder.vue'

const props = defineProps({
  resourceName: { required: true, type: String },
  totalPlaceholders: { default: 2, type: Number },
})

const cards = shallowRef([])
const componentReady = ref(false)

/**
 * Fetch the resource cards
 */
async function fetch() {
  let { data } = await Innoclapps.request(`/${props.resourceName}/cards`)

  cards.value = data
  componentReady.value = true
}

fetch()
</script>
