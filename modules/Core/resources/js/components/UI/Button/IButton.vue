<template>
  <component
    :is="tag"
    ref="buttonRef"
    :type="type"
    :class="[
      'btn',
      {
        active: active === true,
        'btn-primary': variant === 'primary',
        'btn-secondary': variant === 'secondary',
        'btn-danger': variant === 'danger',
        'btn-white': variant === 'white',
        'btn-success': variant === 'success',
        'btn-xs': size === 'xs',
        'btn-sm': size === 'sm',
        'btn-md': size === 'md' || size === true,
        'btn-lg': size === 'lg',
        'rounded-r-none': rounded === 'left',
        'rounded-l-none': rounded === 'right',
        'rounded-full': rounded === 'full',
        'rounded-none': rounded === false,
        'w-full': block,
        'btn-icon': isIconOnly,
      },
      'gap-x-1.5',
    ]"
    :disabled="disabled"
    :tabindex="disabled ? '-1' : undefined"
    @click="handleClickEvent"
  >
    <Icon
      v-if="icon"
      v-show="!loading"
      :icon="icon"
      :class="[
        'pointer-events-none shrink-0',
        iconClass || (size === 'xs' ? 'size-4' : 'size-5'),
      ]"
    />

    <ISpinner
      v-if="loading"
      :class="['shrink-0', size === 'xs' ? 'size-4' : 'size-5']"
    />

    <slot>
      {{ text }}
    </slot>
  </component>
</template>

<script setup>
import { onActivated, onMounted, onUpdated, ref } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  text: [String, Number],
  icon: String,
  iconClass: [String, Array, Object],
  active: Boolean,
  to: [Object, String],
  tag: { default: 'button', type: [String, Object] },
  type: { type: String, default: 'button' },
  disabled: Boolean,
  loading: Boolean,
  rounded: { default: true, type: [Boolean, String] },
  block: Boolean,
  variant: {
    type: String,
    default: 'primary',
    validator(value) {
      return ['primary', 'secondary', 'danger', 'white', 'success'].includes(
        value
      )
    },
  },
  size: {
    type: [String, Boolean],
    default: 'sm',
    validator(value) {
      if (typeof value === 'boolean') return true

      return ['xs', 'sm', 'md', 'lg'].includes(value)
    },
  },
})

const emit = defineEmits(['click'])

const router = useRouter()

const buttonRef = ref(null)
const isIconOnly = ref(false)

onUpdated(checkIfOnlyIcon)
onMounted(checkIfOnlyIcon)
onActivated(checkIfOnlyIcon)

function handleClickEvent(e) {
  if (props.to) {
    router.push(props.to)
  } else {
    emit('click', e)
  }
}

function isIcon(el, className = 'icon') {
  return (
    typeof el.tagName === 'string' &&
    el.tagName.toLowerCase() === 'svg' &&
    el.classList.contains(className)
  )
}

function checkIfOnlyIcon() {
  let button = buttonRef.value

  if ((!button) instanceof Element) {
    return
  }

  // Filter out comment and whitespace-only text nodes
  const nonEmptyNodes = Array.from(button.childNodes).filter(
    node =>
      node.nodeType !== Node.COMMENT_NODE &&
      !(node.nodeType === Node.TEXT_NODE && node.textContent.trim() === '')
  )

  if (nonEmptyNodes.length === 2) {
    // Spinner and icon hidden
    isIconOnly.value =
      isIcon(nonEmptyNodes[0]) && isIcon(nonEmptyNodes[1], 'spinner')
  } else if (nonEmptyNodes.length > 1) {
    isIconOnly.value = false
  } else {
    isIconOnly.value = isIcon(nonEmptyNodes[0])
  }
}
</script>
