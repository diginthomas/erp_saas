<template>
  <IDropdownSelect
    v-model="action"
    :items="actions"
    placement="bottom-end"
    :label="$t('core::actions.actions')"
    label-key="name"
    value-key="uriKey"
    @change="run"
  />
</template>

<script setup>
import { computed, toRef } from 'vue'
import castArray from 'lodash/castArray'

import { useAction } from '../../composables/useAction'

const props = defineProps({
  resourceName: { type: String, required: true },
  ids: { type: [Number, String, Array], required: true },
  actionRequestParams: { type: Object, default: () => ({}) },
  actions: { type: Array, default: () => [] },
})

const emit = defineEmits(['run'])

const actionIds = computed(() => castArray(props.ids))

const { run, action } = useAction(
  actionIds,
  {
    resourceName: toRef(props, 'resourceName'),
    requestParams: toRef(props, 'actionRequestParams'),
  },
  response => emit('run', response)
)
</script>
