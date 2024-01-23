<template>
  <div class="mb-3">
    <slot name="label" :label="label" :label-for="labelFor">
      <IFormLabel
        v-if="!optional && label"
        :for="labelFor"
        :label="label"
        :required="required"
        class="mb-1"
      />

      <div v-else-if="label" class="flex">
        <IFormLabel class="mb-1 grow" :for="labelFor" :label="label" />

        <span
          v-t="'core::app.optional'"
          class="mb-px self-end text-xs text-neutral-600 dark:text-neutral-200"
        />
      </div>
    </slot>

    <slot />

    <IFormText v-if="description" class="mt-2" :text="description" />
  </div>
</template>

<script setup>
defineProps({
  label: String,
  labelFor: String,
  description: String,
  optional: Boolean,
  required: Boolean,
})
</script>
