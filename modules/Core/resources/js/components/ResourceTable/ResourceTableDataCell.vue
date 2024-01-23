<template>
  <td
    :class="[
      'group/td relative',
      'group-hover/tr:bg-neutral-50 group-aria-selected/tr:bg-neutral-50 group-hover/tr:dark:bg-neutral-800 group-aria-selected/tr:dark:bg-neutral-800',
      isRequiredAndMissingValue
        ? '!bg-danger-100 !text-danger-800 ring-1 ring-inset ring-danger-200 ring-offset-4 group-hover/tr:ring-offset-neutral-50 group-aria-selected/tr:ring-offset-neutral-50 hover:!bg-danger-200 dark:ring-danger-300 dark:ring-offset-neutral-900 group-hover/tr:dark:ring-offset-neutral-800 group-aria-selected/tr:dark:ring-offset-neutral-800'
        : '',
      {
        'whitespace-nowrap': !wrap,
        'break-all': wrap,
        'text-left': align === 'left',
        'text-center': align === 'center',
        'text-right': align === 'right',
        'table-primary-column': isPrimary === true,
        'table-actions-column': isActionsColumn,
      },
    ]"
  >
    <div
      :class="[
        {
          flex: isSelectable,
          'whitespace-pre-line': newlineable,
        },
      ]"
    >
      <ActionColumnSeparator v-if="isActionsColumn" v-once />

      <PrimaryColumnSeparator v-else-if="isPrimary" v-once />

      <CheckboxSeparator v-if="isSelectable" v-once :condensed="condensed" />

      <IFormCheckbox
        v-if="isSelectable"
        :class="!condensed ? '-ml-1' : ''"
        :checked="isSelected"
        @click="$emit('selected', row)"
      />

      <div
        :class="[
          'overflow-hidden',
          isSelectable ? (condensed ? 'ml-2' : 'ml-6') : '',
        ]"
      >
        <component :is="link || route ? ILink : 'div'" v-bind="linkBindings">
          <slot />
        </component>
      </div>
    </div>
  </td>
</template>

<script setup>
import { computed } from 'vue'
import cloneDeep from 'lodash/cloneDeep'
import each from 'lodash/each'
import get from 'lodash/get'
import isObject from 'lodash/isObject'
import isString from 'lodash/isString'

import { isBlank } from '@/Core/utils'

import ILink from '../UI/ILink.vue'

import ActionColumnSeparator from './ActionColumnSeparator.vue'
import CheckboxSeparator from './CheckboxSeparator.vue'
import PrimaryColumnSeparator from './PrimaryColumnSeparator.vue'

const props = defineProps({
  row: { type: Object, required: true },
  attribute: { type: String, required: true },
  hasRequiredField: { type: Boolean, required: true },
  isPrimary: { type: Boolean, required: true },
  wrap: { type: Boolean, required: true },
  newlineable: { required: true, type: Boolean },
  customizeable: { required: true, type: Boolean },
  link: { required: true },
  route: { required: true },
  align: { type: String, default: 'left' },
  condensed: Boolean,
  isSortable: Boolean,
  isSelected: Boolean,
  isSelectable: Boolean,
})

defineEmits(['selected'])

const isActionsColumn = computed(() => props.attribute === 'actions')

const hasValue = computed(() => !isBlank(props.row[props.attribute]))

const isRequiredAndMissingValue = computed(
  () => props.hasRequiredField && !hasValue.value
)

const linkBindings = computed(() => {
  if (!props.route && !props.link) {
    return {}
  }

  if (props.route) {
    let to = cloneDeep(props.route)

    if (isObject(to)) {
      routeObjectToBindings(to.params || {})
      routeObjectToBindings(to.query || {})
    } else {
      to = replaceUrlBindings(to)
    }

    return { to }
  }

  return { href: replaceUrlBindings(props.link) }
})

function routeObjectToBindings(object) {
  each(object, (value, key) => {
    object[key] = isString(value) ? replaceUrlBindings(value) : value
  })
}

function replaceUrlBindings(url) {
  Object.keys(props.row).forEach(attribute => {
    url = url.replace('{' + attribute + '}', get(props.row, attribute))
  })

  return url
}
</script>
