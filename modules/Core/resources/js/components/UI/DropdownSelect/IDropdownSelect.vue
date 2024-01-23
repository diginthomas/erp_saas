<template>
  <IDropdown items-class="max-w-xs sm:max-w-sm">
    <template #toggle="{ toggle, noCaret, attrs }">
      <slot :label="toggleLabel" :toggle="toggle" :disabled="disabled">
        <button
          type="button"
          :disabled="disabled"
          class="inline-flex items-center rounded-md px-2 py-1 text-sm text-neutral-800 hover:bg-neutral-100 hover:text-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-300 disabled:pointer-events-none disabled:opacity-60 dark:text-white dark:hover:bg-neutral-700 dark:hover:text-neutral-200 dark:focus:ring-neutral-500"
          v-bind="attrs"
          @click="toggle"
        >
          <slot name="label" :label="toggleLabel">
            <span
              v-if="truncate"
              :class="[
                'truncate',
                typeof truncate === 'string' ? truncate : '',
              ]"
              v-text="toggleLabel"
            />

            <template v-else>
              {{ toggleLabel }}
            </template>
          </slot>

          <span v-if="!noCaret" class="ml-auto shrink-0">
            <Icon
              icon="ChevronDown"
              class="ml-2 size-4 text-neutral-500 dark:text-neutral-400"
            />
          </span>
        </button>
      </slot>
    </template>

    <div class="overflow-y-auto py-1" :style="{ maxHeight: maxHeight }">
      <slot name="header"></slot>

      <IDropdownSelectItem
        v-for="item in items"
        :key="isObject(item) ? item[valueKey] : item"
        :item="item"
        :condensed="condensed"
        :label-key="labelKey"
        @click="handleClickEvent"
      />

      <slot name="footer"></slot>
    </div>
  </IDropdown>
</template>

<script setup>
import { computed } from 'vue'
import isEqual from 'lodash/isEqual'
import isObject from 'lodash/isObject'

import IDropdownSelectItem from './IDropdownSelectItem.vue'

const props = defineProps({
  modelValue: { required: true },
  label: String,
  condensed: Boolean,
  truncate: [Boolean, String],
  disabled: Boolean,
  items: { type: Array, default: () => [] },
  maxHeight: { type: String, default: '500px' },
  // If values are object
  labelKey: { type: String, default: 'label' },
  valueKey: { type: String, default: 'value' },
})

const emit = defineEmits(['change', 'update:modelValue'])

const toggleLabel = computed(() => {
  if (props.label) {
    return props.label
  }

  if (isObject(props.modelValue)) {
    return props.modelValue[props.labelKey]
  } else if (
    typeof props.modelValue === 'string' ||
    typeof props.modelValue === 'number' ||
    props.modelValue === null
  ) {
    if (isObject(props.items[0])) {
      let item = props.items.find(
        item => item[props.valueKey] == props.modelValue
      )

      return item ? item[props.labelKey] : ''
    }

    return props.items.find(item => item == props.modelValue) || ''
  }

  return props.modelValue
})

function handleClickEvent(active) {
  emit('update:modelValue', active)

  if (!isEqual(active, props.modelValue)) {
    emit('change', active)
  }
}
</script>
