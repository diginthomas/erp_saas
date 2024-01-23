<template>
  <div
    class="flex items-center space-x-1 text-sm text-neutral-600 dark:text-white"
  >
    <span v-t="'core::table.length_menu.show'"></span>

    <IDropdownSelect
      :model-value="perPage"
      :items="perPageOptions"
      :disabled="disabled"
      @update:model-value="$emit('update:perPage', $event)"
      @change="$emit('change', $event)"
    />

    <span v-t="'core::table.length_menu.entries'"></span>
  </div>
</template>

<script setup>
defineProps({
  disabled: Boolean,
  perPage: Number,
  perPageOptions: Array,
})
defineEmits(['change', 'update:perPage'])
</script>
