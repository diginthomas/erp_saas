<template>
  <IDropdown>
    <template #toggle="{ toggle, attrs }">
      <IButtonIcon :icon="icon" v-bind="attrs" @click="toggle" />
    </template>

    <div class="py-1">
      <slot />
    </div>
  </IDropdown>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  horizontal: Boolean,
})

const icon = computed(() =>
  props.horizontal === true ? 'DotsHorizontal' : 'DotsVertical'
)
</script>
