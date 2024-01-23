<template>
  <ICustomSelect
    v-if="(!manualLostReason && lostReasons.length > 0) || !allowCustomLocal"
    :options="lostReasons"
    :input-id="manualLostReason ? `${attribute}-hidden` : attribute"
    :input-name="attribute"
    label="name"
    @update:model-value="
      $emit('update:modelValue', $event ? $event.name : null)
    "
  />

  <div v-show="manualLostReason">
    <IFormTextarea
      :id="!manualLostReason ? `${attribute}-hidden` : attribute"
      :model-value="modelValue"
      :name="attribute"
      rows="2"
      @update:model-value="$emit('update:modelValue', $event)"
    />
  </div>

  <IFormText
    v-if="lostReasons.length > 0 && allowCustomLocal"
    class="mt-2 inline-flex items-center space-x-1"
  >
    <ILink
      :text="
        $t(
          `deals::deal.lost_reasons.${
            manualLostReason
              ? 'choose_lost_reason'
              : 'choose_lost_reason_or_enter'
          }`
        )
      "
      tabindex="-1"
      plain
      @click="manualLostReason = !manualLostReason"
    />

    <ILink @click="manualLostReason = !manualLostReason">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="size-5"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        stroke-width="2"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M13 7l5 5m0 0l-5 5m5-5H6"
        />
      </svg>
    </ILink>
  </IFormText>
</template>

<script setup>
import { computed, nextTick, onMounted, ref } from 'vue'

import { useApp } from '@/Core/composables/useApp'

import { useLostReasons } from '../composables/useLostReasons'

const props = defineProps({
  modelValue: String,
  allowCustom: { type: Boolean, default: undefined },
  attribute: { default: 'lost_reason', type: String },
})

defineEmits(['update:modelValue'])

const { setting } = useApp()
const manualLostReason = ref(false)

const allowCustomLocal = computed(() =>
  props.allowCustom === undefined
    ? setting('allow_lost_reason_enter')
    : props.allowCustom
)

const { lostReasonsByName: lostReasons } = useLostReasons()

if (lostReasons.value.length === 0 && allowCustomLocal.value) {
  manualLostReason.value = true
}

onMounted(() => {
  nextTick(() => {
    if (props.modelValue) {
      manualLostReason.value = true
    }
  })
})
</script>
