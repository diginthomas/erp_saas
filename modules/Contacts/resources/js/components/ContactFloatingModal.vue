<template>
  <ISlideover
    id="contactFloatingModal"
    :title="resource.display_name"
    static-backdrop
    form
    @submit="updateHandler(form)"
  >
    <FieldsPlaceholder v-if="!floatingReady" />

    <div v-else class="flex flex-col">
      <FieldsCollapseButton
        v-if="totalCollapsableFields > 0"
        v-model:collapsed="fieldsCollapsed"
        :total="totalCollapsableFields"
        class="mb-2 ml-auto"
      />

      <FormFields
        v-if="mode === 'edit'"
        :fields="fields"
        :form="form"
        :resource-name="resourceName"
        :resource-id="resource.id"
        :collapsed="fieldsCollapsed"
        is-floating
        @update-field-value="form.fill($event.attribute, $event.value)"
        @set-initial-value="form.set($event.attribute, $event.value)"
      />

      <DetailFields
        v-else
        :fields="fields"
        :resource-name="resourceName"
        :resource-id="resource.id"
        :resource="resource"
        :collapsed="fieldsCollapsed"
        is-floating
        @updated="synchronizeAndEmitUpdatedEvent($event, true)"
      />

      <div
        v-show="mode === 'detail'"
        class="mt-6 h-2 border-t border-neutral-200 dark:border-neutral-600"
      />

      <div
        :class="[
          'mt-6 space-y-6 sm:space-y-8',
          mode === 'detail' ? 'px-10' : 'px-1',
        ]"
      >
        <ContactCompaniesList
          :float-mode="mode"
          :contact-id="resource.id"
          :companies="resource.companies"
          :authorize-dissociate="resource.authorizations.update"
          :show-create-button="resource.authorizations.update"
          @dissociated="
            ensureFieldsAndFormIsSynced('companies', resource.companies),
              synchronizeAndEmitUpdatedEvent({
                companies: { id: $event, _delete: true },
              })
          "
          @create-requested="companyBeingCreated = true"
        />

        <ContactDealsList
          :float-mode="mode"
          :contact-id="resource.id"
          :deals="resource.deals"
          :authorize-dissociate="resource.authorizations.update"
          :show-create-button="resource.authorizations.update"
          @dissociated="
            ensureFieldsAndFormIsSynced('deals', resource.deals),
              synchronizeAndEmitUpdatedEvent({
                deals: { id: $event, _delete: true },
              })
          "
          @create-requested="dealBeingCreated = true"
        />

        <MediaCard
          :card="false"
          :resource-name="resourceName"
          :resource-id="resource.id"
          :media="resource.media"
          :authorize-delete="resource.authorizations.update"
          is-floating
          @uploaded="synchronizeAndEmitUpdatedEvent({ media: [$event] })"
          @deleted="
            synchronizeAndEmitUpdatedEvent({
              media: { id: $event.id, _delete: true },
            })
          "
        />
      </div>

      <CreateCompanyModal
        v-model:visible="companyBeingCreated"
        :overlay="false"
        :contacts="[resource]"
        @created="handleCompanyCreated"
        @restored="handleCompanyCreated"
      />

      <CreateDealModal
        v-model:visible="dealBeingCreated"
        :overlay="false"
        :contacts="[resource]"
        @created="handleDealCreated"
        @restored="handleDealCreated"
      />
    </div>

    <template #modal-footer>
      <div class="flex justify-end space-x-2">
        <IButton
          variant="white"
          :disabled="!floatingReady"
          :text="$t('core::app.view_record')"
          @click="$emit('viewRequested', $event)"
        />

        <IButton
          v-if="mode === 'edit'"
          type="submit"
          variant="primary"
          :loading="form.busy"
          :text="$t('core::app.save')"
          :disabled="
            !floatingReady || form.busy || !resource.authorizations.update
          "
        />
      </div>
    </template>
  </ISlideover>
</template>

<script setup>
import { inject, nextTick, ref } from 'vue'

import MediaCard from '@/Core/components/Media/ResourceRecordMediaCard.vue'
import { useForm } from '@/Core/composables/useForm'

import ContactCompaniesList from './ContactCompaniesList.vue'
import ContactDealsList from './ContactDealsList.vue'

const props = defineProps({
  resource: { required: true, type: Object },
  fields: { required: true, type: Array },
  mode: { required: true, type: String },
  floatingReady: { required: true, type: Boolean },
  updateHandler: { required: true, type: Function },
})

defineEmits(['viewRequested'])

const resourceName = Innoclapps.resourceName('contacts')

const { form } = useForm()

const fieldsCollapsed = ref(true)
const companyBeingCreated = ref(false)
const dealBeingCreated = ref(false)

const synchronizeAndEmitUpdatedEvent = inject('synchronizeAndEmitUpdatedEvent')
const hydrateFields = inject('hydrateFields')
const totalCollapsableFields = inject('totalCollapsableFields')

function ensureFieldsAndFormIsSynced(attribute, data) {
  hydrateFields({ [attribute]: data })

  form.fill(
    attribute,
    data.map(r => r.id)
  )
}

function handleCompanyCreated(data) {
  companyBeingCreated.value = false
  synchronizeAndEmitUpdatedEvent({ companies: data.company })

  nextTick(() => {
    ensureFieldsAndFormIsSynced('companies', props.resource.companies)
  })
}

function handleDealCreated(data) {
  dealBeingCreated.value = false
  synchronizeAndEmitUpdatedEvent({ deals: data.deal })

  nextTick(() => {
    ensureFieldsAndFormIsSynced('deals', props.resource.deals)
  })
}
</script>
