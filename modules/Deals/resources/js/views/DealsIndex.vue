<template>
  <MainLayout>
    <template #actions>
      <NavbarSeparator class="hidden lg:block" />

      <NavbarItems>
        <IMinimalDropdown horizontal>
          <IDropdownItem
            icon="DocumentAdd"
            :to="{
              name: 'import-deal',
            }"
            :text="$t('core::import.import')"
          />

          <IDropdownItem
            v-if="$gate.userCan('export deals')"
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

        <DealsNavbarViewSelector active="index" />

        <IButton
          :to="{ name: 'create-deal' }"
          icon="Plus"
          :text="$t('deals::deal.create')"
        />
      </NavbarItems>
    </template>

    <CardsRenderer
      v-if="showCards"
      class="mb-2"
      :resource-name="resourceName"
    />

    <DealsTable
      :table-id="tableId"
      :initialize="initialize"
      :filter-id="
        $route.query.filter_id ? parseInt($route.query.filter_id) : undefined
      "
      @loaded="tableEmpty = $event.isEmpty"
      @deleted="refreshIndex"
    />

    <ResourceExport
      url-path="/deals/export"
      :resource-name="resourceName"
      :filters-view="tableId"
      :title="$t('deals::deal.export')"
    />

    <!-- Create -->
    <RouterView
      name="create"
      :redirect-to-view="true"
      @created="
        ({ isRegularAction }) => (!isRegularAction ? refreshIndex() : undefined)
      "
      @hidden="$router.back"
    />
  </MainLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { onBeforeRouteUpdate, useRoute } from 'vue-router'

import CardsRenderer from '@/Core/components/Cards/CardsRenderer.vue'
import ResourceExport from '@/Core/components/Export/ResourceExport.vue'
import { emitGlobal } from '@/Core/composables/useGlobalEventListener'
import { useTable } from '@/Core/composables/useTable'

import DealsNavbarViewSelector from '../components/DealsNavbarViewSelector.vue'
import DealsTable from '../components/DealsTable.vue'

const resourceName = Innoclapps.resourceName('deals')
const tableId = 'deals'

const route = useRoute()
const { reloadTable, customizeTable } = useTable(tableId)

const initialize = ref(route.meta.initialize)
const tableEmpty = ref(true)

const showCards = computed(() => initialize.value && !tableEmpty.value)

function refreshIndex() {
  emitGlobal('refresh-cards')
  reloadTable()
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
