<template>
  <div class="flex flex-col items-center justify-between px-6 py-3 md:flex-row">
    <TablePerPageOptions
      v-model:per-page="collection.perPage"
      :per-page-options="collection.perPageOptions"
      class="mb-2 md:mb-0"
      :disabled="isLoading"
      @change="loadItems"
    />

    <div v-if="searchable" class="w-full md:w-auto">
      <SearchInput v-model="search" @input="performSearch" />
    </div>
  </div>

  <IOverlay :show="isLoading">
    <ITable :id="tableId" v-bind="$attrs" ref="tableRef" bordered-inner="y">
      <thead>
        <tr>
          <HeaderCell
            v-for="field in fields"
            :key="'th-' + field.key"
            ref="tableHeadingsRef"
            v-model:ctx="ctx"
            :class="{
              hidden: stacked[field.key],
            }"
            :is-sortable="field.sortable"
            :heading="field.label"
            :heading-key="field.key"
            @update:ctx="loadItems"
          />
        </tr>
      </thead>

      <tbody>
        <template v-for="item in collection.items" :key="'tr-' + item.id">
          <slot name="before-row" :row="item"></slot>

          <tr :class="[item.trClass ? item.trClass : null]">
            <DataCell
              v-for="field in fields"
              :key="'td-' + field.key"
              v-slot="slotProps"
              :field="field"
              :item="item"
              :formatter="dataCellFormatter"
              :class="{
                hidden: stacked[field.key],
              }"
            >
              <slot v-bind="slotProps" :name="field.key">
                <ILink
                  v-if="floatFirst && field.key === fields[0].key"
                  :text="slotProps.formatted"
                  class="font-medium"
                  @click="
                    floatResource({
                      resourceName: floatFirst.resourceName,
                      resourceId: item.id,
                      mode: floatFirst.mode,
                    })
                  "
                />

                <ILink
                  v-else-if="field.key === fields[0].key && item.path"
                  :to="item.path"
                  class="font-medium"
                  :text="slotProps.formatted"
                />

                <span v-else v-text="slotProps.formatted"> </span>
              </slot>
              <!-- Stacked -->
              <template v-if="field.key === fields[0].key">
                <StackedDataCell
                  v-for="stackedField in stackedFields"
                  :key="'stacked-' + stackedField.key"
                  :field="stackedField"
                  :item="item"
                  :formatter="dataCellFormatter"
                >
                  <template #default="stackedSlotProps">
                    <slot v-bind="stackedSlotProps" :name="stackedField.key">
                      <span class="text-neutral-700 dark:text-neutral-300">
                        {{ stackedSlotProps.formatted }}
                      </span>
                    </slot>
                  </template>
                </StackedDataCell>
              </template>
            </DataCell>
          </tr>

          <slot name="after-row" :row="item"></slot>
        </template>

        <tr v-if="!collection.hasItems">
          <td :colspan="totalFields" class="is-empty">
            <slot
              name="empty"
              :text="emptyText"
              :loading="isLoading"
              :search="search"
            >
              {{ emptyText }}
            </slot>
          </td>
        </tr>
      </tbody>
    </ITable>
  </IOverlay>

  <TablePagination
    v-if="collection.hasPagination"
    class="px-6 py-3"
    :is-current-page-check="page => collection.isCurrentPage(page)"
    :has-next-page="collection.hasNextPage"
    :has-previous-page="collection.hasPreviousPage"
    :links="collection.pagination"
    :render-links="collection.shouldRenderLinks"
    :from="collection.from"
    :to="collection.to"
    :total="collection.total"
    :loading="isLoading"
    size="sm"
    @go-to-next="collection.nextPage()"
    @go-to-previous="collection.previousPage()"
    @go-to-page="collection.page($event)"
  />
</template>

<script setup>
import { computed, nextTick, onUnmounted, reactive, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useEventListener } from '@vueuse/core'
import debounce from 'lodash/debounce'

import { useFloatingResourceModal } from '@/Core/composables/useFloatingResourceModal'

