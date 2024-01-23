<template>
  <div :class="{ relative: show }">
    <slot />

    <div v-show="show" class="absolute inset-0 z-10">
      <div
        class="absolute inset-0 rounded-md bg-neutral-100 opacity-60 dark:bg-neutral-700"
      />

      <div
        v-if="show"
        class="absolute bottom-1/2 left-1/2 z-20 translate-y-1/2"
      >
        <slot name="overlay">
          <ISpinner class="size-5 text-primary-500 dark:text-neutral-300" />
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean,
})
</script>
