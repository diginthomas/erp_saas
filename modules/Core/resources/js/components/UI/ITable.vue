<template>
  <div
    ref="elRef"
    :style="{ maxHeight: maxHeight }"
    :class="[
      responsive ? 'table-responsive' : '',
      sticky ? 'table-sticky-header' : '',
      wrapperClass,
    ]"
  >
    <table
      v-bind="$attrs"
      ref="tableRef"
      :class="[
        'table-primary',
        borderedInner === true
          ? 'table-bordered-inner-y table-bordered-inner-x'
          : '',
        borderedInner === 'y' ? 'table-bordered-inner-y' : '',
        borderedInner === 'x' ? 'table-bordered-inner-x' : '',
        bordered === true ? 'table-bordered-y table-bordered-x' : '',
        bordered === 'y' ? 'table-bordered-y' : '',
        bordered === 'x' ? 'table-bordered-x' : '',
        condensed ? 'table-condensed' : '',
        sticky ? 'border-separate' : '',
        fixedLayout ? 'table-fixed' : '',
      ]"
      :style="{ borderSpacing: sticky ? 0 : undefined }"
    >
      <slot />
    </table>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineOptions({
  inheritAttrs: false,
})

defineProps({
  maxHeight: String,
  condensed: Boolean,
  responsive: { type: Boolean, default: true },
  wrapperClass: [String, Object, Array],
  fixedLayout: Boolean,
  bordered: { type: [Boolean, String], default: false },
  borderedInner: { type: [Boolean, String], default: false },
  sticky: Boolean,
})

const elRef = ref(null)
const tableRef = ref(null)

defineExpose({ $el: tableRef, $wrapperEl: elRef })
</script>
