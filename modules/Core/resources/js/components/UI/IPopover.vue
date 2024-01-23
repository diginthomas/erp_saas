<template>
  <Popover ref="popperRef">
    <Float
      v-bind="{ ...$attrs, class: {} }"
      :arrow="arrow"
      :offset="10"
      :z-index="1250"
      :placement="placement"
      portal
      :show="localVisible"
      enter="transition ease-out duration-100"
      enter-from="transform opacity-0 scale-95"
      enter-to="transform opacity-100 scale-100"
      leave="transition ease-in duration-75"
      leave-from="transform opacity-100 scale-100"
      leave-to="transform opacity-0 scale-95"
    >
      <PopoverButton as="template" @click="toggle">
        <slot />
      </PopoverButton>

      <PopoverPanel
        ref="panelRef"
        static
        :class="[
          $attrs.class,
          'overflow-hidden rounded-md focus:outline-none',
          colorMaps[variant].backgroundColorClasses,
          bordered ? 'border ' + colorMaps[variant].borderColorClasses : '',
          {
            'shadow-lg': shadow,
          },
        ]"
      >
        <FloatArrow
          v-if="arrow"
          :class="[
            'absolute size-5 rotate-45',
            colorMaps[variant].backgroundColorClasses,
            bordered ? 'border ' + colorMaps[variant].borderColorClasses : '',
          ]"
        />

        <div
          :class="[
            'relative rounded-md',
            colorMaps[variant].backgroundColorClasses,
          ]"
        >
          <div
            v-if="title || $slots.title || closeable"
            :class="[
              colorMaps[variant].backgroundColorClasses,
              'px-4 py-2.5',
              bordered
                ? 'border-b ' + colorMaps[variant].borderColorClasses
                : '',
            ]"
          >
            <div class="flex items-center justify-between">
              <h4
                class="text-[0.9rem] font-medium text-neutral-800 dark:text-neutral-100"
                v-text="title"
              />

              <IButtonIcon
                v-if="closeable"
                class="mt-0.5"
                icon="X"
                @click="hide"
              />
            </div>
          </div>

          <IOverlay :show="busy">
            <slot name="popper"></slot>
          </IOverlay>
        </div>
      </PopoverPanel>
    </Float>
  </Popover>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
import { Float, FloatArrow } from '@headlessui-float/vue'
import { onClickOutside } from '@vueuse/core'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps({
  busy: Boolean,
  variant: { type: String, default: 'white' },
  placement: { type: String, default: 'bottom' },
  bordered: { type: Boolean, default: true },
  arrow: { type: Boolean, default: true },
  shadow: { type: Boolean, default: true },
  visible: Boolean,
  title: String,
  closeable: Boolean,
  disabled: Boolean,
})

const emit = defineEmits(['show', 'hide', 'update:visible'])

const colorMaps = {
  white: {
    backgroundColorClasses: 'bg-white dark:bg-neutral-800',
    borderColorClasses: 'border-neutral-200 dark:border-neutral-700',
  },
}

const localVisible = ref(false)
const panelRef = ref(null)
const popperRef = ref(null)

onMounted(configureAutoClose)

function configureAutoClose() {
  onClickOutside(panelRef, hide, {
    ignore: ['.__popper', '.__dropdown', '.tox', popperRef.value.$el],
  })
}

function toggle() {
  localVisible.value ? hide() : show()
}

function show() {
  if (props.disabled === true) {
    return
  }
  localVisible.value = true
  emit('show')
  emit('update:visible', true)
}

function hide() {
  if (props.disabled === true) {
    return
  }
  localVisible.value = false
  emit('hide')
  emit('update:visible', false)
}

watch(
  () => props.visible,
  (newVal, oldVal) => {
    if (newVal) {
      show()
    } else if (oldVal) {
      hide()
    }
  },
  {
    // immediate: true,
  }
)

defineExpose({
  show,
  hide,
})
</script>
