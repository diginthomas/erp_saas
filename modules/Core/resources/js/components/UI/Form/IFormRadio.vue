<template>
  <div class="flex items-start">
    <input
      :id="id"
      v-model="localModelValue"
      :name="name"
      type="radio"
      :value="value"
      :disabled="disabled"
      class="form-radio"
      @change="handleChangeEvent"
    />

    <IFormLabel :for="id" class="-mt-0.5 ml-2">
      <component
        :is="swatchColor ? TextBackground : 'span'"
        :color="swatchColor || undefined"
        :class="
          swatchColor
            ? 'rounded-md px-2.5 py-0.5 text-sm/5 font-normal dark:!text-white'
            : null
        "
      >
        <slot>{{ label }}</slot>
      </component>
    </IFormLabel>

    <IFormText v-if="description" :id="id + 'description'" class="mt-1">
      {{ description }}
    </IFormText>
  </div>
</template>

<script setup>
import { shallowRef, watch } from 'vue'

import TextBackground from '@/Core/components/TextBackground.vue'
import { randomString } from '@/Core/utils'

const props = defineProps({
  name: String,
  label: String,
  description: String,
  swatchColor: String,
  modelValue: [String, Boolean, Number],
  value: [String, Boolean, Number],
  disabled: Boolean,
  id: {
    type: [String, Number],
    default() {
      return randomString()
    },
  },
})

const emit = defineEmits(['update:modelValue', 'change'])

const localModelValue = shallowRef(null)

function handleChangeEvent(e) {
  let value = e.target.value

  // Allow providing null as a value
  if (value === 'on' && props.value === null) {
    value = null
  }

  if (value === 'false') {
    value = false
  } else if (value === 'true') {
    value = true
  }

  emit('update:modelValue', value)
  emit('change', value)
}

watch(
  () => props.modelValue,
  newVal => {
    localModelValue.value = newVal
  },
  { immediate: true }
)
</script>
