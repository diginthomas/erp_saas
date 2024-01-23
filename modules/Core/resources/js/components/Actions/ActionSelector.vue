<template>
  <component
    :is="type === 'select' ? ActionSelectorSelect : ActionSelectorDropdown"
    v-if="hasActionsAvailable"
    :actions="filteredActions"
    @run="actionExecutedHandler"
  />
</template>

<script setup>
import { computed } from 'vue'

import ActionSelectorDropdown from './ActionSelectorDropdown.vue'
import ActionSelectorSelect from './ActionSelectorSelect.vue'

const props = defineProps({
  actions: { type: Array, default: () => [] },
  type: { required: true, type: String },
  view: {
    default: 'detail',
    validator: function (value) {
      return ['index', 'detail'].indexOf(value) !== -1
    },
  },
})

const emit = defineEmits(['run'])

const filteredActions = computed(() =>
  props.actions.filter(action => {
    if (props.view === 'detail' && action.hideOnDetail === true) {
      return false
    }

    if (props.view === 'index' && action.hideOnIndex === true) {
      return false
    }

    return true
  })
)

const hasActionsAvailable = computed(() => filteredActions.value.length > 0)

function actionExecutedHandler(response) {
  emit('run', response)
}
</script>
