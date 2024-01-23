<template>
  <DetailFieldItem
    v-slot="{ hasValue, value }"
    :field="field"
    :is-floating="isFloating"
    :resource="resource"
    :resource-name="resourceName"
    :resource-id="resourceId"
  >
    <IDropdown
      v-if="hasValue"
      ref="dropdownRef"
      no-caret
      tag="a"
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
  </DetailFieldItem>
</template>

<script setup>
import { ref } from 'vue'

defineProps(['resource', 'resourceName', 'resourceId', 'field', 'isFloating'])

defineEmits(['show'])

const dropdownRef = ref(null)

defineExpose({
  dropdownRef,
})
</script>
