<template>
  <ResourceTable
    v-if="initialize"
    :resource-name="resourceName"
    :table-id="tableId"
    :empty-state="{
      to: { name: 'create-contact' },
      title: $t('contacts::contact.empty_state.title'),
      buttonText: $t('contacts::contact.create'),
      description: $t('contacts::contact.empty_state.description'),
      secondButtonText: $t('core::import.from_file', { file_type: 'CSV' }),
      secondButtonIcon: 'DocumentAdd',
      secondButtonTo: {
        name: 'import-resource',
        params: { resourceName },
      },
    }"
    @loaded="$emit('loaded', $event)"
  >
    <template #header="{ total }">
      <span
        v-t="{
          path: 'contacts::contact.count.all',
          args: { count: total },
        }"
        class="text-sm font-medium text-neutral-700 dark:text-neutral-300"
      />
    </template>

    <template #actions="{ row }">
      <TableRowActions>
        <TableRowAction
          icon="Clock"
          :text="$t('activities::activity.create')"
          @click="
            activityBeingCreatedRow = {
              ...row,
              name: row.display_name,
            }
          "
        />

        <TableRowAction
          v-if="row.authorizations.update"
          :text="$t('core::app.edit')"
          icon="PencilAlt"
          @click="
            floatResourceInEditMode({
              resourceName,
              resourceId: row.id,
            })
          "
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

  <CreateActivityModal
    :visible="activityBeingCreatedRow !== null"
    :contacts="[activityBeingCreatedRow]"
    :with-extended-submit-buttons="true"
    :go-to-list="false"
    @created="
      ({ isRegularAction }) => (
        isRegularAction ? (activityBeingCreatedRow = null) : '', reloadTable()
      )
    "
    @hidden="activityBeingCreatedRow = null"
  />
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

import { useFloatingResourceModal } from '@/Core/composables/useFloatingResourceModal'
import { useResourceable } from '@/Core/composables/useResourceable'
import { useTable } from '@/Core/composables/useTable'

const props = defineProps({
  tableId: { required: true, type: String },
  initialize: { default: true, type: Boolean },
})

const emit = defineEmits(['deleted', 'loaded'])

const resourceName = Innoclapps.resourceName('contacts')

const { t } = useI18n()
const { reloadTable } = useTable(props.tableId)
const { floatResourceInEditMode } = useFloatingResourceModal()
const { deleteResource } = useResourceable(resourceName)

const activityBeingCreatedRow = ref(null)

async function destroy(id) {
  await deleteResource(id)

  emit('deleted', id)

  reloadTable()

  Innoclapps.success(t('core::resource.deleted'))
}
</script>
