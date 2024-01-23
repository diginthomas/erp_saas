<template>
  <div class="flex items-start space-x-2">
    <div class="flex h-5 items-center">
      <input
        :id="id"
        :name="name"
        type="checkbox"
        :disabled="disabled"
        :checked="isChecked"
        :indeterminate="indeterminate"
        :value="value"
        class="form-check"
        @change="updateModelValue"
      />
    </div>

    <div>
      <IFormLabel :for="id">
        <component
          :is="swatchColor ? TextBackground : 'span'"
          :color="swatchColor || undefined"
          :class="
            swatchColor
              ? 'rounded-md px-2.5 py-0.5 text-sm/5 font-normal dark:!text-white'
              : null
          "
        >
          <slot>
            {{ label }}
          </slot>
        </component>
      </IFormLabel>

      <IFormText
        v-if="description"
        :id="id + 'description'"
        class="mt-1"
        :text="description"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

import TextBackground from '@/Core/components/TextBackground.vue'
import { randomString } from '@/Core/utils'

const props = defineProps({
  name: String,
  label: String,
  description: String,
  swatchColor: String,
  indeterminate: Boolean,
  checked: [Array, String, Boolean, Number],
  value: { type: [Array, String, Boolean, Number], default: true },
  uncheckedValue: { type: [Array, String, Boolean, Number], default: false },
  disabled: Boolean,
  id: {
    type: String,
    default() {
      return randomString()
    },
  },
})

const emit = defineEmits(['update:checked', 'change'])

const isChecked = computed(() => {
  if (Array.isArray(props.checked)) {
    return (
      Boolean(
        props.checked.filter(value => String(value) === String(props.value))[0]
      ) || false
    )
  }

  return props.checked == props.value
})

function updateModelValue(e) {
  const modelValue = props.checked
  const isInputChecked = e.target.checked
  let value

  if (Array.isArray(modelValue)) {
    value = [...modelValue]

    if (isInputChecked) {
      value.push(props.value)
    } else {
      value.splice(
        value.findIndex(value => String(value) === String(props.value)),
        1
      )
    }
  } else {
    value = isInputChecked === true ? props.value : props.uncheckedValue
  }

  emit('update:checked', value)
  emit('change', value)
}
</script>
