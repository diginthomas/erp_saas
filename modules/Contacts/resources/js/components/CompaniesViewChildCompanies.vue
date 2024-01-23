<template>
  <div
    v-if="hasParents"
    v-memo="[resource.parents.map(p => p.updated_at)]"
    class="rounded-lg border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900"
  >
    <div class="px-4 py-5 sm:px-6">
      <CompaniesList
        :title="$t('contacts::company.child', { count: totalParents })"
        :companies="resource.parents"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

import CompaniesList from './CompaniesList.vue'

const props = defineProps({
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: [String, Number] },
  resource: { required: true, type: Object },
})

const totalParents = computed(() => props.resource.parents.length)

const hasParents = computed(() => totalParents.value > 0)
</script>
