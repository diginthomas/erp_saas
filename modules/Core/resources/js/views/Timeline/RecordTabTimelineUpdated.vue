<template>
  <BaseRecordTabTimelineItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :created-at="log.created_at"
    :is-pinned="log.is_pinned"
    :timelineable-id="log.id"
    :timeline-relationship="log.timeline_relation"
    :timeline-subject-key="resource.timeline_subject_key"
    :timelineable-key="log.timeline_key"
    icon="PencilAlt"
  >
    <template #heading>
      <I18nT
        v-if="log.causer_name"
        v-once
        scope="global"
        keypath="core::timeline.updated"
      >
        <template #causer>
          <span class="font-medium" v-text="log.causer_name"></span>
        </template>
      </I18nT>

      <span v-else v-once v-text="$t('core::timeline.updated')"></span>
    </template>

    <div class="mt-2">
      <div v-if="changesVisible">
        <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
          {{ $t('core::fields.updated') }} ({{ totalUpdatedAttributes }})
        </p>

        <div
          v-once
          class="mt-2 overflow-hidden rounded-lg border border-neutral-200 bg-white dark:border-neutral-700"
        >
          <ITable bordered-inner="y">
            <thead>
              <tr>
                <th v-t="'core::fields.updated_field'" class="text-left" />

                <th v-t="'core::fields.new_value'" class="text-left" />

                <th v-t="'core::fields.old_value'" class="text-left" />
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="(attribute, key) in updatedAttributes"
                :key="resourceName + key"
              >
                <td class="font-medium">
                  {{ getLabel(attribute, key) }}
                </td>

                <td>
                  <!-- For custom fields -->
                  {{ determineChangedFieldValue(attribute, key) }}
                </td>

                <td>
                  <!-- For custom fields -->
                  {{ determineChangedFieldValue(log.properties.old[key], key) }}
                </td>
              </tr>
            </tbody>
          </ITable>
        </div>
      </div>

      <ILink
        class="mt-2 block"
        :text="updatedFieldsText"
        @click="changesVisible = !changesVisible"
      />
    </div>
  </BaseRecordTabTimelineItem>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import pickBy from 'lodash/pickBy'

import { useDates } from '@/Core/composables/useDates'

import BaseRecordTabTimelineItem from './BaseRecordTabTimelineItem.vue'

const props = defineProps({
  log: { type: Object, required: true },
  resourceName: { type: String, required: true },
  resourceId: { type: [String, Number], required: true },
  resource: { type: Object, required: true },
})

const { t, te } = useI18n()
const { localizeIfDate } = useDates()

const changesVisible = ref(false)

const updatedFieldsText = computed(() =>
  changesVisible.value
    ? t('core::fields.hide_updated')
    : t('core::fields.view_updated') + ' (' + totalUpdatedAttributes.value + ')'
)

/**
 * Excluded the one that ends with _id because they are probably relation ID,
 * We are tracking the relations display name as well so we can display new and old value
 * in proper format not the actual ID.
 *
 */
const updatedAttributes = computed(() => {
  return pickBy(
    props.log.properties.attributes,
    (attribute, field) => field.indexOf('_id') === -1
  )
})

const totalUpdatedAttributes = computed(
  () => Object.keys(updatedAttributes.value).length
)

function getLabel(attribute, key) {
  // Check if the attributes has label key, usually used in custom fields where
  // label and value is stored separately e.q. {label: 'Label', value:'Value'}
  if (attribute && attribute.label) {
    return attribute.label
  }

  if (
    (props.log.module,
    props.log.module + '::fields.' + props.resourceName + '.' + key)
  ) {
    if (te(props.log.module + '::fields.' + props.resourceName + '.' + key)) {
      return t(props.log.module + '::fields.' + props.resourceName + '.' + key)
    }
  }

  return key
}

function determineChangedFieldValue(data, key) {
  return data && Object.hasOwn(data, 'value')
    ? localizeIfDate(data.value, data.value)
    : te('core::timeline.' + key + '.' + data)
      ? t('core::timeline.' + key + '.' + data)
      : localizeIfDate(data, data)
}
</script>
