<template>
  <div class="isolate inline-flex flex-1 items-center">
    <IDropdown
      ref="dropdownRef"
      :placement="placement"
      size="md"
      rounded="left"
      icon="Filter"
      no-caret
      truncate="max-w-[13rem]"
      :text="activeFilter?.name || $t('core::filters.filters')"
      v-bind="$attrs"
    >
      <div class="w-[19rem]">
        <div
          class="border-b border-neutral-200 px-4 py-3 dark:border-neutral-700"
        >
          <ILink
            :text="$t('core::filters.new')"
            :class="[
              'mr-2',
              {
                'border-r border-neutral-300 pr-2 dark:border-neutral-700':
                  activeFilter,
              },
            ]"
            @click="initiateNewFilter"
          />

          <ILink
            v-show="activeFilter"
            :text="$t('core::filters.edit')"
            class="border-r border-neutral-300 pr-2 dark:border-neutral-700"
            @click="initiateEdit"
          />

          <ILink
            v-show="activeFilter"
            :text="$t('core::filters.clear_applied')"
            class="pl-2"
            @click="clearActive"
          />

          <SearchInput
            v-model="search"
            size="sm"
            class="mt-3"
            :placeholder="$t('core::filters.search')"
            @keydown.stop="() => 'stop headless ui trying active click'"
          />
        </div>

        <p
          v-show="hasSavedFilters && !searchResultIsEmpty"
          class="mt-1 inline-flex items-center truncate px-4 py-2 text-sm font-medium text-neutral-900 dark:text-neutral-200"
        >
          <Icon
            icon="Bars3"
            class="mr-2 size-5 text-neutral-500 dark:text-neutral-300"
          />
          {{ $t('core::filters.available') }}
        </p>

        <p
          v-show="!hasSavedFilters || searchResultIsEmpty"
          v-t="'core::filters.not_available'"
          class="block px-4 py-2 text-sm text-neutral-500 dark:text-neutral-300"
        />

        <div
          v-show="hasSavedFilters && !searchResultIsEmpty"
          class="inline-block max-h-80 w-full overflow-auto"
        >
          <FilterDropdownItem
            v-for="filter in filteredList"
            :key="filter.id"
            :identifier="identifier"
            :view="view"
            :filter-id="filter.id"
            :name="filter.name"
            @click="handleFilterSelected"
          />
        </div>
      </div>
    </IDropdown>

    <IButton
      v-show="editButtonIsVisible"
      v-i-tooltip="$t('core::filters.edit_filter')"
      rounded="right"
      variant="white"
      icon="Pencil"
      size="md"
      class="-ml-px focus:z-10"
      @click="toggleFiltersBuilderVisibility"
    />

    <IButton
      v-show="addFilterButtonIsVisible"
      v-i-tooltip="$t('core::filters.add_filter')"
      rounded="right"
      variant="white"
      class="-ml-px focus:z-10"
      size="md"
      icon="Plus"
      @click="toggleFiltersBuilderVisibility"
    />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

import { emitGlobal } from '@/Core/composables/useGlobalEventListener'
import { useQueryBuilder } from '@/Core/composables/useQueryBuilder'

import { useFilterable } from '../../composables/useFilterable'

import FilterDropdownItem from './FiltersDropdownItem.vue'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps({
  placement: { default: 'bottom-end', type: String },
  identifier: { required: true, type: String },
  view: { required: true, type: String },
})

const emit = defineEmits(['apply'])

const { queryBuilderRules, hasRulesApplied } = useQueryBuilder(
  props.identifier,
  props.view
)

const {
  filters,
  activeFilter,
  filtersBuilderVisible,
  toggleFiltersBuilderVisibility,
} = useFilterable(props.identifier, props.view)

const dropdownRef = ref(null)
const search = ref(null)

const searchResultIsEmpty = computed(
  () => search.value && filteredList.value.length === 0
)

const editButtonIsVisible = computed(
  () => hasRulesApplied.value || activeFilter.value
)

const addFilterButtonIsVisible = computed(
  () => !hasRulesApplied.value && !activeFilter.value
)

const hasSavedFilters = computed(() => filters.value.length > 0)

const filteredList = computed(() => {
  if (!search.value) {
    return filters.value
  }

  return filters.value.filter(filter => {
    return filter.name.toLowerCase().includes(search.value.toLowerCase())
  })
})

function hide() {
  dropdownRef.value.hide()
}

function clearActive() {
  activeFilter.value = null

  emit('apply', queryBuilderRules.value)
}

function initiateEdit() {
  toggleFiltersBuilderVisibility()
  hide()
}

function initiateNewFilter() {
  if (activeFilter.value) {
    clearActive()
  }

  filtersBuilderVisible.value = true

  hide()
}

function handleFilterSelected(id) {
  emitGlobal(`${props.identifier}-${props.view}-filter-selected`, id)
}
</script>
