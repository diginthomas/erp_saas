<template>
  <img :class="computedClasses" :src="src" />
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  src: {
    type: String,
    required: true,
  },
  size: {
    default: 'sm',
    type: String,
    validator(value) {
      return ['xs', 'sm', 'lg'].includes(value)
    },
  },
})

const sizeMaps = {
  xs: 'size-5',
  sm: 'size-8',
  lg: 'size-10',
}

const computedClasses = computed(() => [
  'inline-block rounded-full',
  sizeMaps[props.size],
])
</script>
