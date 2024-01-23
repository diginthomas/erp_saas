<template>
  <TransitionRoot as="template" :show="isOpen">
    <Dialog
      as="div"
      static
      class="dialog relative"
      :open="isOpen"
      :initial-focus="initialFocus"
      @close="maybePreventClosing"
    >
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <IDialogOverlay v-show="overlay" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div
          class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
        >
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel
              :as="form ? 'form' : 'div'"
              :novalidate="form ? true : undefined"
              :class="[
                sizeClass,
                'relative w-full transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 dark:bg-neutral-900',
              ]"
              @submit.prevent="$emit('submit', $event)"
            >
              <div
                class="absolute right-4 top-5 pt-0.5 sm:right-5 sm:top-6 sm:pt-0"
              >
                <IDialogCloseButton @click="hide" />
              </div>

              <div
                class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 dark:bg-neutral-900"
              >
                <IDialogTitle :sub-title="subTitle" class="mr-5">
                  <slot name="title" :title="title">{{ title }}</slot>
                </IDialogTitle>

                <div class="mt-4">
                  <slot />
                </div>
              </div>

              <div
                v-show="!hideFooter"
                class="bg-neutral-50 px-4 py-3 sm:px-6 dark:bg-neutral-800"
              >
                <slot name="modal-footer" :cancel="hide">
                  <div class="flex justify-end space-x-2">
                    <slot
                      name="modal-cancel"
                      :cancel="hide"
                      :text="cancelText || $dialog._cancelText"
                    >
                      <IButton
                        :variant="cancelVariant"
                        :disabled="computedCancelDisabled"
                        :size="cancelSize"
                        :text="cancelText || $dialog._cancelText"
                        @click="hide"
                      />
                    </slot>

                    <slot name="modal-ok" :text="okText || $dialog._okText">
                      <IButton
                        :variant="okVariant"
                        :type="form ? 'submit' : 'button'"
                        :size="okSize"
                        :loading="okLoading"
                        :disabled="computedOkDisabled"
                        :text="okText || $dialog._okText"
                        @click="handleOkClick"
                      />
                    </slot>
                  </div>
                </slot>
              </div>

              <div
                :id="id + '-teleport'"
                ref="teleportRef"
                class="_child-dialogs"
              />
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, toRef, watch } from 'vue'
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'
import { useTimeoutFn } from '@vueuse/core'

import emitsDefinition from './emits'
import IDialogCloseButton from './IDialogCloseButton.vue'
import IDialogOverlay from './IDialogOverlay.vue'
import IDialogTitle from './IDialogTitle.vue'
import propsDefinition from './props'
import { useDialog, useDialogSize } from './useDialog'

const props = defineProps(propsDefinition)
const emit = defineEmits(emitsDefinition)
useDialog(show, hide, toRef(props, 'id'))

const sizeClass = useDialogSize(toRef(props, 'size'))
const isOpen = ref(false)
const hiding = ref(false)
const teleportRef = ref(null)

const computedOkDisabled = computed(() => {
  return props.busy || props.okDisabled
})

const computedCancelDisabled = computed(() => {
  return props.busy || props.cancelDisabled
})

watch(
  () => props.visible,
  newVal => (newVal ? show() : hide())
)

function maybePreventClosing() {
  if (teleportRef.value.children.length > 0) {
    return
  }

  if (!props.staticBackdrop) {
    hide()
  }
}

function show() {
  emit('show')
  isOpen.value = true
  emit('update:visible', true)
  nextTick(() => emit('shown'))
}

function hide() {
  // Sometimes when the modal is hidden via the close button,
  // the v-model:visible is updated later and causing the hide event to be fired twice
  if (hiding.value) {
    return
  }

  hiding.value = true
  isOpen.value = false

  emit('update:visible', false)

  nextTick(() => {
    useTimeoutFn(() => {
      emit('hidden')
      hiding.value = false
    }, 200)
  })
}

function handleOkClick(e) {
  emit('ok', e)
}

onMounted(() => {
  if (props.visible) {
    show()
  }
})

defineExpose({ hide, show })
</script>
