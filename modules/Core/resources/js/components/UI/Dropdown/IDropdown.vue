<template>
  <Menu>
    <Float
      ref="floatRef"
      :placement="placement"
      :arrow="arrow"
      :show="visible"
      :offset="offset"
      :z-index="zIndex"
      :adaptive-width="adaptiveWidth"
      :auto-placement="autoPlacement"
      :shift="shift"
      :flip="flip"
      portal
      enter="transition ease-out duration-100"
      enter-from="transform opacity-0 scale-95"
      enter-to="transform opacity-100 scale-100"
      leave="transition ease-in duration-75"
      leave-from="transform opacity-100 scale-100"
      leave-to="transform opacity-0 scale-95"
    >
      <slot name="toggle" :toggle="toggle" :no-caret="noCaret" :attrs="$attrs">
        <MenuButton
          v-bind="$attrs"
          :variant="isDefaultButtonTag ? variant : undefined"
          :block="isDefaultButtonTag && fullWidth ? true : undefined"
          :as="tag"
          @click.prevent="toggle"
        >
          <slot name="toggle-content">
            <span
              v-if="truncate"
              :class="[
                'truncate',
                typeof truncate === 'string' ? truncate : '',
              ]"
              v-text="text"
            />

            <template v-else>
              {{ text }}
            </template>
          </slot>

          <span v-if="!noCaret" class="ml-auto shrink-0">
            <Icon icon="ChevronDown" class="ml-2 size-4" />
          </span>
        </MenuButton>
      </slot>

      <MenuItems
        ref="menuItemsRef"
        static
        :class="[
          '__dropdown rounded-md border border-neutral-200 bg-white shadow-lg outline-none dark:border-neutral-700 dark:bg-neutral-800',
          itemsClass,
        ]"
      >
        <FloatArrow
          v-if="arrow"
          class="absolute size-5 rotate-45 border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800"
        />

        <div class="relative rounded-md bg-white dark:bg-neutral-800">
          <slot />
        </div>
      </MenuItems>
    </Float>
  </Menu>
</template>

<script setup>
import { computed, onMounted, provide, ref } from 'vue'
import { Menu, MenuButton, MenuItems } from '@headlessui/vue'
import { Float, FloatArrow } from '@headlessui-float/vue'
import { onClickOutside } from '@vueuse/core'

import IButton from '@/Core/components/UI/Button/IButton.vue'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps({
  tag: { default: IButton },
  fullWidth: { default: true, type: Boolean },
  truncate: [Boolean, String],
  variant: { type: String, default: 'white' },
  itemsClass: [String, Array, Object],
  arrow: { type: Boolean, default: true },
  placement: { type: String, default: 'bottom' },
  zIndex: { type: Number, default: 1250 },
  offset: { type: [Number, Function, Object], default: 15 },
  flip: { type: [Boolean, Number], default: true },
  text: [String, Number],
  adaptiveWidth: Boolean,
  autoPlacement: Boolean,
  shift: [Boolean, Number],
  noCaret: Boolean,
})

const emit = defineEmits(['show', 'hide'])

provide('hide', hide)

const visible = ref(false)

const menuItemsRef = ref(null)
const floatRef = ref(null)

const isDefaultButtonTag = computed(() => props.tag === IButton)

onMounted(configureAutoClose)

function configureAutoClose() {
  onClickOutside(menuItemsRef, hide, {
    ignore: ['.__popper', floatRef.value.$el.nextSibling],
  })
}

function toggle() {
  visible.value ? hide() : show()
}

function show() {
  visible.value = true
  emit('show')
}

function hide() {
  visible.value = false
  emit('hide')
}

defineExpose({
  show,
  hide,
})
</script>
