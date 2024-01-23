<template>
  <ILink class="group flex items-center" plain @click="$emit('click')">
    <span class="flex items-center px-4 py-2 text-sm font-medium">
      <span
        class="flex size-7 shrink-0 items-center justify-center rounded-full border-2 border-neutral-300 group-hover:border-neutral-400 dark:border-neutral-500 dark:group-hover:border-neutral-100"
      >
        <ISpinner
          v-if="requestInProgress"
          class="size-6 text-neutral-500 dark:text-neutral-200"
        />

        <Icon
          v-else
          icon="Plus"
          class="size-5 text-neutral-500 dark:text-neutral-100"
        />
      </span>

      <span
        class="ml-4 text-sm font-medium text-neutral-500 group-hover:text-neutral-900 dark:text-neutral-400 dark:group-hover:text-neutral-100"
      >
        <slot />
      </span>
    </span>
  </ILink>
</template>

<script setup>
defineProps({ requestInProgress: Boolean })

defineEmits(['click'])
</script>
