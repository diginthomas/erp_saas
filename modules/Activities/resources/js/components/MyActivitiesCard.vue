<template>
  <CreateActivityModal
    :visible="activityBeingCreated"
    @created="handleActivityCreatedEvent"
    @hidden="activityBeingCreated = false"
  />

  <CardAsyncTable ref="tableRef" :card="card">
    <template #actions>
      <IButton
        variant="white"
        icon="Plus"
        :text="$t('activities::activity.create')"
        class="hide-when-editing -mb-1"
        @click="activityBeingCreated = true"
      />
    </template>

    <template #empty="slotProps">
      <div
        class="flex flex-col justify-center"
        :class="{
          'items-center py-4': !slotProps.search && !slotProps.loading,
        }"
      >
        <Icon
          v-show="!slotProps.search && !slotProps.loading"
          icon="Check"
          class="size-8 text-success-500"
        />

        <p
          class="text-neutral-500 dark:text-neutral-300"
          :class="{ 'mt-2 text-lg': !slotProps.search && !slotProps.loading }"
        >
          <span v-show="slotProps.loading" v-text="slotProps.text"></span>

          <span v-show="!slotProps.loading">
            {{
              slotProps.search ? slotProps.text : $t('core::app.all_caught_up')
            }}
          </span>
        </p>
      </div>
    </template>

    <template #title="{ row, formatted }">
      <div class="relative">
        <div class="group flex items-center">
          <div class="mr-1.5 mt-1 self-start sm:self-auto">
            <StateChange
              :activity-id="row.id"
              :is-completed="row.is_completed"
              :disabled="!row.authorizations.update"
              @changed="reloadTable"
            />
          </div>

          <div
            class="whitespace-normal group-hover:left-6 hover:max-w-none sm:max-w-[13rem] sm:truncate sm:whitespace-nowrap sm:group-hover:absolute sm:hover:whitespace-normal"
          >
            <ILink
              :text="formatted"
              @click="
                floatResourceInEditMode({
                  resourceName: 'activities',
                  resourceId: row.id,
                })
              "
            />
          </div>
        </div>
      </div>
    </template>
    <!-- eslint-disable-next-line vue/valid-v-slot -->
    <template #type.name="{ row, formatted }">
      <TextBackground
        :color="row.type.swatch_color"
        class="inline-flex items-center justify-center self-start rounded-md px-2.5 py-px text-sm/5 dark:!text-white"
      >
        <Icon :icon="row.type.icon" class="mr-1.5 size-4" />

        <span>{{ formatted }}</span>
      </TextBackground>
    </template>
  </CardAsyncTable>
</template>

<script setup>
import { ref } from 'vue'

import { useFloatingResourceModal } from '@/Core/composables/useFloatingResourceModal'

import StateChange from './ActivityStateChange.vue'

defineProps({ card: Object })

const tableRef = ref(null)
const activityBeingCreated = ref(false)

const { floatResourceInEditMode } = useFloatingResourceModal()

function handleActivityCreatedEvent() {
  reloadTable()
  activityBeingCreated.value = false
}

function reloadTable() {
  tableRef.value.reload()
}
</script>

<style scoped>
:deep(tr > td) {
  position: relative;
}

:deep(tr > td:first-child:after),
:deep(tr > td:first-child:before) {
  content: '';
  position: absolute;
  left: 0;
  width: 100%;
}

:deep(td.due:first-child:before),
:deep(td.due:first-child:after) {
  width: auto;
  height: 100%;
  top: 0;
  border-left: 3px solid rgba(var(--color-danger-500), 1);
}

:deep(td.not-due:first-child:before),
:deep(td.not-due:first-child:after) {
  width: auto;
  height: 100%;
  top: 0;
  border-left: 3px solid transparent;
}
</style>
