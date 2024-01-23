<template>
  <div class="mb-2 block lg:hidden">
    <ActivitiesTableTypePicker v-model="selectedType" />
  </div>

  <ResourceTable
    v-if="initialize"
    :resource-name="resourceName"
    :data-request-query-string="dataRequestQueryString"
    :table-id="tableId"
    :empty-state="{
      to: { name: 'create-activity' },
      title: $t('activities::activity.empty_state.title'),
      buttonText: $t('activities::activity.create'),
      description: $t('activities::activity.empty_state.description'),
      secondButtonText: $t('core::import.from_file', { file_type: 'CSV' }),
      secondButtonIcon: 'DocumentAdd',
      secondButtonTo: {
        name: 'import-resource',
        params: { resourceName },
      },
    }"
    v-bind="$attrs"
  >
    <template #header="{ total }">
      <div class="hidden lg:block">
        <ActivitiesTableTypePicker v-model="selectedType" />
      </div>

      <span
        v-t="{
          path: 'activities::activity.count',
          args: { count: total },
        }"
        class="text-sm font-medium text-neutral-700 dark:text-neutral-300"
      />
    </template>

    <template #title="{ row }">
      <div class="flex items-center space-x-1">
        <ActivityStateChange
          v-if="row.authorizations.update"
          class="mt-0.5"
          :is-completed="row.is_completed"
          :activity-id="row.id"
          @changed="reloadTable"
        />

        <span>
          {{ row.title }}
        </span>
      </div>
    </template>

    <template #actions="{ row }">
      <TableRowActions
        v-if="row.authorizations.update || row.authorizations.update"
      >
        <TableRowAction
          v-if="row.authorizations.update"
          :text="$t('core::app.edit')"
          icon="PencilAlt"
          :to="{
            name: 'edit-activity',
            params: { id: row.id },
          }"
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
  <!-- Edit/View -->
  <RouterView name="edit" />
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import { useResourceable } from '@/Core/composables/useResourceable'
import { useTable } from '@/Core/composables/useTable'

import ActivitiesTableTypePicker from './ActivitiesTableTypePicker.vue'
import ActivityStateChange from './ActivityStateChange.vue'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps({
  tableId: { required: true, type: String },
  initialize: { default: true, type: Boolean },
})

const emit = defineEmits(['deleted'])

const resourceName = Innoclapps.resourceName('activities')

const { t } = useI18n()
const { reloadTable } = useTable(props.tableId)
const { deleteResource } = useResourceable(resourceName)

const selectedType = ref(undefined)

const dataRequestQueryString = computed(() => ({
  activity_type_id: selectedType.value,
}))

async function destroy(id) {
  await deleteResource(id)

  emit('deleted', id)
  reloadTable()

  Innoclapps.success(t('core::resource.deleted'))
}
</script>
