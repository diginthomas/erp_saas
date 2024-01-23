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
            v-if="$gate.userCan('export activities')"
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

        <ActivitiesNavbarViewSelector active="index" />

        <IButton
          :to="{ name: 'create-activity' }"
          icon="Plus"
          :text="$t('activities::activity.create')"
        />
      </NavbarItems>
    </template>

    <ActivitiesTable
      :table-id="tableId"
      :initialize="initialize"
      :filter-id="
        $route.query.filter_id ? parseInt($route.query.filter_id) : undefined
      "
    />

    <ResourceExport
      url-path="/activities/export"
      :resource-name="resourceName"
      :filters-view="tableId"
      :title="$t('activities::activity.export')"
    />

    <!-- Create -->
    <RouterView
      name="create"
      @created="
        ({ isRegularAction, action }) => (
          refreshIfInitialized(),
          isRegularAction || action === 'go-to-list'
            ? $router.back()
            : undefined
        )
      "
      @hidden="$router.back"
    />
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue'
import { onBeforeRouteUpdate, useRoute } from 'vue-router'

import ResourceExport from '@/Core/components/Export/ResourceExport.vue'
import { useTable } from '@/Core/composables/useTable'

import ActivitiesNavbarViewSelector from '../components/ActivitiesNavbarViewSelector.vue'
import ActivitiesTable from '../components/ActivitiesTable.vue'

const resourceName = Innoclapps.resourceName('activities')
const tableId = 'activities'

const route = useRoute()
const { reloadTable, customizeTable } = useTable(tableId)

const initialize = ref(route.meta.initialize)

function refreshIfInitialized() {
  if (initialize.value) {
    reloadTable()
  }
}

/**
 * For all cases intialize index to be true
 * This helps when intially "initialize" was false
 * But now when the user actually sees the index, it should be updated to true
 */
onBeforeRouteUpdate((to, from, next) => {
  initialize.value = true

  next()
})
</script>
