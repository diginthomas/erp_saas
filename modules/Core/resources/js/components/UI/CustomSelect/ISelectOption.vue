<template>
  <li
    :id="`cs${uid}__option-${index}`"
    v-memo="[isHighlighted, isSelectable, label, swatchColor]"
    role="option"
    class="relative"
    :aria-selected="isHighlighted ? true : null"
  >
    <a
      href="#"
      :class="[
        'group block px-4 py-2 text-sm focus:outline-none',
        computedClasses,
      ]"
      @click.prevent="$emit('selected')"
      @mouseover.self.passive="
        isSelectable ? $emit('typeAheadPointer', index) : null
      "
    >
      <component
        :is="swatchColor ? TextBackground : 'span'"
        :color="swatchColor || undefined"
        :class="
          swatchColor
            ? 'inline-flex items-center justify-center rounded-full px-2.5 font-normal leading-5 dark:!text-white'
            : null
        "
      >
        <slot :label="label">
          {{ label }}
        </slot>
      </component>
    </a>

    <slot name="option-inner" :index="index"></slot>
  </li>
</template>

<script setup>
import { computed } from 'vue'

import TextBackground from '@/Core/components/TextBackground.vue'

const props = defineProps([
  'label',
  'uid',
  'index',
  'active',
  'isSelected',
  'isSelectable',
  'swatchColor',
])

defineEmits(['typeAheadPointer', 'selected'])

const isHighlighted = computed(() => props.isSelected || props.active)

const computedClasses = computed(() => ({
  'bg-neutral-100/80 text-neutral-700 hover:text-neutral-900 dark:bg-neutral-700 dark:text-neutral-100 dark:hover:text-white':
    isHighlighted.value,
  'text-neutral-700 dark:text-neutral-100 dark:hover:text-white':
    !isHighlighted.value,
  'pointer-events-none opacity-50': !props.isSelectable,
}))
</script>
