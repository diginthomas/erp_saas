<template>
  <IndexFieldItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <OptionsList
      :value="field.value"
      :display-as-pills="field.displayAsPills"
      :each-on-new-line="column.wrap"
      :on-click-redirect-to="field.onOptionClickRedirectTo"
      :label-key="field.labelKey"
    />
  </IndexFieldItem>
</template>

<script setup>
import OptionsList from '../OptionsList.vue'

defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
