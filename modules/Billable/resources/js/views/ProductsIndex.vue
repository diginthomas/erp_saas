<template>
  <MainLayout>
    <template #actions>
      <NavbarSeparator class="hidden lg:block" />

      <NavbarItems>
        <IMinimalDropdown horizontal>
          <IDropdownItem
            icon="DocumentAdd"
            :to="{
              name: 'import-resource',
              params: { resourceName },
            }"
            :text="$t('core::import.import')"
          />

          <IDropdownItem
            v-if="$gate.userCan('export products')"
            icon="DocumentDownload"
            :text="$t('core::app.export.export')"
            @click="$dialog.show('export-modal')"
          />

          <IDropdownItem
            icon="Trash"
            :to="{
              name: 'trashed-resource-records',
              params: { resourceName },
            }"
            :text="$t('core::app.soft_deletes.trashed')"
          />

          <IDropdownItem
            icon="Cog"
            :text="$t('core::table.list_settings')"
            @click="customizeTable"
          />
        </IMinimalDropdown>

        <IButton
          :to="{ name: 'create-product' }"
          icon="Plus"
          :text="$t('billable::product.create')"
        />
      </NavbarItems>
    </template>

    <ResourceTable
      :resource-name="resourceName"
      :table-id="tableId"
      :empty-state="{
        to: { name: 'create-product' },
        title: $t('billable::product.empty_state.title'),
        buttonText: $t('billable::product.create'),
        description: $t('billable::product.empty_state.description'),
        secondButtonText: $t('core::import.from_file', { file_type: 'CSV' }),
        secondButtonIcon: 'DocumentAdd',
        secondButtonTo: {
          name: 'import-resource',
          params: { resourceName },
        },
      }"
    >
      <template #header="{ total }">
        <span
          v-t="{
            path: 'billable::product.count',
            args: { count: total },
          }"
          class="text-sm font-medium text-neutral-700 dark:text-neutral-300"
        />
      </template>

      <template #actions="{ row }">
        <TableRowActions>
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

    <ResourceExport
      url-path="/products/export"
      :resource-name="resourceName"
      :title="$t('billable::product.export')"
    />

    <!-- Create, Edit -->
    <RouterView
      @created="reloadTable"
      @restored="reloadTable"
      @updated="reloadTable"
    />
  </MainLayout>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

import ResourceExport from '@/Core/components/Export/ResourceExport.vue'
import { useResourceable } from '@/Core/composables/useResourceable'
import { useTable } from '@/Core/composables/useTable'

const resourceName = Innoclapps.resourceName('products')
const tableId = 'products'

const { t } = useI18n()
const { reloadTable, customizeTable } = useTable(tableId)
const { deleteResource, cloneResource } = useResourceable(resourceName)
const router = useRouter()

async function clone(id) {
  const product = await cloneResource(id)

  reloadTable()

  router.push({ name: 'edit-product', params: { id: product.id } })
}

async function destroy(id) {
  await deleteResource(id)

  reloadTable()

  Innoclapps.success(t('billable::product.deleted'))
}
</script>
