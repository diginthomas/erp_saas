<template>
  <ComposeMessage
    v-if="isComposing"
    ref="composeRef"
    :visible="isComposing"
    :resource-name="resourceName"
    :resource-id="resourceId"
    :related-resource="resource"
    :associations="newMessageAdditionalAssociations"
    :to="to"
    @hidden="isComposing = false"
  />

  <BaseEmailField
    v-bind="$attrs"
    ref="baseFieldRef"
    :field="field"
    :is-floating="isFloating"
    :resource="resource"
    :resource-name="resourceName"
    :resource-id="resourceId"
    @show="handleDropdownShownEvent"
  >
    <template v-if="field.value && !isFloating" #start>
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
import { computed, nextTick, ref, toRef } from 'vue'
import { useStore } from 'vuex'

import BaseEmailField from '@/Core/fields/Detail/EmailField.vue'

import { useMessageAssociations } from '../../composables/useMessageAssociations'
import ComposeMessage from '../../views/Emails/ComposeMessage.vue'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps([
  'resource',
  'resourceName',
  'resourceId',
  'field',
  'isFloating',
])

const store = useStore()
const isComposing = ref(false)

const composeRef = ref(null)
const baseFieldRef = ref(null)

const { newMessageAdditionalAssociations } = useMessageAssociations(
  toRef(props, 'resourceName'),
  toRef(props, 'resource')
)

const hasAccountsConfigured = computed(
  () => store.getters['emailAccounts/hasConfigured']
)

const to = computed(() => [
  {
    address: props.field.value,
    name: props.resource.display_name,
    path: props.resource.path, // for associations popover
    resourceName: props.resourceName,
    id: props.resourceId,
  },
])

function compose(state = true) {
  baseFieldRef.value.dropdownRef.hide()
  isComposing.value = state

  nextTick(() => {
    composeRef.value.subjectRef.focus()
  })
}

function handleDropdownShownEvent() {
  store.dispatch('emailAccounts/fetch')
}
</script>
