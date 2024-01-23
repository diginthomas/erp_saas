<template>
  <component
    :is="tag"
    v-bind="$attrs"
    :icon="tag === 'IButtonIcon' ? $attrs.icon || 'Clipboard' : undefined"
    @click="performCopy"
  >
    <slot />
  </component>
</template>

<script setup>
import { toRef } from 'vue'
import { useClipboard } from '@vueuse/core'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  text: [String, Number],
  tag: { type: [String, Object], default: 'IButtonIcon' },
  successMessage: {
    type: String,
    default: 'Text copied to clipboard.',
  },
})

const { copy } = useClipboard({
  source: toRef(props, 'text'),
  legacy: true,
})

function performCopy() {
  copy()

  Innoclapps.info(props.successMessage)
}
</script>
