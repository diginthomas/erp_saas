<template>
  <ResourceTable
    v-if="initialize"
    :resource-name="resourceName"
    :table-id="tableId"
    :empty-state="{
      to: { name: 'create-deal' },
      title: $t('deals::deal.empty_state.title'),
      buttonText: $t('deals::deal.create'),
      description: $t('deals::deal.empty_state.description'),
      secondButtonText: $t('core::import.from_file', { file_type: 'CSV' }),
      secondButtonIcon: 'DocumentAdd',
      secondButtonTo: {
        name: 'import-resource',
        params: { resourceName },
      },
    }"
    v-bind="$attrs"
    @loaded="$emit('loaded', $event)"
  >
    <template #header="{ meta }">
      <div class="flex items-center text-sm">
        <span
          class="font-medium text-neutral-700 dark:text-neutral-300"
          v-text="formatMoney(meta.summary?.value)"
        />

        <span
          v-show="
            !isFilteringWonOrLostDeals &&
            meta.summary?.weighted_value > 0 &&
            meta.summary?.weighted_value !== meta.summary?.value
          "
          class="mx-1 text-neutral-900 dark:text-neutral-300"
          v-text="'-'"
        />

        <span
          v-show="
            !isFilteringWonOrLostDeals &&
            meta.summary?.weighted_value > 0 &&
            meta.summary?.weighted_value !== meta.summary?.value
          "
          class="inline-flex items-center font-medium text-neutral-700 dark:text-neutral-300"
        >
          <Icon icon="Scale" class="mr-1 size-4" />

          <span>
            {{ formatMoney(meta.summary?.weighted_value) }}
          </span>
        </span>

        <span class="mx-1 text-neutral-900 dark:text-neutral-300">-</span>

        <span
          v-t="{
            path: 'deals::deal.count.all',
            args: { count: meta.summary?.count },
          }"
          class="font-medium text-neutral-700 dark:text-neutral-300"
        />
      </div>
    </template>

    <template #status="{ row, column }">
      <IBadge
        :variant="column.statuses[row.status]?.badge"
        :text="$t('deals::deal.status.' + column.statuses[row.status]?.name)"
        :rounded="false"
        class="rounded"
      />
    </template>

    <template #actions="{ row }">
      <TableRowActions>
        <TableRowAction
          :text="$t('activities::activity.create')"
          icon="Clock"
          @click="activityBeingCreatedRow = row"
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
    :deals="[activityBeingCreatedRow]"
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
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import { useAccounting } from '@/Core/composables/useAccounting'
import { useFloatingResourceModal } from '@/Core/composables/useFloatingResourceModal'
import { useQueryBuilder } from '@/Core/composables/useQueryBuilder'
import { useResourceable } from '@/Core/composables/useResourceable'
import { useTable } from '@/Core/composables/useTable'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps({
  tableId: { required: true, type: String },
  initialize: { default: true, type: Boolean },
})

const emit = defineEmits(['deleted', 'loaded'])

const resourceName = Innoclapps.resourceName('deals')

const { t } = useI18n()
const { formatMoney } = useAccounting()
const { reloadTable } = useTable(props.tableId)
const { floatResourceInEditMode } = useFloatingResourceModal()
const { deleteResource } = useResourceable(resourceName)

const activityBeingCreatedRow = ref(null)

const { findRule } = useQueryBuilder(props.tableId)

const isFilteringWonOrLostDeals = computed(() => {
  let rule = findRule('status')

  if (!rule) {
    return false
  }

  return rule.query.value === 'won' || rule.query.value === 'lost'
})

async function destroy(id) {
  await deleteResource(id)

  emit('deleted', id)

  reloadTable()

  Innoclapps.success(t('core::resource.deleted'))
}
</script>
