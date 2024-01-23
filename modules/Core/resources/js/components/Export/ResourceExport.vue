<template>
  <IModal
    :id="modalId"
    :title="computedTitle"
    :ok-loading="exportInProgress"
    :ok-disabled="exportInProgress"
    :ok-text="$t('core::app.export.export')"
    @ok="performExport"
  >
    <IFormGroup :label="$t('core::app.export.type')">
      <IFormSelect v-model="form.type">
        <option value="csv">CSV</option>

        <option value="xls">XLS</option>

        <option value="xlsx">XLSX</option>
      </IFormSelect>
    </IFormGroup>

    <IFormGroup class="space-y-1">
      <template #label>
        <div class="inline-flex items-center space-x-2">
          <IFormLabel tag="p" :label="$t('core::dates.range')" />

          <IDropdownSelect
            v-if="form.period !== 'all'"
            v-model="selectedDateRangeField"
            :items="dateableFields"
            :label="
              !selectedDateRangeField
                ? $t('core::app.created_at')
                : selectedDateRangeField.label
            "
            label-key="label"
            value-key="attribute"
            condensed
            @show="fetchFieldsIfNotFetched"
          />
        </div>
      </template>

      <IFormRadio
        v-for="period in periods"
        :id="period.id"
        :key="period.text"
        v-model="form.period"
        :value="period.value"
        name="period"
        :label="period.text"
      />
    </IFormGroup>

    <IFormGroup v-if="isCustomOptionSelected">
      <div class="sm:ml-6">
        <IFormLabel
          :label="$t('core::app.export.select_range')"
          for="custom-period-start"
          class="mb-1"
        />

        <DateRangePicker
          id="custom-period"
          v-model="form.customPeriod"
          name="custom-period"
        />
      </div>
    </IFormGroup>

    <IFormGroup class="mt-6 space-y-1">
      <IFormRadio
        v-model="fieldsAreBeingSelected"
        :label="$t('core::fields.all')"
        name="select_fields"
        :value="false"
      />

      <IFormRadio
        v-model="fieldsAreBeingSelected"
        :label="$t('core::fields.select')"
        name="select_fields"
        :value="true"
      />
    </IFormGroup>

    <div v-if="fieldsAreBeingSelected">
      <div class="grid grid-cols-3 gap-x-4 gap-y-2">
        <div
          v-for="field in fields"
          :key="field.attribute"
          class="flex items-center rounded border border-neutral-200 px-2 py-1.5 dark:border-neutral-700"
        >
          <IFormCheckbox
            :id="field.attribute"
            v-model:checked="form.fields"
            :label="field.label"
            :value="field.attribute"
            :disabled="field.primary"
          />
        </div>
      </div>
    </div>

    <div
      v-show="canUseFilterForExport"
      class="mt-5 rounded-md border border-neutral-200 bg-neutral-50 p-3 dark:border-neutral-500 dark:bg-neutral-700"
    >
      <IFormCheckbox
        v-model:checked="shouldApplyFilters"
        :label="$t('core::app.export.apply_filters')"
      />
    </div>
  </IModal>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { whenever } from '@vueuse/core'
import FileDownload from 'js-file-download'

import { useDates } from '@/Core/composables/useDates'
import { useForm } from '@/Core/composables/useForm'
import { useQueryBuilder } from '@/Core/composables/useQueryBuilder'

const props = defineProps({
  resourceName: String,
  filtersView: String,
  title: String,
  urlPath: { required: true, type: String },
  modalId: { default: 'export-modal', type: String },
})

const { t } = useI18n()
const { UTCDateTimeInstance } = useDates()

const { form } = useForm({
  period: 'last_7_days',
  type: 'csv',
  fields: [],
  customPeriod: {
    start: UTCDateTimeInstance.startOf('month').toISODate(),
    end: UTCDateTimeInstance.toISODate(),
  },
})

const fields = ref([])
const selectedDateRangeField = ref(null)
const fieldsAreBeingSelected = ref(false)

const fieldsFetched = computed(() => fields.value.length > 0)

const dateableFields = computed(() =>
  fields.value.filter(f => f.dateable === true)
)

const periods = [
  { text: t('core::dates.today'), value: 'today' },
  { text: t('core::dates.periods.7_days'), value: 'last_7_days' },
  { text: t('core::dates.this_month'), value: 'this_month' },
  { text: t('core::dates.last_month'), value: 'last_month' },
  { text: t('core::app.all'), value: 'all', id: 'all' },
  { text: t('core::dates.custom'), value: 'custom', id: 'custom' },
]

const {
  queryBuilderRules,
  hasRulesApplied,
  rulesAreValid: hasValidFilterRules,
} = useQueryBuilder(props.resourceName, props.filtersView)

const shouldApplyFilters = ref(true)

const exportInProgress = ref(false)

const computedTitle = computed(
  () => props.title || t('core::app.export.export')
)

const isCustomOptionSelected = computed(() => form.period === 'custom')

const canUseFilterForExport = computed(
  () => hasRulesApplied.value && hasValidFilterRules.value
)

function getFileNameFromResponseHeaders(response) {
  return response.headers['content-disposition'].split('filename=')[1]
}

async function fetchFieldsIfNotFetched() {
  if (!fieldsFetched.value) {
    fields.value = await retrieveExportFields()
  }
}

async function retrieveExportFields() {
  const { data } = await Innoclapps.request(
    `${props.resourceName}/export-fields`
  )

  return data
}

function performExport() {
  exportInProgress.value = true

  Innoclapps.request()
    .post(
      props.urlPath,
      {
        fields: fieldsAreBeingSelected.value ? form.fields : null,
        date_range_field:
          form.period !== 'all'
            ? selectedDateRangeField.value?.attribute
            : null,
        period: !isCustomOptionSelected.value
          ? form.period === 'all'
            ? null
            : form.period
          : form.customPeriod,
        type: form.type,
        filters:
          shouldApplyFilters.value && canUseFilterForExport.value
            ? queryBuilderRules.value
            : null,
      },
      {
        responseType: 'blob',
      }
    )
    .then(response => {
      FileDownload(response.data, getFileNameFromResponseHeaders(response))
    })
    .finally(() => (exportInProgress.value = false))
}

whenever(fieldsAreBeingSelected, async () => {
  await fetchFieldsIfNotFetched()

  if (form.fields.length === 0) {
    fields.value.forEach(field => {
      form.fields.push(field.attribute)
    })
  }
})
</script>
