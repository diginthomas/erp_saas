<template>
  <input
    :id="id"
    ref="inputRef"
    :value="modelValue"
    :name="name"
    :disabled="disabled"
    :autocomplete="autocomplete"
    :autofocus="autofocus"
    :type="type"
    :tabindex="tabindex"
    :required="required"
    :placeholder="placeholder"
    :pattern="pattern"
    :minlength="minlength"
    :maxlength="maxlength"
    :min="min"
    :max="max"
    :class="[
      'form-input',
      {
        'form-input-sm': size === 'sm',
        'form-input-md': size === 'md' || size === true,
        'form-input-lg': size === 'lg',
        'rounded-r-none': rounded === 'left',
        'rounded-l-none': rounded === 'right',
        'rounded-full': rounded === 'full',
        'rounded-none': rounded === false,
      },
    ]"
    @blur="blurHandler"
    @focus="focusHandler"
    @keyup="keyupHandler"
    @keydown="keydownHandler"
    @input="inputHandler"
  />
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  rounded: { default: true, type: [Boolean, String] },
  type: { type: String, default: 'text' },
  max: { type: [String, Number], default: undefined },
  min: { type: [String, Number], default: undefined },
  size: {
    type: [String, Boolean],
    default: 'md',
    validator(value) {
      if (typeof value === 'boolean') return true

      return ['sm', 'md', 'lg'].includes(value)
    },
  },
  modelValue: [String, Number],
  autocomplete: String,
  maxlength: [String, Number],
  minlength: [String, Number],
  pattern: String,
  placeholder: String,
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
  'keyup',
  'keydown',
  'change',
])

const inputRef = ref(null)
const valueWhenFocus = ref(null)

function inputHandler(e) {
  const value = e.target.value

  emit('input', value)
  emit('update:modelValue', value)
}

function blurHandler(e) {
  emit('blur', e)

  if (e.target.value !== valueWhenFocus.value) {
    emit('change', e.target.value)
  }
}

function focusHandler(e) {
  emit('focus', e)

  valueWhenFocus.value = e.target.value
}

function keyupHandler(e) {
  emit('keyup', e)
}

function keydownHandler(e) {
  emit('keydown', e)
}

function blur() {
  inputRef.value.blur()
}

function click() {
  inputRef.value.click()
}

function focus(options) {
  inputRef.value.focus(options)
}

function select() {
  inputRef.value.select()
}

function setRangeText(replacement) {
  inputRef.value.setRangeText(replacement)
}

defineExpose({
  setRangeText,
  select,
  focus,
  click,
  blur,
  inputRef,
})
</script>
