<template>
  <MenuItem v-slot="{ active: focused }" :disabled="disabled">
    <a
      :href="ahref"
      :class="[
        'group block text-sm focus:outline-none',
        !condensed ? 'px-4 py-2' : 'px-2.5 py-1.5',
        { 'group flex items-center': icon },
        { 'justify-between': icon && prependIcon },
        focused || active
          ? 'bg-neutral-100/80 text-neutral-700 hover:text-neutral-900 dark:bg-neutral-700 dark:text-neutral-100 dark:hover:text-white'
          : 'text-neutral-700 dark:text-neutral-100 dark:hover:text-white',
        disabled ? 'pointer-events-none opacity-50' : null,
      ]"
      @click="handleClickEvent"
    >
      <Icon
        v-if="icon && !prependIcon"
        :icon="icon"
        :class="[
          'mr-2 size-5 shrink-0',
          !(focused || active)
            ? 'text-neutral-500 group-hover:text-neutral-600 dark:text-neutral-300 dark:group-hover:text-neutral-100'
            : '',
        ]"
      />

      <slot>{{ text }}</slot>

      <Icon
        v-if="icon && prependIcon"
        :icon="icon"
        :class="[
          'ml-2 mt-px size-5 shrink-0',
          !(focused || active)
            ? 'text-neutral-500 group-hover:text-neutral-600 dark:text-neutral-300 dark:group-hover:text-neutral-100'
            : '',
        ]"
      />

      <p
        v-if="description"
        class="text-neutral-500 dark:text-neutral-300"
        v-text="description"
      />
    </a>
  </MenuItem>
</template>

<script setup>
import { computed, inject } from 'vue'
import { useRouter } from 'vue-router'
import { MenuItem } from '@headlessui/vue'

const props = defineProps({
  active: Boolean,
  condensed: Boolean,
  disabled: Boolean,
  icon: String,
  prependIcon: Boolean,
  href: String,
  text: [String, Number],
  description: String,
  to: [Object, String],
})

const emit = defineEmits(['click'])

// IDropdown method
const hide = inject('hide')

const router = useRouter()

const ahref = computed(() => {
  if (props.href) {
    return props.href
  }

  if (props.to) {
    return router.resolve(props.to).href
  }

  return '#'
})

function handleClickEvent(e) {
  // Is it needed?
  if (props.disabled) {
    return
  }

  if ((!props.to && !props.href) || props.to) {
    e.preventDefault()
  }

  if (props.to) {
    router.push(props.to)
  }

  emit('click', e)

  if (!props.href) {
    hide()
  }
}
</script>
