<template>
  <RadioGroup
    :model-value="modelValue"
    :disabled="disabled"
    @update:model-value="pickIcon"
  >
    <div class="flex flex-wrap gap-x-[0.42rem] gap-y-2" v-bind="$attrs">
      <RadioGroupOption
        v-for="icon in icons"
        :key="icon[valueField]"
        v-slot="{ checked }"
        :name="name"
        as="template"
        :value="icon[valueField]"
      >
        <div
          v-i-tooltip="icon.tooltip"
          :class="[
            'flex cursor-pointer items-center justify-center rounded-md text-neutral-700 ring-1 ring-neutral-300 dark:border-neutral-500 dark:text-neutral-200 dark:ring-neutral-500',
            size === 'sm' ? 'px-2 py-1.5' : 'px-2.5 py-2',
            {
              'pointer-events-none opacity-70': disabled,
            },
            checked
              ? 'bg-neutral-200 hover:bg-neutral-200 dark:bg-neutral-800'
              : 'bg-white hover:bg-neutral-50 hover:text-neutral-900 dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:hover:text-neutral-100',
          ]"
        >
          <RadioGroupLabel as="span">
            <Icon :icon="icon.icon" class="size-5" />
          </RadioGroupLabel>
        </div>
      </RadioGroupOption>
    </div>
  </RadioGroup>
</template>

<script setup>
import { ref, watch } from 'vue'
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  modelValue: [String, Number],
  label: String,
  disabled: Boolean,
  valueField: { type: String, default: 'icon' },
  size: { type: String, default: 'sm' },
  name: String,
  icons: {
    type: Array,
    default: function () {
      return [
        'Mail',
        'PencilAlt',
        'OfficeBuilding',
        'Phone',
        'Calendar',
        'Collection',
        'Bell',
        'AtSymbol',
        'Briefcase',
        'Chat',
        'CheckCircle',
        'BookOpen',
        'Camera',
        'Truck',
        'Folder',
        'DeviceMobile',
        'Users',
        'CreditCard',
        'Clock',
        'ShieldExclamation',
        'WrenchScrewdriver',
        'ShoppingBag',
        'Film',
        'Gift',
        'Inbox',
        'Key',
        'LockClosed',
        'PaintBrush',
        'Bookmark',
        'AcademicCap',
        'ArchiveBox',
        'BugAnt',
        'CodeBracket',
      ].map(icon => ({ icon, tooltip: null }))
    },
  },
})

const emit = defineEmits(['update:modelValue', 'change'])

const selected = ref(props.modelValue)

function pickIcon(icon) {
  selected.value = icon
  updateModelValue(icon)
}

function updateModelValue(value) {
  emit('update:modelValue', value)
  emit('change', value)
}

watch(() => props.modelValue, pickIcon)
</script>
