<template>
  <IModal
    :id="tableId + 'listSettings'"
    size="sm"
    :visible="tableBeingCustomized"
    :description="$t('core::table.customize_list_view')"
    :title="$t('core::table.list_settings')"
    @hidden="handleModalHidden"
  >
    <div v-if="tableSettings.allowDefaultSortChange" class="mb-4 mt-10">
      <p
        v-t="'core::table.default_sort'"
        class="mb-1 text-sm font-medium text-neutral-700 dark:text-neutral-100"
      />

      <SortableDraggable
        v-model="sorted"
        :item-key="item => item.attribute + '-' + item.direction"
        class="space-y-2"
        handle=".sort-draggable-handle"
        v-bind="$draggable.common"
      >
        <template #item="{ index }">
          <div class="flex items-center space-x-1.5">
            <div class="grow">
              <IFormSelect
                :id="'column_' + index"
                v-model="sorted[index].attribute"
                size="sm"
              >
                <!-- ios by default selects the first field but no events are triggered in this case
                we will make sure to add blank one -->
                <option v-if="!sorted[index].attribute" value=""></option>

                <option
                  v-for="sortableColumn in sortable"
                  v-show="!isSortedColumnDisabled(sortableColumn.attribute)"
                  :key="sortableColumn.attribute"
                  :value="sortableColumn.attribute"
                >
                  {{ sortableColumn.label }}
                </option>
              </IFormSelect>
            </div>

            <div class="flex-auto">
              <IFormSelect
                :id="'column_type_' + index"
                v-model="sorted[index].direction"
                size="sm"
              >
                <option value="asc">
                  Asc (<span v-t="'core::app.ascending'"></span>)
                </option>

                <option value="desc">
                  Desc (<span v-t="'core::app.descending'"></span>)
                </option>
              </IFormSelect>
            </div>

            <IButton
              :variant="index === 0 ? 'secondary' : 'danger'"
              :disabled="index === 0 && isAddSortColumnDisabled"
              :icon="index === 0 ? 'Plus' : 'Minus'"
              @click="index === 0 ? addSortedColumn() : removeSorted(index)"
            />

            <IButtonIcon
              icon="Selector"
              class="sort-draggable-handle cursor-move"
            />
          </div>
        </template>
      </SortableDraggable>
    </div>

    <p
      v-t="'core::table.columns'"
      class="mb-1.5 text-sm font-medium text-neutral-700 dark:text-neutral-100"
    />

    <SearchInput
      v-model="search"
      size="sm"
      @input="setMutableCustomizeableColumns"
    />

    <div class="my-4 max-h-[400px] overflow-auto">
      <SortableDraggable
        v-model="mutableCustomizeableColumns"
        :move="onColumnMove"
        item-key="attribute"
        v-bind="{ ...$draggable.scrollable, filter: 'label' }"
      >
        <template #item="{ element, index }">
          <div
            :class="[
              'mb-2 mr-2 flex items-center rounded-md border border-neutral-200 px-3 py-2 dark:border-neutral-600',
              element.primary
                ? 'pointer-events-none bg-neutral-50 dark:bg-neutral-800'
                : 'hover:bg-neutral-50 dark:hover:bg-neutral-800',
            ]"
          >
            <div class="grow">
              <IFormCheckbox
                :id="'col-' + element.attribute"
                v-model:checked="mutableCustomizeableColumns[index].visible"
                v-i-tooltip="
                  element.primary ? $t('core::table.primary_column') : ''
                "
                :name="'col-' + element.attribute"
                :disabled="element.primary === true"
              >
                <Icon
                  v-if="element.helpText"
                  v-i-tooltip="element.helpText"
                  icon="QuestionCircle"
                  class="size-4 text-neutral-600"
                />
                {{ element.label }}
              </IFormCheckbox>
            </div>

            <IButtonIcon
              v-if="!element.primary"
              icon="Selector"
              class="cursor-move"
            />
          </div>
        </template>
      </SortableDraggable>
    </div>

    <IFormGroup
      :label="$t('core::table.per_page')"
      label-for="tableSettingsPerPage"
    >
      <IFormSelect id="tableSettingsPerPage" v-model="perPage">
        <option v-for="number in [25, 50, 100]" :key="number" :value="number">
          {{ number }}
        </option>
      </IFormSelect>
    </IFormGroup>

    <IFormGroup
      :label="$t('core::table.max_height')"
      :description="$t('core::table.max_height_info')"
      label-for="tableSettingsMaxHeight"
    >
      <div class="relative mt-1 rounded-md shadow-sm">
        <IFormInput
          id="tableSettingsMaxHeight"
          v-model="maxHeight"
          type="number"
          min="200"
          step="50"
          class="pr-10"
          list="maxHeight"
        />

        <datalist id="maxHeight">
          <option value="200" />

          <option value="250" />

          <option value="300" />

          <option value="350" />

          <option value="400" />

          <option value="500" />
        </datalist>

        <div
          class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"
        >
          <span class="-mt-1 text-neutral-400">px</span>
        </div>
      </div>
    </IFormGroup>

    <div class="mt-5">
      <IFormCheckbox v-model:checked="bordered">
        {{ $t('core::table.bordered') }}
      </IFormCheckbox>

      <IFormCheckbox v-model:checked="condensed">
        {{ $t('core::table.condensed') }}
      </IFormCheckbox>

      <IFormGroup
        :description="pollingEnabled ? $t('core::table.polling_info') : null"
      >
        <IFormCheckbox
          v-model:checked="pollingEnabled"
          @change="pollingInterval = $event ? DEFAULT_POLLING_INTERVAL : null"
        >
          {{ $t('core::table.enable_polling') }}
        </IFormCheckbox>

        <IFormInput
          v-show="pollingEnabled"
          v-model="pollingInterval"
          type="number"
          min="10"
          class="mt-2"
          @blur="
            pollingInterval < MINIMUM_POLLING_INTERVAL
              ? (pollingInterval = MINIMUM_POLLING_INTERVAL)
              : undefined
          "
        />
      </IFormGroup>
    </div>

    <template #modal-footer>
      <div class="space-x-2 text-right">
        <IButton
          variant="white"
          :text="$t('core::app.cancel')"
          @click="customizeTable(false)"
        />

        <IButton
          variant="white"
          :disabled="form.busy"
          :text="$t('core::app.reset')"
          @click="reset"
        />

        <IButton
          variant="primary"
          :disabled="form.busy"
          :text="$t('core::app.save')"
          @click="save()"
        />
      </div>
    </template>
  </IModal>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue'
