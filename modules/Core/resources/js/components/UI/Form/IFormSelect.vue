<template>
  <select
    :id="id"
    ref="selectRef"
    :name="name"
    :autofocus="autofocus"
    :placeholder="placeholder"
    :tabindex="tabindex"
    :disabled="disabled"
    :required="required"
    :multiple="multiple"
    :class="[
      'form-select',
      {
        'form-select-sm': size === 'sm',
        'form-select-md': size === 'md' || size === true,
        'form-select-lg': size === 'lg',
        'rounded-r-none': rounded === 'left',
        'rounded-l-none': rounded === 'right',
        'rounded-full': rounded === 'full',
        'rounded-none': rounded === false,
      },
    ]"
    :value="modelValue"
    @blur="blurHandler"
    @focus="focusHandler"
    @input="inputHandler"
    @change="changeHandler"
  >
    <slot />
  </select>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  rounded: { default: true, type: [Boolean, String] },
  modelValue: {},
  placeholder: String,
  multiple: Boolean,
  size: {
    type: [String, Boolean],
    default: 'md',
    validator(value) {
      if (typeof value === 'boolean') return true

      return ['sm', 'md', 'lg'].includes(value)
    },
  },
  id: String,
  name: String,
  disabled: Boolean,
  autofocus: Boolean,
  required: Boolean,
  tabindex: [String, Number],
})

const emit = defineEmits([
  'update:modelValue',
  'focus',
  'blur',
  'input',
  'change',
])

const selectRef = ref(null)

function changeHandler(e) {
  emit('update:modelValue', e.target.value)
  emit('change', e.target.value)
}

function inputHandler(e) {
  emit('input', e.target.value)
}

function blurHandler(e) {
  emit('blur', e)
}

function focusHandler(e) {
  emit('focus', e)
}

function blur() {
  selectRef.value.blur()
}

function focus(options) {
  selectRef.value.focus(options)
}

defineExpose({ blur, focus })
</script>
