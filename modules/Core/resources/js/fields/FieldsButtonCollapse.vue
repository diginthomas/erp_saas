<template>
  <button
    type="button"
    class="inline-flex items-center rounded-md px-1.5 py-1.5 text-sm text-neutral-800 hover:bg-neutral-100 hover:text-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-300 disabled:pointer-events-none disabled:opacity-60 dark:text-neutral-200 dark:hover:bg-neutral-700 dark:hover:text-neutral-100 dark:focus:ring-neutral-500"
    @click="$emit('update:collapsed', !collapsed)"
  >
    <Icon :icon="collapsed ? 'ChevronDown' : 'ChevronUp'" class="size-4" />

    <span
      v-t="{
        path: collapsed
          ? 'core::fields.more_fields'
          : 'core::fields.hide_fields',
        args: { count: total },
      }"
      class="ml-1 mr-1 text-xs"
    />
  </button>
</template>

<script setup>
defineProps({
  collapsed: { required: true, type: Boolean },
  total: { type: Number, required: true },
})

defineEmits(['update:collapsed'])
</script>
