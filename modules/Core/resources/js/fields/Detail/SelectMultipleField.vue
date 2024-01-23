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
      :display-as-pills="field.displayAsPills"
      :each-on-new-line="field.eachOnNewLine"
      :on-click-redirect-to="field.onOptionClickRedirectTo"
      :label-key="field.labelKey"
    />
  </DetailFieldItem>
</template>

<script setup>
import OptionsList from '../OptionsList.vue'

defineProps(['resource', 'resourceName', 'resourceId', 'field', 'isFloating'])
</script>
