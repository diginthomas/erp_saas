<template>
  <span class="relative z-0 inline-flex rounded-md shadow-sm">
    <IButton
      :variant="variant"
      :size="size"
      :type="type"
      :icon="icon"
      :icon-class="iconClass"
      :to="to"
      rounded="left"
      :disabled="disabled"
      :loading="loading"
      class="-mr-px"
      @click="$emit('click', $event)"
    >
      <slot name="button-content">
        {{ text }}
      </slot>
    </IButton>

    <IDropdown
      :placement="placement"
      no-caret
      :variant="variant"
      :disabled="disabled"
      :size="size"
      rounded="right"
    >
      <template #toggle-content>
        <Icon icon="ChevronDown" class="size-4" />
      </template>

      <slot />
    </IDropdown>
  </span>
</template>

<script setup>
defineProps({
  text: [String, Number],
  to: [Object, String],
  placement: { type: String, default: 'bottom-end' },
  size: { type: String, default: 'sm' },
  disabled: Boolean,
  loading: Boolean,
  variant: { type: String, default: 'primary' },
  icon: { type: String },
  type: { type: String, default: 'button' },
  iconClass: [String, Array, Object],
})

defineEmits(['click'])
</script>
