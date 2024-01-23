<template>
  <div
    class="fixed left-auto right-1 z-50 w-full max-w-xs rounded-md border border-neutral-600 bg-neutral-800 px-6 py-5 shadow-lg shadow-primary-400/40 md:left-80 md:right-auto md:max-w-md dark:shadow-primary-900/40"
    :class="totalResults <= 5 ? 'bottom-36' : 'bottom-[3.3rem]'"
  >
    <div class="flex flex-col md:flex-row md:items-center">
      <div class="mr-8 flex shrink-0 items-center">
        <IFormCheckbox
          :id="`${tableId}ActionToggleAll`"
          :checked="ids.length > 0"
          @change="$emit('unselect')"
        />

        <label
          class="ml-1 text-sm text-neutral-100"
          :for="`${tableId}ActionToggleAll`"
          v-text="
            $t('core::actions.records_count', {
              count: ids.length,
            })
          "
        />
      </div>

      <div class="md:w-68 mt-3 w-full md:mt-0">
        <ActionSelector
          type="select"
          size="sm"
          view="index"
          :ids="ids"
          :action-request-params="requestParams"
          :actions="actions || []"
          :resource-name="resourceName"
          @run="$emit('run', $event)"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import ActionSelector from '@/Core/components/Actions/ActionSelector.vue'

defineProps([
  'ids',
  'actions',
  'requestParams',
  'resourceName',
  'tableId',
  'totalResults',
])

defineEmits(['run', 'unselect'])
</script>
