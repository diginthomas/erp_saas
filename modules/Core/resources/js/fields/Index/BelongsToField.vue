<template>
  <IndexFieldItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <OptionsList
      :value="field.value"
      :field="field"
      :label-key="field.labelKey"
      :display-as-pills="field.displayAsPills"
      :on-click-redirect-to="field.onOptionClickRedirectTo"
    />
  </IndexFieldItem>
</template>

<script setup>
import OptionsList from '../OptionsList.vue'

defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