import { useLoader } from '../../composables/useLoader'
import { useResponsiveTable } from '../../composables/useResponsiveTable'
import { CancelToken } from '../../services/HTTP'
import Paginator from '../../services/ResourcePaginator'

import TablePagination from './TablePagination.vue'
import TablePerPageOptions from './TablePerPageOptions.vue'
import DataCell from './TableSimpleDataCell.vue'
import HeaderCell from './TableSimpleHeaderCell.vue'
import StackedDataCell from './TableSimpleStackedDataCell.vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  stackable: Boolean,
  requestUri: { required: true, type: String },
  requestQueryString: Object,
  tableId: { required: true, type: String },
  floatFirst: Object,
  actionColumn: Boolean,
  initialData: Object,
  fields: Array,
  searchable: { type: Boolean, default: true },
  // Initial sort by field key/name
  sortBy: String,
})

const emit = defineEmits(['dataLoaded'])

const { t } = useI18n()
const { setLoading, isLoading } = useLoader()
const { floatResource } = useFloatingResourceModal()
const { isColumnVisible } = useResponsiveTable()

const tableRef = ref(null)
const search = ref('')
const initialDataSet = ref(false)

let requestCancelToken = null

const collection = ref(new Paginator())
const replaceCollectionData = ref(null)
const stacked = reactive({})

const ctx = ref({
  sortBy: null,
  direction: null,
})

const tableHeadingsRef = ref([])

const emptyText = computed(() => {
  if (collection.value.hasItems) {
    return ''
  }

  if (isLoading.value) {
    return '...'
  }

  if (search.value) {
    return t('core::app.no_search_results')
  }

  return t('core::table.empty')
})

const stackedFields = computed(() =>
  props.fields.filter(field => stacked[field.key])
)

const totalFields = computed(() => props.fields.length)

const queryString = computed(() => ({
  page: collection.value.currentPage,
  per_page: collection.value.perPage,
  q: search.value,
  order: [
    {
      field: ctx.value.sortBy || props.sortBy,
      direction: ctx.value.direction || 'asc',
    },
  ],
  ...(props.requestQueryString || {}),
}))

const performSearch = debounce(loadItems, 400)

watch(() => collection.value.currentPage, loadItems, { immediate: true })

function dataCellFormatter(item, field) {
  return field.formatter
    ? field.formatter(item[field.key], field.key, item)
    : item[field.key]
}

function replaceCollection(data) {
  replaceCollectionData.value = data
  reload()
}

function reload() {
  loadItems()
}

async function request() {
  cancelPreviousRequest()

  setLoading(true)

  let { data } = await Innoclapps.request(`/${props.requestUri}`, {
    params: queryString.value,
    cancelToken: new CancelToken(token => (requestCancelToken = token)),
  })

  // cards support data.value
  collection.value.setState(data.value ? data.value : data)

  emit('dataLoaded', {
    items: collection.value.items,
    requestQueryString: queryString.value,
  })

  props.stackable && nextTick(stackColumns)

  setLoading(false)
}

function loadItems() {
  if (!initialDataSet.value && props.initialData) {
    initialDataSet.value = true
    collection.value.setState(props.initialData)
    props.stackable && nextTick(stackColumns)
  } else if (replaceCollectionData.value !== null) {
    collection.value.setState(replaceCollectionData.value)
    replaceCollectionData.value = null
    props.stackable && nextTick(stackColumns)
  } else {
    request()
  }
}

function cancelPreviousRequest() {
  if (requestCancelToken) {
    requestCancelToken()
  }
}

function stackColumns() {
  props.fields.forEach((field, idx) => {
    if (idx > 0 && tableHeadingsRef.value[idx]) {
      stacked[field.key] = !isColumnVisible(
        tableHeadingsRef.value[idx].$el,
        tableRef.value.$wrapperEl
      )
    }
  })
}

if (props.stackable) {
  useEventListener(window, 'resize', stackColumns)
}

onUnmounted(() => {
  collection.value.flush()
})

defineExpose({ reload, replaceCollection })
</script>
