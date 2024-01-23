<template>
  <VDatePicker
    :locale="locale"
    :is24hr="!usesTwelveHourTime"
    :is-dark="isDarkMode"
    :timezone="userTimezone"
    :hide-time-header="true"
    title-position="left"
    color="primary"
    :popover="{
      visibility: 'focus',
      positionFixed: true,
    }"
  >
    <template v-for="(_, name) in $slots" #[name]="slotData">
      <slot :name="name" v-bind="slotData" />
    </template>
  </VDatePicker>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'

import { useApp } from '@/Core/composables/useApp'
import { getLocale } from '@/Core/utils'

import AsyncComponentLoader from '../AsyncComponentLoader.vue'

const props = defineProps({
  isDate: { type: Boolean, required: true },
  isDateTime: { type: Boolean, required: true },
})

const VDatePicker = defineAsyncComponent({
  loader: () => import('v-calendar').then(module => module.DatePicker),
  loadingComponent: AsyncComponentLoader,
})

import { useDates } from '@/Core/composables/useDates'

import 'v-calendar/dist/style.css'

const { usesTwelveHourTime, userTimezone } = useDates()
const { isDarkMode } = useApp()

const masks = computed(() => {
  let masks = {}

  if (props.isDate) {
    // masks.input = dateFormatForMoment.value
    masks.modelValue = 'YYYY-MM-DD'
  } else if (props.isDateTime) {
    masks.modelValue = 'YYYY-MM-DD HH:mm:ss'
  } else {
    // TODO time, not yet used
  }

  return masks
})

const locale = computed(() => {
  return {
    masks: masks.value,
    id: getLocale().replace('_', '-'),
  }
})
</script>
