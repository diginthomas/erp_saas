<template>
  <a
    v-if="isExternalLink"
    v-bind="$attrs"
    :class="[linkClasses, 'inline-flex items-center']"
    :href="href"
    target="_blank"
    rel="noopener noreferrer"
    @click="$emit('click', $event)"
  >
    <slot>
      <span>{{ text }}</span>

      <Icon icon="ExternalLink" class="ml-2 size-4 shrink-0" />
    </slot>
  </a>

  <RouterLink
    v-else-if="isRouterLink"
    v-slot="{ isActive, href: routerLinkHref, navigate }"
    v-bind="$props"
    custom
  >
    <a
      v-bind="$attrs"
      :href="determineRouterLinkHref(routerLinkHref)"
      :class="[linkClasses, isActive ? activeClass : inactiveClass]"
      @click="navigate($event), $emit('click', $event)"
    >
      <slot>{{ text }}</slot>
    </a>
  </RouterLink>

  <a
    v-else
    v-bind="$attrs"
    :href="href || '#'"
    :class="linkClasses"
    @click.prevent="$emit('click', $event)"
  >
    <slot>{{ text }}</slot>
  </a>
</template>

<script setup>
import { computed } from 'vue'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps({
  text: String,
  xs: Boolean,
  plain: Boolean,
  href: String,

  variant: {
    type: String,
    default: 'primary',
    validator(value) {
      return ['primary', 'neutral', 'warning', 'danger', 'info'].includes(value)
    },
  },

  // RouterLink
  to: [String, Object],
  replace: Boolean,
  activeClass: String,
  exactActiveClass: String,
  inactiveClass: String,
})

defineEmits(['click'])

const isExternalLink = computed(
  () =>
    props.href &&
    typeof props.href === 'string' &&
    props.href.startsWith('http')
)

const isRouterLink = computed(() => Boolean(props.to))

const linkClasses = computed(() => {
  const common = { 'text-xs': props.xs }

  if (props.plain) {
    return common
  }

  return {
    ...common,
    link: true,
    'link-primary': props.variant === 'primary',
    'link-neutral': props.variant === 'neutral',
    'link-danger': props.variant === 'danger',
    'link-warning': props.variant === 'warning',
    'link-info': props.variant === 'info',
  }
})

/**
 * "RouterLink" allows providing custom "href" attribute, different from the one the router generated,
 *  we will allow this too for convenience.
 *
 * @param {String} currentHref
 */
function determineRouterLinkHref(currentHref) {
  return props.href && props.href !== currentHref ? props.href : currentHref
}
</script>
