<template>
  <div class="flex w-full items-center overflow-x-auto">
    <ILink
      v-show="model && !isDisabled"
      :text="$t('core::app.all')"
      class="mr-3 border-r-2 border-neutral-200 pr-3 dark:border-neutral-600"
      @click="model = null"
    />

    <div
      v-for="status in statuses"
      :key="status.name"
      :class="[
        'mr-2 rounded-md',
        status.name === model ? 'bg-neutral-800/10' : '',
      ]"
    >
      <TextBackground
        v-i-tooltip.bottom.light="
          statusRuleIsApplied
            ? $t('documents::document.filters.status_disabled')
            : ''
        "
        :color="status.color"
        :bg-opacity="0.09"
        :class="isDisabled ? 'opacity-70' : 'cursor-pointer'"
        class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm/5 dark:!text-white"
        @click="!isDisabled ? (model = status.name) : undefined"
      >
        <Icon :icon="status.icon" class="mr-1.5 size-4" />
        {{ $t('documents::document.status.' + status.name) }}
      </TextBackground>
    </div>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'

import { useApp } from '@/Core/composables/useApp'
import { useQueryBuilder } from '@/Core/composables/useQueryBuilder'

const model = defineModel()

const { scriptConfig } = useApp()

const { hasAnyBuilderRules, rulesAreValid, findRule } =
  useQueryBuilder('documents')

const statuses = scriptConfig('documents.statuses')

const statusRuleIsApplied = computed(() => Boolean(findRule('status')))

const isDisabled = computed(
  () => statusRuleIsApplied.value || !rulesAreValid.value
)

// Remove selected type when the builder has rules and they are valid
// to prevent errors in the filters
watch(hasAnyBuilderRules, newVal => {
  if (newVal && rulesAreValid.value) {
    model.value = undefined
  }
})

// The same for when rules become valid, when valid and has builder rules remove selected type
watch(rulesAreValid, newVal => {
  if (hasAnyBuilderRules.value && newVal) {
    model.value = undefined
  }
})
</script>
