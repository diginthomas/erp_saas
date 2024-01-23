<template>
  <WorkflowsForm
    v-if="editOrCreate"
    :workflow="workflow"
    :triggers="triggers"
    @hide="editOrCreate = false"
    @update:workflow="$emit('update:workflow', $event)"
    @delete-requested="$emit('deleteRequested', $event)"
  />

  <div :class="{ 'opacity-70': !workflow.is_active, hidden: editOrCreate }">
    <div class="flex items-center px-4 py-4 sm:px-6">
      <div class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between">
        <div class="truncate">
          <div class="flex">
            <ILink
              class="flex items-center truncate font-medium"
              @click="editOrCreate = true"
            >
              <span class="mr-1">
                {{ workflow.title }}
              </span>

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
          </div>

          <div class="mt-3 flex">
            <div class="flex items-center space-x-4 text-sm text-neutral-500">
              <p class="text-neutral-800 dark:text-neutral-300">
                {{
                  $t('core::workflow.total_executions', {
                    total: workflow.total_executions,
                  })
                }}
              </p>

              <p
                v-if="workflow.created_at"
                class="text-sm text-neutral-800 dark:text-white"
              >
                {{ $t('core::app.created_at') }}:
                {{ localizedDateTime(workflow.created_at) }}
              </p>
            </div>
          </div>
        </div>

        <div class="mt-4 shrink-0 sm:ml-5 sm:mt-0">
          <IFormToggle
            :label="$t('core::app.active')"
            :model-value="workflow.is_active"
            @change="handleWorkflowActiveChangeEvent"
          />
        </div>
      </div>

      <IMinimalDropdown class="ml-4 shrink-0">
        <IDropdownItem
          :text="$t('core::app.edit')"
          @click="editOrCreate = true"
        />

        <IDropdownItem :text="$t('core::app.delete')" @click="requestDelete" />
      </IMinimalDropdown>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

import { useDates } from '@/Core/composables/useDates'

import WorkflowsForm from './WorkflowsForm.vue'

const props = defineProps({
  triggers: { required: true, type: Array },
  workflow: { required: true, type: Object },
})

const emit = defineEmits(['update:workflow', 'deleteRequested'])

const { t } = useI18n()
const { localizedDateTime } = useDates()

const editOrCreate = ref(false)

function handleWorkflowActiveChangeEvent(value) {
  Innoclapps.request()
    .put(`/workflows/${props.workflow.id}`, {
      ...{ ...props.workflow, ...{ data: {} } },
      ...props.workflow.data,
      ...{ is_active: value },
    })
    .then(({ data }) => {
      emit('update:workflow', { ...data, key: props.workflow.key })
      Innoclapps.success(t('core::workflow.updated'))
    })
}

function requestDelete() {
  emit('deleteRequested', props.workflow)
}

if (!props.workflow.id) {
  editOrCreate.value = true
}
</script>
