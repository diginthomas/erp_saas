<template>
  <ICard
    no-body
    :header="$t('core::workflow.workflows')"
    :overlay="!componentReady"
  >
    <template #actions>
      <IButton
        v-show="hasDefinedWorkflows"
        icon="plus"
        :disabled="workflowBeingCreated"
        :text="$t('core::workflow.create')"
        @click="add"
      />
    </template>

    <div v-if="componentReady">
      <TransitionGroup
        v-if="hasDefinedWorkflows"
        name="flip-list"
        tag="ul"
        class="divide-y divide-neutral-200 dark:divide-neutral-700"
      >
        <li v-for="workflow in orderedWorkflows" :key="workflow.key">
          <WorkflowsListItem
            :workflow="workflow"
            :triggers="availableTriggers"
            @update:workflow="setWorkflowInList"
            @delete-requested="destroy"
          />
        </li>
      </TransitionGroup>

      <div v-else class="p-7">
        <button
          type="button"
          class="relative flex w-full flex-col items-center rounded-lg border-2 border-neutral-300 bg-neutral-50/60 p-6 hover:border-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 sm:p-12 dark:border-neutral-500 dark:bg-neutral-800 dark:hover:border-neutral-400"
          @click="add"
        >
          <Icon
            icon="RocketLaunch"
            class="mx-auto size-12 text-primary-500 dark:text-primary-400"
          />

          <span
            v-t="'core::workflow.create'"
            class="mt-2 block font-medium text-neutral-900 dark:text-neutral-100"
          />

          <p
            v-t="'core::workflow.info'"
            class="mt-2 block max-w-2xl text-sm text-neutral-600 dark:text-neutral-300"
          />
        </button>
      </div>
    </div>
  </ICard>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import findIndex from 'lodash/findIndex'
import orderBy from 'lodash/orderBy'

import { randomString } from '@/Core/utils'

import WorkflowsListItem from './WorkflowsListItem.vue'

const { t } = useI18n()

const availableTriggers = ref([])
const workflows = ref([])
const componentReady = ref(false)

const orderedWorkflows = computed(() =>
  orderBy(
    workflows.value,
    [
      item => (item.id == null ? 0 : 1), // null or undefined IDs first
      item => (item.is_active ? 1 : 0), // active items next
      'title', // then sort by title
    ],
    [
      'asc', // We want null IDs at the beginning, so we sort ascending (since we're using 0 for null IDs)
      'desc',
      'asc',
    ]
  )
)

const workflowBeingCreated = computed(
  () => workflows.value.filter(workflow => !workflow.id).length > 0
)

const hasDefinedWorkflows = computed(() => workflows.value.length > 0)

function add() {
  workflows.value.unshift({
    key: randomString(),
    title: '',
    description: null,
    is_active: false,
    trigger_type: null,
    action_type: null,
  })
}

function removeWorkflowFromList(key) {
  workflows.value.splice(findIndex(workflows.value, ['key', key]), 1)
}

function setWorkflowInList(workflow) {
  let index = findIndex(workflows.value, ['key', workflow.key])
  workflows.value[index] = workflow
}

function destroy(workflow) {
  if (!workflow.id) {
    removeWorkflowFromList(workflow.key)
  } else {
    Innoclapps.confirm().then(() => makeDestroyRequest(workflow))
  }
}

function makeDestroyRequest(workflow) {
  Innoclapps.request()
    .delete(`/workflows/${workflow.id}`)
    .then(() => {
      removeWorkflowFromList(workflow.key)

      Innoclapps.success(t('core::workflow.deleted'))
    })
}

Promise.all([
  Innoclapps.request('/workflows'),
  Innoclapps.request('/workflows/triggers'),
]).then(responses => {
  workflows.value.push(
    ...responses[0].data.map(w => ({ ...w, key: randomString() }))
  )
  availableTriggers.value = responses[1].data
  componentReady.value = true
})
</script>
