<template>
  <button
    type="button"
    class="rounded-full border-0 p-0.5 text-neutral-500 hover:text-neutral-700 focus:outline-none focus:ring-2 focus:ring-neutral-300 dark:text-neutral-200 dark:hover:text-neutral-400 dark:focus:ring-neutral-500"
    @click="handleClickEvent"
  >
    <slot v-bind="{ classes, icon }">
      <Icon :icon="icon" :class="classes" />
    </slot>
  </button>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  to: [Object, String],
  icon: { required: true, type: String },
  iconClass: [String, Array, Object],
})

const emit = defineEmits(['click'])

const router = useRouter()

const classes = computed(() => [
  'pointer-events-none',
  props.iconClass || 'size-5',
])

function handleClickEvent(e) {
  if (props.to) {
    router.push(props.to)
  } else {
    emit('click', e)
  }
}
</script>
