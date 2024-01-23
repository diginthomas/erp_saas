<template>
  <IDropdownItem
    :class="dropdownClass"
    :icon="icon"
    :prepend-icon="prependIcon"
    :disabled="isDisabled"
    @click="$emit('click', item)"
  >
    {{ label }}
  </IDropdownItem>
</template>

<script setup>
import { computed } from 'vue'
import isObject from 'lodash/isObject'

const props = defineProps(['item', 'labelKey'])

defineEmits(['click'])

const asObject = computed(() => isObject(props.item))

const label = computed(() =>
  asObject.value ? props.item[props.labelKey] : props.item
)

const isDisabled = computed(
  () => asObject.value && props.item.disabled === true
)

const icon = computed(() =>
  asObject.value && props.item.icon ? props.item.icon : null
)

const prependIcon = computed(() =>
  asObject.value && Object.hasOwn(props.item, 'prependIcon')
    ? props.item.prependIcon
    : false
)

const dropdownClass = computed(() =>
  asObject.value && Object.hasOwn(props.item, 'class') && props.item.class
    ? props.item.class
    : null
)
</script>
