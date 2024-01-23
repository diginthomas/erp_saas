<template>
  <PresentationChart :card="card" :request-query-string="requestQueryString">
    <template #actions>
      <IDropdownSelect
        v-model="pipeline"
        :items="pipelines"
        class="md:mr-3"
        label-key="name"
        value-key="id"
        placement="bottom-end"
        truncate="max-w-[13rem]"
        adaptive-width
        condensed
      />
    </template>
  </PresentationChart>
</template>

<script setup>
import { computed, shallowRef } from 'vue'

import { usePipelines } from '../composables/usePipelines'

const props = defineProps({
  card: Object,
})

const { orderedPipelines: pipelines, findPipelineById } = usePipelines()
const pipeline = shallowRef(findPipelineById(props.card.pipeline_id)) // active selected pipeline

const requestQueryString = computed(() => ({
  pipeline_id: pipeline.value.id,
}))
</script>
