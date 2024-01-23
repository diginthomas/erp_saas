<template>
  <IPopover
    v-if="authorizedToUpdate && status === 'open'"
    v-model:visible="popoverVisible"
    class="w-72"
    @show="handleStagePopoverShowEvent"
  >
    <button
      type="button"
      class="flex flex-wrap items-center justify-center text-neutral-600 hover:text-neutral-800 focus:outline-none md:flex-nowrap md:justify-start dark:text-neutral-300 dark:hover:text-neutral-400"
    >
      <span class="w-auto max-w-[15rem] truncate" v-text="dealPipeline.name" />

      <Icon icon="ChevronRight" class="size-3" />

      <span
        class="w-auto max-w-[15rem] truncate text-left"
        v-text="dealStage.name"
      />

      <Icon icon="ChevronDown" class="ml-2 hidden size-4 shrink-0 md:block" />
    </button>

    <template #popper>
      <div class="p-4">
        <ICustomSelect
          v-model="selectPipeline"
          :options="pipelines"
          :clearable="false"
          class="mb-2"
          label="name"
          @option-selected="handlePipelineChangedEvent"
          @update:model-value="
            form.errors.clear('pipeline_id'), form.errors.clear('stage_id')
          "
        />

        <IFormError :error="form.getError('pipeline_id')" />

        <ICustomSelect
          v-model="selectPipelineStage"
          :options="selectPipeline ? selectPipeline.stages : []"
          :clearable="false"
          label="name"
          @update:model-value="form.errors.clear('stage_id')"
        />

        <IFormError :error="form.getError('stage_id')" />
      </div>

      <div
        class="flex justify-end space-x-1 bg-neutral-100 px-4 py-3 dark:bg-neutral-900"
      >
        <IButton
          variant="white"
          :disabled="form.busy"
          :text="$t('core::app.cancel')"
          @click="popoverVisible = false"
        />

        <IButton
          variant="primary"
          :text="$t('core::app.save')"
          :loading="form.busy"
          :disabled="form.busy || !selectPipelineStage"
          @click="saveStageChange"
        />
      </div>
    </template>
  </IPopover>

  <div v-else class="flex items-center text-neutral-600 dark:text-neutral-300">
    <span v-text="dealPipeline.name" />

    <Icon icon="ChevronRight" class="size-3" />

    <span v-text="dealStage.name" />
  </div>
</template>

<script setup>
import { computed, ref, shallowRef } from 'vue'

import { useForm } from '@/Core/composables/useForm'
import { useResourceable } from '@/Core/composables/useResourceable'

import { usePipelines } from '../composables/usePipelines'

const props = defineProps({
  dealId: { required: true, type: Number },
  pipeline: { required: true, type: Object }, // use directly from deal in case the pipeline is hidden from the current user
  stageId: { required: true, type: Number },
  status: { required: true, type: String },
  authorizedToUpdate: { required: true, type: Boolean },
})

const emit = defineEmits(['updated'])

const { orderedPipelines: pipelines } = usePipelines()
const { form } = useForm()
const { updateResource } = useResourceable(Innoclapps.resourceName('deals'))

const dealPipeline = computed(() => props.pipeline)

const dealStage = computed(
  () => props.pipeline.stages.filter(stage => stage.id == props.stageId)[0]
)

const popoverVisible = ref(false)
const selectPipeline = shallowRef(null)
const selectPipelineStage = shallowRef(null)

async function saveStageChange() {
  let updatedDeal = await updateResource(
    form.fill({
      pipeline_id: selectPipeline.value.id,
      stage_id: selectPipelineStage.value.id,
    }),
    props.dealId
  )

  emit('updated', updatedDeal)
  popoverVisible.value = false
}

function handleStagePopoverShowEvent() {
  selectPipeline.value = dealPipeline.value
  selectPipelineStage.value = dealStage.value
}

function handlePipelineChangedEvent(value) {
  if (value.id != props.pipeline.id) {
    // Use the first stage selected from the new pipeline
    selectPipelineStage.value = value.stages[0] || null
  } else if (value.id === props.pipeline.id) {
    // revent back to the original stage after the user select new stage
    // and goes back to the original without saving
    selectPipelineStage.value = dealStage.value
  }
}
</script>
