<template>
  <Transition
    leave-active-class="transition ease-in duration-1000"
    leave-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <small v-show="show" class="text-sm text-neutral-400 dark:text-neutral-200">
      <slot>
        {{ message }}
      </slot>
    </small>
  </Transition>
</template>

<script setup>
defineProps({ message: String, show: Boolean })
</script>
