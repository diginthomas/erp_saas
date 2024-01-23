<template>
  <Compose
    v-if="isComposing"
    ref="composeRef"
    :visible="isComposing"
    :to="to"
    @hidden="isComposing = false"
  />

  <BaseEmailField
    v-bind="$attrs"
    ref="baseFieldRef"
    :column="column"
    :row="row"
    :field="field"
    :resource-name="resourceName"
    :resource-id="resourceId"
    @show="handleDropdownShownEvent"
  >
    <template v-if="field.value" #start>
      <span
        v-i-tooltip="
          hasAccountsConfigured
            ? ''
            : $t('mailclient::mail.account.integration_not_configured')
        "
      >
        <IDropdownItem
          href="#"
          :disabled="!hasAccountsConfigured"
          :text="$t('mailclient::mail.create')"
          @click="compose(true)"
        />
      </span>
    </template>
  </BaseEmailField>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue'
import { useStore } from 'vuex'

import BaseEmailField from '@/Core/fields/Index/EmailField.vue'

import Compose from '../../views/Emails/ComposeMessage.vue'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps([
  'column',
  'row',
  'field',
  'resourceName',
  'resourceId',
])

const store = useStore()
const isComposing = ref(false)

const composeRef = ref(null)
const baseFieldRef = ref(null)

const hasAccountsConfigured = computed(
  () => store.getters['emailAccounts/hasConfigured']
)

/**
 * Get the predefined TO property
 */
const to = computed(() => [
  {
    address: props.field.value,
    name: props.row.display_name || props.row.name,
    resourceName: props.resourceName,
    path: props.row.path, // for associations popover
    id: props.row.id,
  },
])

/**
 * Compose new email
 */
function compose(state = true) {
  isComposing.value = state
  baseFieldRef.value.dropdownRef.hide()

  nextTick(() => {
    composeRef.value.subjectRef.focus()
  })
}

function handleDropdownShownEvent() {
  store.dispatch('emailAccounts/fetch')
}
</script>
