<template>
  <div
    v-memo="[resource.deals.map(d => d.updated_at)]"
    class="rounded-lg border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900"
  >
    <div class="px-4 py-5 sm:px-6">
      <ContactDealsList
        :contact-id="resourceId"
        :deals="resource.deals"
        :authorize-dissociate="resource.authorizations.update"
        :show-create-button="resource.authorizations.update"
        @create-requested="
          $router.push({
            name: 'createDealViaContact',
            params: { id: resourceId },
          })
        "
      />
    </div>
  </div>
</template>

<script setup>
import ContactDealsList from './ContactDealsList.vue'

defineProps({
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: [String, Number] },
  resource: { required: true, type: Object },
})
</script>
