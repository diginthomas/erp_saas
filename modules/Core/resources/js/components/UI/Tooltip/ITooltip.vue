<template>
  <Teleport to="body">
    <div
      ref="tooltipRef"
      class="pointer-events-none absolute left-0 top-0 z-[1300]"
    >
      <Transition
        enter-active-class="duration-200 ease"
        enter-from-class="transform opacity-0"
        leave-to-class="transform opacity-0"
      >
        <div
          v-if="activeElement"
          :class="[
            'overflow-hidden rounded-md',
            variantClasses[variant].wrapper,
          ]"
        >
          <div
            ref="arrowRef"
            :class="variantClasses[variant].arrow"
            class="absolute size-2 rotate-45"
          />

          <div class="relative max-w-sm" :class="variantClasses[variant].inner">
            <div
              class="px-4 py-1.5 text-center text-sm"
              :class="variantClasses[variant].content"
            >
              {{ content }}
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </Teleport>
</template>

<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { arrow, computePosition, shift } from '@floating-ui/dom'
import { offset as floatingOffset } from '@floating-ui/dom'

const props = defineProps({
  delay: { type: Number, default: 300 },
  offset: { type: Number, default: 8 },
})

const variantClasses = {
  dark: {
    wrapper: 'border border-neutral-900/90 shadow-lg bg-neutral-900/90',
    inner: 'bg-neutral-900/90',
    content: 'text-white',
    arrow: 'border border-neutral-900/90 bg-neutral-900/90',
  },
  light: {
    wrapper:
      'border border-neutral-200 dark:border-neutral-700 shadow-lg bg-white dark:bg-neutral-800',
    inner: 'bg-white dark:bg-neutral-800',
    content: 'text-neutral-700 dark:text-neutral-200',
    arrow:
      'border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800',
  },
}

const arrowRef = ref()
const tooltipRef = ref()
const content = ref('')
const variant = ref('dark')
const activeElement = ref(null)
const delayTimeout = ref(null)

const mouseoverListener = e => {
  const target = e.target.closest('[v-tooltip]')

  if (delayTimeout.value) {
    clearTimeout(delayTimeout.value)
    delayTimeout.value = null
  }

  if (activeElement.value !== target) {
    activeElement.value = null
  }

  if (target) {
    delayTimeout.value = setTimeout(() => {
      activeElement.value = target
    }, props.delay)
  }
}

const showTooltip = async () => {
  if (!activeElement.value) return

  await nextTick()

  const elPlacement = activeElement.value.getAttribute('v-tooltip-placement')
  variant.value = activeElement.value.getAttribute('v-tooltip-variant')
  content.value = activeElement.value.getAttribute('v-tooltip') || ''

  const options = {
    placement: elPlacement,
    middleware: [
      floatingOffset(props.offset),
      shift(),
      arrow({ element: arrowRef.value }),
    ],
  }

  computePosition(activeElement.value, tooltipRef.value, options).then(
    ({ x, y, placement, middlewareData }) => {
      Object.assign(tooltipRef.value.style, {
        left: `${x}px`,
        top: `${y}px`,
      })

      const staticSide = {
        top: 'bottom',
        right: 'left',
        bottom: 'top',
        left: 'right',
      }[placement.split('-')[0]]

      Object.assign(arrowRef.value.style, {
        ...(middlewareData.arrow?.y && {
          top: `${middlewareData.arrow.y}px`,
        }),
        ...(middlewareData.arrow?.x && {
          left: `${middlewareData.arrow.x}px`,
        }),
        right: '',
        bottom: '',
        [staticSide]: '-0.25rem',
      })
    }
  )
}

const hideTooltip = () => {
  activeElement.value = null
  content.value = null
}

watch(activeElement, async el => {
  if (el) await showTooltip()
  else hideTooltip()
})

onMounted(() => {
  document.addEventListener('mouseover', mouseoverListener)
})

onBeforeUnmount(() => {
  if (delayTimeout.value) clearTimeout(delayTimeout.value)
  document.removeEventListener('mouseover', mouseoverListener)
})
</script>
