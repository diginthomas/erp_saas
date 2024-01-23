<template>
  <IndexFieldItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <OptionsList
      :value="field.value"
      :label-key="field.labelKey"
      :on-click-redirect-to="field.onOptionClickRedirectTo"
      :display-as-pills="true"
    />
  </IndexFieldItem>
</template>

<script setup>
import OptionsList from '@/Core/fields/OptionsList.vue'

defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
