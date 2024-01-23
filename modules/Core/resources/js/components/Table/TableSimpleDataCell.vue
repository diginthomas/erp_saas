<template>
  <td :class="['whitespace-nowrap', item.tdClass, field.tdClass]">
    <slot v-bind="slotProps"></slot>
  </td>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  field: { type: Object, required: true },
  item: { type: Object, required: true },
  formatter: { type: Function, required: true },
})

const slotProps = computed(() => ({
  row: props.item,
  column: props.field,
  formatted: formatted.value,
}))

const formatted = computed(() => props.formatter(props.item, props.field))
</script>
