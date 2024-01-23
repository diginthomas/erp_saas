<template>
  <IndexFieldItem
    v-slot="{ hasValue, value }"
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <IDropdown
      v-if="hasValue"
      ref="dropdownRef"
      tag="a"
      no-caret
      class="link link-primary"
      :href="'mailto:' + value"
      @show="$emit('show')"
    >
      <template #toggle-content>
        {{ value }}
      </template>

      <slot name="start" :email="value" />

      <IButtonCopy
        :success-message="$t('core::fields.email_copied')"
        :text="value"
        tag="IDropdownItem"
      >
        {{ $t('core::app.copy') }}
      </IButtonCopy>

      <IDropdownItem
        :href="'mailto:' + value"
        :text="$t('core::app.open_in_app')"
      />
    </IDropdown>

    <span v-else>&mdash;</span>
  </IndexFieldItem>
</template>

<script setup>
import { ref } from 'vue'

defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])

defineEmits(['show'])

const dropdownRef = ref(null)

defineExpose({
  dropdownRef,
})
</script>
