<template>
  <span :style="style">
    <slot>{{ text }}</slot>
  </span>
</template>

<script setup>
import { computed } from 'vue'
import hexRgb from 'hex-rgb'

import { getContrast, shadeColor } from '@/Core/utils'

const props = defineProps({
  text: [String, Number],
  color: String,
  // When false uses black white color contrast with original background
  shade: { type: Boolean, default: true },
  // Only when shade is true
  bgOpacity: { type: [String, Number], default: 0.1 },
  colorShade: { type: [String, Number], default: -50 },
})

const style = computed(() => {
  if (!props.color || props.color.length !== 7) {
    return null
  }

  if (!props.shade) {
    return {
      background: props.color,
      color: getContrast(props.color),
    }
  }

  const rgbObject = hexRgb(props.color)
  let rgbBackgroud = `rgba(${rgbObject.red}, ${rgbObject.green}, ${rgbObject.blue}, ${props.bgOpacity})`

  return {
    background: rgbBackgroud,
    color: shadeColor(props.color, props.colorShade),
  }
})
</script>
