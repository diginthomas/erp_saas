<template>
  <TransitionRoot as="template" :show="open">
    <Dialog
      as="div"
      static
      class="dialog __confirmation-dialog relative"
      :open="open"
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
        <IDialogOverlay />
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
              :class="[
                'relative w-full transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:p-6 dark:bg-neutral-800',
                sizeClass,
              ]"
            >
              <component
                :is="dialog.component"
                v-if="dialog.component"
                :close="close"
                :cancel="cancel"
                :dialog="dialog"
              />

              <template v-else>
                <div class="sm:flex sm:items-start">
                  <div
                    :class="[
                      'mx-auto flex size-12 shrink-0 items-center justify-center rounded-full sm:mx-0 sm:size-10',
                      dialog.iconWrapperColorClass
                        ? dialog.iconWrapperColorClass
                        : 'bg-danger-100',
                    ]"
                  >
                    <Icon
                      :icon="dialogIcon"
                      :class="[
                        'size-6',
                        dialog.iconColorClass
                          ? dialog.iconColorClass
                          : 'text-danger-600',
                      ]"
                    />
                  </div>

                  <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                    <IDialogTitle v-if="title" class="mt-2">
                      <!-- eslint-disable-next-line vue/no-v-html -->
                      <span v-if="dialog.html" v-html="title"></span>

                      <span v-else v-text="title"></span>
                    </IDialogTitle>

                    <div
                      v-if="dialog.message"
                      :class="Boolean(title) ? 'mt-2' : ''"
                    >
                      <p class="text-sm text-neutral-500 dark:text-neutral-300">
                        <!-- eslint-disable-next-line vue/no-v-html -->
                        <span v-if="dialog.html" v-html="dialog.message" />

                        <span v-else v-text="dialog.message" />
                      </p>
                    </div>
                  </div>
                </div>

                <div class="mt-5 sm:flex sm:flex-row-reverse">
                  <IButton
                    :variant="confirmVariant"
                    size="md"
                    class="w-full sm:ml-2 sm:w-auto"
                    :text="dialog.confirmText"
                    @click="confirm"
                  />

                  <IButton
                    :variant="cancelVariant"
                    size="md"
                    class="mt-2 w-full sm:mt-0 sm:w-auto"
                    :text="dialog.cancelText"
                    @click="cancel"
                  />
                </div>
              </template>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { computed, ref, toRef } from 'vue'
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'

import IDialogOverlay from './IDialogOverlay.vue'
import IDialogTitle from './IDialogTitle.vue'
import { useDialogSize } from './useDialog'

const props = defineProps({
  dialog: { required: true, type: Object },
})

const sizeClass = useDialogSize(toRef(props.dialog, 'size'))

const open = ref(true)

const title = computed(() =>
  props.dialog.title === false ? null : props.dialog.title
)

const dialogIcon = computed(() => props.dialog.icon || 'ExclamationTriangle')

const confirmVariant = computed(() => props.dialog.confirmVariant || 'danger')

const cancelVariant = computed(() => props.dialog.cancelVariant || 'white')

function close() {
  open.value = false
}

function confirm() {
  props.dialog.resolve()
  close()
}

function cancel() {
  props.dialog.reject()
  close()
}
</script>