import cloneDeep from 'lodash/cloneDeep'
import filter from 'lodash/filter'
import find from 'lodash/find'
import findIndex from 'lodash/findIndex'
import orderBy from 'lodash/orderBy'

import { useForm } from '@/Core/composables/useForm'

import { useTable } from '../../composables/useTable'

const props = defineProps({
  tableId: { required: true, type: String },
  urlPath: { required: true, type: String },
})

const {
  settings: tableSettings,
  tableBeingCustomized,
  reloadTable,
  customizeTable,
} = useTable(props.tableId)

const sorted = ref([])
const mutableCustomizeableColumns = ref([])
const search = ref(null)
const maxHeight = ref(null)
const condensed = ref(false)
const bordered = ref(false)
const perPage = ref(null)

const MINIMUM_POLLING_INTERVAL = tableSettings.value.minimumPollingInterval
const DEFAULT_POLLING_INTERVAL = 25
const pollingInterval = ref(null)
const pollingEnabled = ref(false)

const { form } = useForm()

const customizeableColumns = computed(() =>
  filter(tableSettings.value.columns, 'customizeable')
)

const sortable = computed(() => filter(customizeableColumns.value, 'sortable'))

const isAddSortColumnDisabled = computed(() => {
  // Return true if all sortable columns are already sorted
  if (sorted.value.length === sortable.value.length) {
    return true
  }

  // Check if any sorted column has not been selected (has an empty 'attribute')
  return sorted.value.some(column => column.attribute === '')
})

function onColumnMove(data) {
  // You can't reorder primary columns or actions column
  // you can't add new columns before the first primary column
  // as the first primary column contains specific data table related to the table
  // You can't add new columns after the last primary column
  const { index, futureIndex } = data.draggedContext
  const isPrimaryColumn = idx => mutableCustomizeableColumns.value[idx].primary

  if (
    isPrimaryColumn(index) ||
    (futureIndex === 0 && isPrimaryColumn(futureIndex))
  ) {
    return false
  }
}

function isSortedColumnDisabled(attribute) {
  return Boolean(find(sorted.value, ['attribute', attribute]))
}

function addSortedColumn() {
  sorted.value.push({ attribute: '', direction: 'asc' })
}

function removeSorted(index) {
  sorted.value.splice(index, 1)
}

function handleModalHidden() {
  if (tableBeingCustomized.value) {
    customizeTable(false)
  }

  search.value = null
  setMutableCustomizeableColumns()
}

function setDefaults() {
  setTableConfig()
  setMutableCustomizeableColumns()
}

function setMutableCustomizeableColumns() {
  mutableCustomizeableColumns.value = filter(
    customizeableColumns.value,
    column => {
      return (
        !search.value ||
        column.label.toLowerCase().includes(search.value.toLowerCase())
      )
    }
  )

  mutableCustomizeableColumns.value.forEach(column => {
    column.visible = !column.hidden
  })
}

function prepareColumnsForStorage(columns) {
  return columns.map((column, index) => ({
    attribute: column.attribute,
    order: index + 1,
    width: column.width,
    wrap: column.wrap,
    hidden: !column.visible,
  }))
}

function reset() {
  request(form.clear()).then(initializeComponent)
}

function save(columns, reload = true) {
  const prepareColumns = () => {
    const defaultColumns = orderBy(customizeableColumns.value, col =>
      findIndex(mutableCustomizeableColumns.value, ['attribute', col.attribute])
    )

    return prepareColumnsForStorage(columns || defaultColumns)
  }

  const formData = form.clear().set({
    order: sorted.value.filter(column => column.attribute !== ''),
    columns: prepareColumns(),
    pollingInterval: pollingInterval.value,
    maxHeight: maxHeight.value,
    condensed: condensed.value,
    bordered: bordered.value,
    perPage: perPage.value,
  })

  return request(formData, reload)
}

async function request(form, reload = true) {
  const data = await form.post(`${props.urlPath}/settings`)

  tableSettings.value = data

  // Clear the search value as it's used in the "setMutableCustomizeableColumns" function
  // In case the user performed a search, will save only the filtered columns.
  search.value = ''

  nextTick(setDefaults)

  // We will re-query the table because the hidden columns are not queried
  // and in this case the data won't be shown
  if (reload) {
    nextTick(() => reloadTable())
  }

  customizeTable(false)
}

function setTableConfig() {
  pollingInterval.value = tableSettings.value.pollingInterval
  pollingEnabled.value = parseInt(tableSettings.value.pollingInterval) >= 10

  maxHeight.value = tableSettings.value.maxHeight
  condensed.value = tableSettings.value.condensed
  bordered.value = tableSettings.value.bordered
  perPage.value = tableSettings.value.perPage
}

function initializeComponent() {
  setDefaults()
  sorted.value = cloneDeep(tableSettings.value.order)
}

initializeComponent()

defineExpose({ save, onColumnMove })
</script>
