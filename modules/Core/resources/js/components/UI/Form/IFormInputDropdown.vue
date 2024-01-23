<template>
  <IDropdown ref="dropdownRef" adaptive-width>
    <template #toggle="{ toggle }">
      <div
        :style="{ width }"
        :class="['relative', { 'pointer-events-none': disabled }]"
      >
        <!-- On mobile pointer events are disabled to not open the keyboard on touch,
        in this case, the user will be able to select only from the dropdown provided values -->
        <IFormInput
          :id="inputId"
          v-bind="$attrs"
          ref="inputRef"
          v-model="selectedItem"
          autocomplete="off"
          :class="[
            'pointer-events-none pr-8',
            { 'sm:pointer-events-auto': !disabled },
          ]"
          :disabled="disabled"
          :placeholder="placeholder"
          @click="toggle(), inputClicked()"
          @blur="inputBlur"
        />

        <IButtonIcon
          v-show="Boolean(selectedItem)"
          class="absolute right-3 top-2"
          icon="X"
          icon-class="size-4"
          @click="clearSelected"
        />
      </div>
    </template>

    <div
      :style="[
        {
          height: height,
          'overflow-y': maxHeight ? 'scroll' : null,
          'max-height': maxHeight || 'auto',
        },
      ]"
    >
      <IDropdownItem
        v-for="(item, index) in items"
        :key="index"
        :active="isSelected(item)"
        :text="item"
        :condensed="condensed"
        @click="itemPicked(item)"
      />
    </div>
  </IDropdown>
</template>

<script setup>
import { ref, shallowRef, watch } from 'vue'
import { useTimeoutFn } from '@vueuse/core'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps({
  inputId: { type: String, default: 'input-dropdown' },
  width: String,
  height: String,
  maxHeight: String,
  modelValue: String,
  placeholder: String,
  items: Array,
  disabled: Boolean,
  condensed: Boolean,
})

const emit = defineEmits(['update:modelValue', 'blur', 'cleared', 'shown'])

const isOpen = ref(false)
const selectedItem = shallowRef(props.modelValue)

const inputRef = ref(null)
const dropdownRef = ref(null)

watch(selectedItem, newVal => {
  if (newVal !== props.modelValue) {
    emit('update:modelValue', newVal)
  }
})

watch(
  () => props.modelValue,
  newVal => {
    if (newVal !== selectedItem.value) {
      selectedItem.value = newVal
    }
  }
)

function openIfNeeded() {
  if (!isOpen.value) {
    dropdownRef.value.show()
    isOpen.value = true
    emit('shown')
  }
}

// eslint-disable-next-line no-unused-vars
function inputClicked(e) {
  openIfNeeded()
}

// eslint-disable-next-line no-unused-vars
function inputBlur(e) {
  // Allow timeout as if user  clicks on the dropdown item to have
  // a selected value in case @blur event is checking the value
  useTimeoutFn(() => emit('blur'), 500)
}

function closePicker() {
  dropdownRef.value.hide()
  isOpen.value = false
}

function itemPicked(item) {
  closePicker()
  selectedItem.value = item
}

function isSelected(item) {
  return item === selectedItem.value
}

function clearSelected() {
  selectedItem.value = ''
  emit('cleared')
  inputRef.value.focus()
  openIfNeeded()
}
</script>
