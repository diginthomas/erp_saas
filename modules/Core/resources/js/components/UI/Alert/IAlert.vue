<template>
  <div
    v-show="computedShow"
    :class="['p-4', colors[variant].bg, rounded ? 'rounded-md' : '']"
  >
    <div :class="['flex', wrapperClass]">
      <div class="shrink-0">
        <Icon
          :icon="icon || iconMap[variant]"
          :class="['size-5', colors[variant].icon]"
        />
      </div>

      <div class="ml-3">
        <h3
          v-show="heading"
          :class="['text-sm font-medium', colors[variant].heading]"
          v-text="heading"
        />

        <div
          class="text-sm"
          :class="[colors[variant].text, heading ? 'mt-2' : '']"
        >
          <slot :variant="variant">{{ text }}</slot>
        </div>
      </div>

      <span v-if="dismissible" class="ml-auto">
        <IButtonIcon icon="X" class="ml-3" @click="dismiss" />
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  heading: String,
  text: [String, Number],
  show: { type: Boolean, default: true },
  dismissible: Boolean,
  rounded: { type: Boolean, default: true },
  icon: String,
  wrapperClass: [Array, Object, String],
  variant: {
    default: 'info',
    type: String,
    validator(value) {
      return ['success', 'info', 'warning', 'danger'].includes(value)
    },
  },
})

const emit = defineEmits(['dismissed'])

const colors = {
  warning: {
    bg: 'bg-warning-50',
    text: 'text-warning-700',
    heading: 'text-warning-800',
    icon: 'text-warning-400',
  },
  danger: {
    bg: 'bg-danger-50',
    text: 'text-danger-700',
    heading: 'text-danger-800',
    icon: 'text-danger-400',
  },
  success: {
    bg: 'bg-success-50',
    text: 'text-success-700',
    heading: 'text-success-800',
    icon: 'text-success-400',
  },
  info: {
    bg: 'bg-info-50',
    text: 'text-info-700',
    heading: 'text-info-800',
    icon: 'text-info-400',
  },
}

const iconMap = {
  warning: 'ExclamationTriangle',
  danger: 'XCircle',
  success: 'CheckCircle',
  info: 'InformationCircle',
}

const dismissed = ref(false)

const computedShow = computed(() => (dismissed.value ? false : props.show))

function dismiss() {
  dismissed.value = true
  emit('dismissed')
}
</script>
