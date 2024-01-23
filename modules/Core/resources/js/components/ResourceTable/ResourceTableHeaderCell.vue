<template>
  <th
    ref="thRef"
    :class="[
      'group/th relative',
      {
        'table-primary-column': isPrimary,
        'table-actions-column': isActionsColumn,
        'text-left': align === 'left',
        'text-center': align === 'center',
        'text-right': align === 'right',
        'cursor-pointer hover:bg-neutral-100 hover:text-neutral-700 dark:hover:bg-neutral-600 dark:hover:text-neutral-100':
          hasMenu,
      },
    ]"
    :style="{
      maxWidth: isActionsColumn ? width : undefined,
    }"
  >
    <ResourceTableHeaderCellMenu
      v-if="hasMenu"
      :attribute="attribute"
      :is-sortable="isSortable"
      :is-primary="isPrimary"
      :customizeable="customizeable"
      :label="label"
      :wrap="wrap"
      @updated="$emit('updated', $event)"
      @sort-asc="$emit('sortAsc', $event)"
      @sort-desc="$emit('sortDesc', $event)"
    />

    <ActionColumnSeparator v-if="isActionsColumn" v-once />

    <PrimaryColumnSeparator v-else-if="isPrimary" v-once />

    <CheckboxSeparator
      v-if="isSelectable"
      v-once
      :condensed="condensed"
      class="z-20"
    />

    <div
      v-if="!isActionsColumn"
      :class="[
        'inline-flex items-center',
        isSelectable ? (condensed ? 'space-x-2' : 'space-x-6') : '',
      ]"
    >
      <IFormCheckbox
        v-if="isSelectable"
        :class="!condensed ? '-ml-1' : ''"
        :indeterminate="indeterminate"
        :checked="indeterminate || allRowsSelected"
        @change="$emit('checkboxChanged', indeterminate)"
      />

      <div :class="['inline-flex items-center', isSelectable ? 'mt-px' : '']">
        <span
          :class="['truncate', isOrdered ? 'mr-2' : '']"
          :style="{
            maxWidth: `${parseInt(width, 10) - totalXMargin}px`,
          }"
        >
          <slot>{{ label }}</slot>
        </span>

        <Icon
          :icon="sortIcon"
          :class="[
            'size-4 text-sm text-neutral-700 dark:text-neutral-200',
            !isOrdered ? 'hidden' : '',
          ]"
        />
      </div>
    </div>

    <div v-else class="flex items-center justify-center">
      <ILink v-if="tableCustomizeable" plain>
        <Icon icon="Cog" class="size-5" @click="$emit('customize')" />
      </ILink>
    </div>
  </th>
</template>

<script setup>
import { computed, ref } from 'vue'

import ActionColumnSeparator from './ActionColumnSeparator.vue'
import CheckboxSeparator from './CheckboxSeparator.vue'
import PrimaryColumnSeparator from './PrimaryColumnSeparator.vue'
import ResourceTableHeaderCellMenu from './ResourceTableHeaderCellMenu.vue'

const props = defineProps({
  // Whether the current column is ordered
  isOrdered: Boolean,
  wrap: Boolean,
  attribute: { type: String, required: true },
  customizeable: { type: Boolean, required: true },
  tableCustomizeable: { type: Boolean, required: true },
  width: { required: true },
  label: { required: true },
  align: { type: String, default: 'left' },
  condensed: Boolean,
  isSortedAscending: Boolean,
  isPrimary: Boolean,
  isSelectable: Boolean,
  isSortable: Boolean,
  allRowsSelected: Boolean,
  totalSelected: Number,
})

defineEmits(['checkboxChanged', 'sortAsc', 'sortDesc', 'updated', 'customize'])

const totalXMargin = 25

const thRef = ref(null)

const indeterminate = computed(
  () => props.totalSelected > 0 && !props.allRowsSelected
)

const sortIcon = computed(() =>
  props.isSortedAscending ? 'ArrowUpSolid' : 'ArrowDownSolid'
)

const isActionsColumn = computed(() => props.attribute === 'actions')

const hasMenu = computed(() => props.isSortable || props.customizeable)
</script>
