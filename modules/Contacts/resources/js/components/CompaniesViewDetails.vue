<template>
  <IOverlay
    :show="!componentReady"
    :class="{
      'rounded-lg bg-white p-6 shadow dark:bg-neutral-900': !componentReady,
    }"
  >
    <div
      v-show="componentReady"
      class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900"
    >
      <div class="flex items-center justify-between px-6 py-3">
        <h2
          v-t="'core::app.record_view.sections.details'"
          class="font-medium text-neutral-800 dark:text-white"
        />

        <div class="flex items-center space-x-1">
          <IButtonIcon
            v-if="resource.authorizations.update"
            v-i-tooltip="$t('core::app.edit')"
            icon="PencilAlt"
            icon-class="size-4.5"
            @click="floatResourceInEditMode({ resourceName, resourceId })"
          />

          <IButtonIcon
            v-if="$gate.isSuperAdmin()"
            v-i-tooltip="$t('core::fields.manage')"
            :to="{
              name: 'resource-fields',
              params: { resourceName },
              query: { view: $scriptConfig('fields.views.detail') },
            }"
            icon="AdjustmentsVertical"
            icon-class="size-4.5"
          />

          <FieldsCollapseButton
            v-if="totalCollapsable > 0"
            v-model:collapsed="fieldsCollapsed"
            :total="totalCollapsable"
          />
        </div>
      </div>

      <DetailFields
        v-if="componentReady"
        v-bind="$attrs"
        class="overflow-y-auto px-3 py-4"
        resizeable
        initial-height-class="h-[7rem]"
        :collapsed="fieldsCollapsed"
        :fields="fields"
        :resource-name="resourceName"
        :resource-id="resourceId"
        :resource="resource"
      />
    </div>
  </IOverlay>
</template>

<script setup>
import { ref, toRef } from 'vue'

import { useFloatingResourceModal } from '@/Core/composables/useFloatingResourceModal'
import { useResourceFields } from '@/Core/composables/useResourceFields'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: [String, Number] },
  resource: { required: true, type: Object },
})

const fieldsCollapsed = ref(true)

const {
  fields,
  hasFields: componentReady,
  setResource,
  totalCollapsable,
  getDetailFields,
} = useResourceFields()

const { floatResourceInEditMode } = useFloatingResourceModal()

getDetailFields(props.resourceName, props.resource.id).then(detailFields => {
  fields.value = detailFields
  setResource(toRef(props, 'resource'))
})
</script>
