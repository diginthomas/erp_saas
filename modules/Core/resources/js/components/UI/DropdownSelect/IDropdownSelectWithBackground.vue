<template>
  <TextBackground
    :color="backgroundColor"
    class="block rounded-md pb-px dark:!text-white"
  >
    <IDropdownSelect v-bind="$attrs" :disabled="disabled">
      <template #default="{ toggle, label }">
        <button
          type="button"
          :disabled="disabled"
          class="inline-flex w-full items-center justify-between px-2.5 text-sm focus:outline-none disabled:pointer-events-none"
          @click="toggle"
        >
          <span>{{ label }}</span>

          <Icon
            v-if="!disabled"
            icon="ChevronDown"
            class="ml-2 size-4 shrink-0"
          />
        </button>
      </template>
    </IDropdownSelect>
  </TextBackground>
</template>

<script setup>
defineOptions({ inheritAttrs: false })

defineProps({
  disabled: Boolean,
  wrapperClass: [String, Array, Object],
  backgroundColor: { type: String, required: true },
})
</script>
