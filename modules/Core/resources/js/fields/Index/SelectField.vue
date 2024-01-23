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
      :on-click-redirect-to="field.onOptionClickRedirectTo"
      :label-key="field.labelKey"
      :value-key="field.valueKey"
      :list="field.options"
    />
  </IndexFieldItem>
</template>

<script setup>
import OptionsList from '../OptionsList.vue'

defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
