<template>
  <div class="mb-2 block lg:hidden">
    <DocumentsTableStatusPicker v-model="selectedStatus" />
  </div>

  <ResourceTable
    v-if="initialize"
    :resource-name="resourceName"
    :table-id="tableId"
    :data-request-query-string="dataRequestQueryString"
    :empty-state="{
      to: { name: 'create-document' },
      title: $t('documents::document.empty_state.title'),
      buttonText: $t('documents::document.create'),
      description: $t('documents::document.empty_state.description'),
    }"
    @loaded="$emit('loaded', $event)"
  >
    <template #header="{ total }">
      <div class="hidden lg:ml-6 lg:block">
        <DocumentsTableStatusPicker v-model="selectedStatus" />
      </div>

      <span
        v-t="{
          path: 'documents::document.count.all',
          args: { count: total },
        }"
        class="text-sm font-medium text-neutral-700 dark:text-neutral-300"
      />
    </template>

    <template #status="{ row }">
      <TextBackground
        :color="statuses[row.status].color"
        class="rounded-md px-2.5 py-0.5 dark:!text-white"
      >
        {{ $t('documents::document.status.' + row.status) }}
      </TextBackground>
    </template>

    <template #actions="{ row }">
      <TableRowActions>
        <TableRowAction
          tag="a"
          :href="row.public_url"
          :text="$t('documents::document.view')"
          icon="Eye"
        />

        <TableRowAction
          v-if="row.authorizations.update && row.status === 'draft'"
          :to="{
            name: 'edit-document',
            params: { id: row.id },
            query: { section: 'send' },
          }"
          :text="$t('documents::document.send.send')"
          icon="Mail"
        />

        <TableRowAction
          :text="$t('core::app.clone')"
          icon="Duplicate"
          @click="clone(row.id)"
        />

        <TableRowAction
          v-if="row.authorizations.delete"
          :text="$t('core::app.delete')"
          icon="Trash"
          @click="$confirm(() => destroy(row.id))"
        />
      </TableRowActions>
    </template>
  </ResourceTable>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

import { useApp } from '@/Core/composables/useApp'
import { useResourceable } from '@/Core/composables/useResourceable'
import { useTable } from '@/Core/composables/useTable'

import DocumentsTableStatusPicker from './DocumentsTableStatusPicker.vue'

const props = defineProps({
  tableId: { required: true, type: String },
  initialize: { default: true, type: Boolean },
})

const emit = defineEmits(['deleted', 'loaded'])

const resourceName = Innoclapps.resourceName('documents')

const router = useRouter()
const { t } = useI18n()
const { scriptConfig } = useApp()
const { reloadTable } = useTable(props.tableId)
const { deleteResource, cloneResource } = useResourceable(resourceName)

const statuses = scriptConfig('documents.statuses')
const selectedStatus = ref(null)

const dataRequestQueryString = computed(() => ({
  status: selectedStatus.value,
}))

async function clone(id) {
  const document = await cloneResource(id)

  reloadTable()
  router.push({ name: 'edit-document', params: { id: document.id } })
}

async function destroy(id) {
  await deleteResource(id)

  emit('deleted', id)
  reloadTable()

  Innoclapps.success(t('core::resource.deleted'))
}
</script>
