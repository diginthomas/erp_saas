<template>
  <DetailFieldItem
    :field="field"
    :is-floating="isFloating"
    :resource="resource"
    :resource-name="resourceName"
    :resource-id="resourceId"
  >
    <OptionsList
      :value="field.value"
      :label-key="field.labelKey"
      :display-as-pills="field.displayAsPills"
      :on-click-redirect-to="field.onOptionClickRedirectTo"
    />
  </DetailFieldItem>
</template>

<script setup>
import OptionsList from '../OptionsList.vue'

defineProps(['resource', 'resourceName', 'resourceId', 'field', 'isFloating'])
</script>
