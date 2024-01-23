<template>
  <div
    :class="[
      bordered ? 'border-neutral-200 dark:border-neutral-700' : '',
      { 'border-b': bordered === 'bottom' || bordered === true },
      { 'border-y': bordered === 'vertical' },
      { 'border-t': bordered === 'top' },
    ]"
  >
    <div
      :class="[
        'flex items-center',
        listWrapperClass,
        { 'justify-center': centered },
      ]"
    >
      <a
        href="#"
        :class="{ 'pointer-events-none opacity-50': scrolledToFirstTab }"
        class="block px-1 text-neutral-500 sm:hidden dark:text-neutral-200"
        @click.prevent="scrollLeft"
      >
        <Icon icon="ChevronLeft" class="size-6" />
      </a>

      <TabList
        ref="listRef"
        :class="[
          'overlow-y-hidden -mb-px flex grow snap-x snap-mandatory overflow-x-auto scrollbar-thin scrollbar-track-neutral-200 scrollbar-thumb-neutral-300 sm:space-x-4 sm:px-4',
          { 'justify-around': fill, 'sm:grow-0': !fill },
        ]"
      >
        <slot />
      </TabList>

      <a
        href="#"
        class="block px-1 text-neutral-500 sm:hidden dark:text-neutral-200"
        :class="{ 'pointer-events-none opacity-50': scrolledToLastTab }"
        @click.prevent="scrollRight"
      >
        <Icon icon="ChevronRight" class="size-6" />
      </a>
    </div>
  </div>
</template>

<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { TabList } from '@headlessui/vue'

defineProps({
  responsive: { type: Boolean, default: true },
  bordered: { type: [String, Boolean], default: 'bottom' },
  listWrapperClass: [String, Array, Object],
  fill: Boolean,
  centered: Boolean,
})

let observer = null

const scrolledToLastTab = ref(false)
const scrolledToFirstTab = ref(true)

const listRef = ref(null)

function firstTabElm() {
  return listRef.value.$el.querySelector('button:first-child')
}

function lastTabElm() {
  return listRef.value.$el.querySelector('button:last-child')
}

function scrollLeft() {
  listRef.value.$el.scrollLeft -= firstTabElm().offsetWidth
}

function scrollRight() {
  listRef.value.$el.scrollLeft += lastTabElm().offsetWidth
}

function unobserve() {
  observer.unobserve(firstTabElm())
  observer.unobserve(lastTabElm())
}

function observerCallback(entries) {
  entries.forEach(entry => {
    if (entry.target == lastTabElm()) {
      scrolledToLastTab.value = entry.isIntersecting
    } else if (entry.target == firstTabElm()) {
      scrolledToFirstTab.value = entry.isIntersecting
      scrolledToLastTab.value = false
    }
  })
}

function createObserver() {
  observer = new IntersectionObserver(observerCallback, {
    root: listRef.value.$el,
    threshold: 1.0,
  })
}

function observe() {
  createObserver()

  nextTick(() => {
    observer.observe(firstTabElm())
    observer.observe(lastTabElm())
  })
}

onMounted(() => {
  observe()
})

onBeforeUnmount(() => {
  unobserve()
  observer = null
})
</script>
