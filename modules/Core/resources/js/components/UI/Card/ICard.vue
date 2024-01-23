<template>
  <IOverlay :show="overlay">
    <component
      :is="tag"
      :class="[
        'card bg-white dark:bg-neutral-900',
        {
          'ring-1 ring-neutral-200 dark:ring-neutral-500 dark:ring-opacity-50':
            ring,
          'rounded-lg': rounded,
          shadow: shadow,
          'overflow-hidden': overflowHidden,
        },
      ]"
      v-bind="$attrs"
    >
      <!-- Header -->
      <div
        v-if="header || $slots.header || $slots.actions"
        :class="[
          'flex flex-wrap items-center justify-between border-b border-neutral-200 sm:flex-nowrap dark:border-neutral-700',
          condensed ? 'px-6 py-3' : 'px-4 py-4 sm:px-6',
          headerClass,
        ]"
      >
        <div class="grow">
          <slot name="header">
            <ICardHeading>{{ header }}</ICardHeading>
          </slot>

          <p
            v-if="description"
            class="mt-1 max-w-2xl text-sm text-neutral-500 dark:text-neutral-200"
            v-text="description"
          />
        </div>

        <div
          v-if="$slots.actions"
          class="shrink-0 sm:ml-4"
          :class="actionsClass"
        >
          <slot name="actions"></slot>
        </div>
      </div>

      <!-- Body -->
      <ICardBody v-if="!noBody" :condensed="condensed">
        <slot />
      </ICardBody>

      <slot v-else></slot>

      <!-- Footer -->
      <ICardFooter
        v-if="$slots.footer"
        :condensed="condensed"
        :class="footerClass"
      >
        <slot name="footer"></slot>
      </ICardFooter>
    </component>
  </IOverlay>
</template>

<script setup>
defineOptions({
  inheritAttrs: false,
})

defineProps({
  tag: { type: [String, Object], default: 'div' },
  header: String,
  headerClass: [String, Array, Object],
  actionsClass: [String, Array, Object],
  footerClass: [String, Array, Object],
  description: String,
  condensed: Boolean,
  overlay: Boolean,
  ring: { default: true, type: Boolean },
  rounded: { default: true, type: Boolean },
  shadow: { default: true, type: Boolean },
  overflowHidden: { default: true, type: Boolean },
  noBody: Boolean,
})
</script>
