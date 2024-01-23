<template>
  <div
    v-memo="[resource.contacts.map(d => d.updated_at)]"
    class="rounded-lg border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900"
  >
    <div class="px-4 py-5 sm:px-6">
      <DealContactCard
        :deal-id="resourceId"
        :contacts="resource.contacts"
        :authorize-dissociate="resource.authorizations.update"
        :show-create-button="resource.authorizations.update"
        @create-requested="
          $router.push({
            name: 'createContactViaDeal',
            params: { id: resourceId },
          })
        "
      />
    </div>
  </div>
</template>

<script setup>
import DealContactCard from './DealContactsList.vue'

defineProps({
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: [String, Number] },
  resource: { required: true, type: Object },
})
</script>
