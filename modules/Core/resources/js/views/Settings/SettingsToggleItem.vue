<template>
  <li class="px-4 py-4 sm:px-6">
    <div
      class="space-x-0 space-y-3 md:flex md:items-center md:justify-between md:space-y-0 lg:space-x-3"
    >
      <div>
        <h5
          class="font-medium leading-relaxed text-neutral-700 dark:text-neutral-100"
          v-text="heading"
        />

        <p
          class="break-words text-sm text-neutral-600 dark:text-neutral-300"
          v-text="description"
        />
      </div>

      <div>
        <IFormToggle
          :model-value="modelValue"
          :value="checkedValue"
          :unchecked-value="uncheckedValue"
          @change="handleChange"
        />
      </div>
    </div>
  </li>
</template>

<script setup>
defineProps({
  heading: { type: String, required: true },
  description: String,
  modelValue: { required: true },
  checkedValue: { type: [Number, String, Boolean], default: true },
  uncheckedValue: { type: [Number, String, Boolean], default: false },
})

const emit = defineEmits(['update:modelValue', 'change'])

function handleChange(value) {
  emit('update:modelValue', value)
  emit('change', value)
}
</script>
