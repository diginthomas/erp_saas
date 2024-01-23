<template>
  <div
    class="flex h-14 w-full flex-col justify-center rounded-lg p-2 md:h-32 md:p-4"
    :style="{ background: swatch.hex }"
  >
    <div
      class="flex items-center justify-between px-4 md:mt-auto md:block md:px-0"
      :style="{ color: getContrast(swatch.hex) }"
    >
      <div class="text-center text-sm font-medium">
        {{ swatch.stop }}
      </div>

      <div class="text-center text-xs uppercase opacity-90">
        <label :for="swatch.hex" class="cursor-pointer">
          {{ swatch.hex }}
        </label>

        <input
          :id="swatch.hex"
          type="color"
          :value="swatch.hex"
          class="h-0"
          @input="handleSwatchColorInput"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import debounce from 'lodash/debounce'

import { getContrast } from '@/Core/utils'

defineProps(['swatch', 'color'])

const emit = defineEmits(['update:hex'])

const handleSwatchColorInput = debounce(function (e) {
  emit('update:hex', e.target.value)
}, 300)
</script>

<style scoped>
input[type='color']::-moz-color-swatch {
  border: none;
}

input[type='color']::-webkit-color-swatch-wrapper {
  padding: 0;
}

input[type='color']::-webkit-color-swatch {
  border: none;
}
</style>
