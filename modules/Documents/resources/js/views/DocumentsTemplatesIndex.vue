<template>
  <MainLayout>
    <template #actions>
      <NavbarSeparator class="hidden lg:block" />

      <div class="inline-flex items-center">
        <IButton
          :to="{ name: 'create-document-template' }"
          :text="$t('documents::document.template.create')"
          icon="Plus"
        />
      </div>
    </template>

    <ResourceTable
      :resource-name="resourceName"
      :table-id="tableId"
      :empty-state="{
        to: { name: 'create-document-template' },
        title: $t('documents::document.template.empty_state.title'),
        buttonText: $t('documents::document.template.create'),
        description: $t('documents::document.template.empty_state.description'),
      }"
    >
      <template #actions="{ row }">
        <TableRowActions>
          <TableRowAction
            v-if="row.authorizations.update"
            :to="{ name: 'edit-document-template', params: { id: row.id } }"
            :text="$t('core::app.edit')"
            icon="PencilAlt"
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
    <!-- Create, Edit -->
    <RouterView name="create" @created="reloadTable" />

    <RouterView name="edit" @updated="reloadTable" />
  </MainLayout>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

import { useResourceable } from '@/Core/composables/useResourceable'
import { useTable } from '@/Core/composables/useTable'

const resourceName = Innoclapps.resourceName('document-templates')
const tableId = 'document-templates'

const { t } = useI18n()
const router = useRouter()
const { reloadTable } = useTable(tableId)
const { deleteResource, cloneResource } = useResourceable(resourceName)

async function clone(id) {
  const template = await cloneResource(id)

  reloadTable()
  router.push({ name: 'edit-document-template', params: { id: template.id } })
}

async function destroy(id) {
  await deleteResource(id)

  reloadTable()

  Innoclapps.success(t('documents::document.template.deleted'))
}
</script>
