<template>
  <ICard no-body>
    <template #header>
      <slot name="header">
        <ICardHeading>
          {{ header || $t('core::import.import_records') }}
        </ICardHeading>
      </slot>
    </template>

    <template #actions>
      <IButton
        v-if="!importingViaBatch"
        variant="white"
        icon="DocumentDownload"
        :text="$t('core::import.download_sample')"
        @click="downloadSample(`/${resourceName}/import/sample`)"
      />
    </template>

    <ResourceImportSteps v-if="!importingViaBatch" :steps="steps" />

    <ICardBody>
      <IAlert v-if="importingViaBatch">
        <div class="-mt-0.5 flex items-center space-x-2">
          <h4
            v-t="'core::import.import_in_progress'"
            class="text-base font-medium text-info-700"
          />

          <ISpinner class="size-4"></ISpinner>
        </div>
        {{ $t('core::import.records_being_imported_in_batches') }}
      </IAlert>

      <IAlert
        v-if="rowsExceededMessage"
        dismissible
        variant="danger"
        class="mb-5"
      >
        {{ rowsExceededMessage }}
      </IAlert>

      <MediaUpload
        v-if="!importingViaBatch"
        class="ml-5"
        :action-url="
          appendQueryString(
            `${$scriptConfig('apiURL')}/${resourceName}/import/upload`
          )
        "
        extensions="csv"
        :multiple="false"
        :show-output="false"
        :upload-text="$t('core::import.start')"
        @file-uploaded="handleFileUploaded"
      />

      <div v-if="importBeingMapped" class="mt-5 text-sm">
        <h3
          v-t="'core::import.spreadsheet_columns'"
          class="mb-3 text-lg font-medium text-neutral-700 dark:text-neutral-200"
        />

        <div class="flex">
          <div class="w-1/2">
            <div
              v-for="(column, index) in importBeingMapped.mappings"
              :key="'mapping-' + index"
              :class="[
                {
                  'bg-neutral-100 dark:bg-neutral-800': !column.attribute,
                  'bg-white dark:bg-neutral-700': column.attribute,
                },
                'mb-2 mr-3 flex h-16 flex-col justify-center rounded-md px-4 ring-1 ring-inset ring-neutral-300 dark:ring-neutral-500',
              ]"
            >
              <IFormLabel :required="isColumnRequired(column)">
                {{ column.original }}
                <span
                  v-if="column.skip && !isColumnRequired(column)"
                  class="text-xs"
                >
                  ({{ $t('core::import.column_will_not_import') }})
                </span>
              </IFormLabel>

              <p
                class="truncate text-neutral-500 dark:text-neutral-300"
                v-text="column.preview"
              />
            </div>
          </div>

          <div class="w-1/2">
            <div
              v-for="(column, index) in importBeingMapped.mappings"
              :key="'field-' + index"
              class="mb-2 flex h-16 items-center"
            >
              <Icon
                icon="ChevronRight"
                class="mr-3 size-5 text-neutral-800 dark:text-neutral-200"
              />

              <IFormSelect
                v-model="importBeingMapped.mappings[index].attribute"
                :size="false"
                class="h-16 px-4 py-5 hover:bg-neutral-100 dark:hover:bg-neutral-600"
                @input="importBeingMapped.mappings[index].skip = !$event"
              >
                <option v-if="!isColumnRequired(column)" value="">N/A</option>

                <option
                  v-for="field in importBeingMapped.fields"
                  :key="'field-' + index + '-' + field.attribute"
                  :disabled="isFieldMapped(field.attribute)"
                  :value="field.attribute"
                >
                  {{ field.label }}
                </option>
              </IFormSelect>
            </div>
          </div>
        </div>
      </div>
    </ICardBody>

    <template v-if="importBeingMapped" #footer>
      <div class="flex items-center justify-end space-x-2">
        <IButton
          :disabled="importIsInProgress"
          variant="white"
          :text="$t('core::app.cancel')"
          @click="destroy(importBeingMapped.id)"
        />

        <IButton
          :loading="importIsInProgress"
          :disabled="importIsInProgress"
          :text="
            importIsInProgress
              ? $t('core::app.please_wait')
              : $t('core::import.import')
          "
          @click="performImport"
        />
      </div>
    </template>
  </ICard>

  <ICard
    class="mt-5"
    :overlay="loadingImportHistory"
    header-class="relative overflow-hidden"
    :header="$t('core::import.history')"
    no-body
  >
    <ITable v-if="hasImportHistory" bordered-inner="y">
      <thead>
        <tr>
          <th v-t="'core::import.date'" class="text-left" />

          <th v-t="'core::import.file_name'" class="text-left" />

          <th v-t="'core::import.user'" class="text-left" />

          <th v-t="'core::import.total_imported'" class="text-center" />

          <th v-t="'core::import.total_duplicates'" class="text-center" />

          <th v-t="'core::import.total_skipped'" class="text-center" />

          <th v-t="'core::import.progress'" class="text-center" />

          <th v-t="'core::import.status'" class="text-center" />

          <th />
        </tr>
      </thead>

      <tbody>
        <template v-for="history in computedImports" :key="history.id">
          <tr>
            <td
              class="text-left font-medium"
              v-text="localizedDateTime(history.created_at)"
            />

            <td class="text-left" v-text="history.file_name" />

            <td class="text-left" v-text="history.user.name" />

            <td>
              <div class="flex items-center justify-center space-x-1">
                <span>
                  {{ history.imported }}
                </span>

                <ILink
                  v-if="
                    history.revertable &&
                    history.authorizations.revert &&
                    !revertInProgress[history.id]
                  "
                  class="font-medium"
                  :text="$t('core::import.revert')"
                  variant="danger"
                  xs
                  @click="revert(history)"
                />

                <ISpinner v-if="revertInProgress[history.id]" class="size-4" />
              </div>
            </td>

            <td class="text-center" v-text="history.duplicates" />

            <td class="text-center">
              {{ history.skipped }}
              <span
                v-if="
                  history.skip_file_filename &&
                  history.skipped > 0 &&
                  (history.authorizations.downloadSkipFile ||
                    history.authorizations.uploadFixedSkipFile)
                "
              >
                <ILink
                  class="font-medium"
                  :text="$t('core::import.why_skipped')"
                  xs
                  @click="
                    showSkipInfoFor === history.id
                      ? (showSkipInfoFor = null)
                      : (showSkipInfoFor = history.id)
                  "
                />
              </span>
            </td>

            <td class="text-center align-middle">
              <div class="relative h-4 rounded-full bg-neutral-200">
                <div
                  class="h-4 rounded-full bg-success-500"
                  :style="{ width: history.progress + '%' }"
                />

                <span
                  class="absolute inset-0 flex items-center justify-center text-xs font-medium text-neutral-900"
                >
                  {{ history.progress }}%
                </span>
              </div>
            </td>

            <td class="text-center">
              <span v-i-tooltip="history.status" class="inline-block">
                <Icon
                  v-if="history.status === 'mapping'"
                  icon="Bars3CenterLeft"
                  class="size-5 animate-pulse text-neutral-500 dark:text-neutral-400"
                />

                <Icon
                  v-else-if="history.status === 'finished'"
                  icon="CheckCircle"
                  class="size-5 text-success-500 dark:text-success-400"
                />

                <Icon
                  v-else-if="history.status === 'in-progress'"
                  icon="DotsHorizontal"
                  class="size-5 animate-bounce text-neutral-500 dark:text-neutral-400"
                />
              </span>
            </td>

            <td>
              <div class="flex items-center justify-end space-x-2">
                <ILink
                  v-if="
                    history.status === 'mapping' &&
                    (!importBeingMapped ||
                      (importBeingMapped && importBeingMapped.id != history.id))
                  "
                  :text="$t('core::app.continue')"
                  @click="continueMapping(history.id)"
                />

                <IButtonIcon
                  v-if="
                    !importingViaBatch &&
                    !revertInProgress[history.id] &&
                    history.authorizations.delete
                  "
                  icon="Trash"
                  @click="destroy(history.id, true)"
                />
              </div>
            </td>
          </tr>

          <tr
            v-if="
              history.skip_file_filename &&
              showSkipInfoFor === history.id &&
              (history.authorizations.downloadSkipFile ||
                history.authorizations.uploadFixedSkipFile)
            "
          >
            <td colspan="4">
              <h3
                class="text-base/6 font-medium text-neutral-900 dark:text-white"
              >
                {{ $t('core::import.skip_file') }}
                <span class="text-sm text-neutral-500 dark:text-neutral-300">
                  {{
                    $t('core::import.total_rows_skipped', {
                      count: history.skipped,
                    })
                  }}
                </span>
              </h3>

              <p
                v-t="'core::import.skip_file_generation_info'"
                class="mt-3 max-w-4xl font-normal"
              />

              <p
                v-t="'core::import.skip_file_fix_and_continue'"
                class="my-2 max-w-3xl font-normal"
              />

              <div class="mb-4 mt-8 flex items-center">
                <ILink
                  v-if="history.authorizations.downloadSkipFile"
                  :text="$t('core::import.download_skip_file')"
                  class="mr-4"
                  @click="downloadSkipFile(history.id)"
                />

                <media-upload
                  v-if="history.authorizations.uploadFixedSkipFile"
                  :action-url="
                    appendQueryString(
                      `${$scriptConfig('apiURL').replace(
                        /\/+$/,
                        ''
                      )}${generateImportUri(history.id, '/skip-file')}`
                    )
                  "
                  extensions="csv"
                  name="skip_file"
                  input-id="skip_file"
                  :multiple="false"
                  :show-output="false"
                  :select-file-text="$t('core::import.upload_fixed_skip_file')"
                  :upload-text="$t('core::import.start')"
                  @file-uploaded="handleSkipFileUploaded($event)"
                />
              </div>
            </td>

            <td colspan="4" />
          </tr>
        </template>
      </tbody>
    </ITable>

    <ICardBody v-else v-show="!loadingImportHistory" class="text-center">
      <Icon icon="DocumentText" class="mx-auto size-12 text-neutral-400" />

      <h3
        v-t="'core::import.no_history'"
        class="mt-2 text-sm text-neutral-600 dark:text-neutral-200"
      />
    </ICardBody>
  </ICard>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import FileDownload from 'js-file-download'
import find from 'lodash/find'
import findIndex from 'lodash/findIndex'
import orderBy from 'lodash/orderBy'

import MediaUpload from '@/Core/components/Media/MediaUpload.vue'
import { useApp } from '@/Core/composables/useApp'
import { useDates } from '@/Core/composables/useDates'

import ResourceImportSteps from './ResourceImportSteps.vue'

const props = defineProps({
  header: String,
  resourceName: { required: true, type: String },
  requestQueryString: { type: Object, default: () => ({}) },
})

const { t } = useI18n()
const { resetStoreState } = useApp()
const { localizedDateTime } = useDates()
const route = useRoute()
const router = useRouter()

const imports = ref([])
const usingSkipFile = ref(false)
const showSkipInfoFor = ref(null)
const importBeingMapped = ref(null)
const rowsExceededMessage = ref(null)
const loadingImportHistory = ref(false)
const importIsInProgress = ref(false)
const revertInProgress = ref({})
const importingViaBatch = ref(false)

const steps = ref([
  {
    id: '01',
    name: t('core::import.steps.step_1.name'),
    description: t('core::import.steps.step_1.description'),
    status: 'current',
  },
  {
    id: '02',
    name: t('core::import.steps.step_2.name'),
    description: t('core::import.steps.step_2.description'),
    status: 'upcoming',
  },
  {
    id: '03',
    name: t('core::import.steps.step_3.name'),
    description: t('core::import.steps.step_3.description'),
    status: 'upcoming',
  },
  {
    id: '04',
    name: t('core::import.steps.step_4.name'),
    description: t('core::import.steps.step_4.description'),
    status: 'upcoming',
  },
])

/**
 * Get the computed imports ordered by date
 */
const computedImports = computed(() =>
  orderBy(imports.value, 'created_at', 'desc')
)

/**
 * Indicates whether the resource has import history
 */
const hasImportHistory = computed(() => computedImports.value.length > 0)

/**
 * Change the current import step to
 */
function changeCurrentStep(id, status) {
  // When changing to "complete" or "current" we will
  // update all other steps below this step to complete
  if (status === 'complete' || status === 'current') {
    let stepsBelowStep = steps.value.filter(
      step => parseInt(step.id) < parseInt(id)
    )

    stepsBelowStep.forEach(step => (step.status = 'complete'))
  }

  if (status === 'current') {
    // When changing to current, all steps above this step will be upcoming
    let stepsAboveStep = steps.value.filter(
      step => parseInt(step.id) > parseInt(id)
    )

    stepsAboveStep.forEach(step => (step.status = 'upcoming'))
  }

  steps.value[findIndex(steps.value, ['id', id])].status = status
}

/**
 * Create URL for the import request.
 */
function generateImportUri(id, extra = '') {
  return `/${props.resourceName}/import/${id}${extra}`
}

/**
 * Append query string to the given url
 */
function appendQueryString(url, queryString = {}) {
  if (
    Object.keys(props.requestQueryString).length > 0 ||
    Object.keys(queryString).length > 0
  ) {
    let str = []
    let allQueryString = { ...props.requestQueryString, ...queryString }

    for (var q in allQueryString)
      str.push(
        encodeURIComponent(q) + '=' + encodeURIComponent(allQueryString[q])
      )

    url += '?' + str.join('&')
  }

  return url
}

/**
 * Download skip file for the give import id
 * */
function downloadSkipFile(id) {
  Innoclapps.request(generateImportUri(id, '/skip-file'), {
    responseType: 'blob',
  }).then(response => {
    FileDownload(
      response.data,
      response.headers['content-disposition'].split('filename=')[1]
    )
  })
}

/**
 * Download sample import file
 */
function downloadSample(route) {
  Innoclapps.request(route).then(({ data }) => {
    FileDownload(data, 'sample.csv')

    if (
      steps.value[0].status === 'current' ||
      steps.value[3].status === 'complete'
    ) {
      changeCurrentStep('02', 'current')
    }
  })
}

/**
 * Check whether the field is mapped in a column
 */
function isFieldMapped(attribute) {
  return Boolean(
    find(importBeingMapped.value.mappings, ['attribute', attribute])
  )
}

/**
 * Revert the given import.
 */
async function revert(history) {
  await Innoclapps.confirm({
    message: t('core::import.revert_info'),
    confirmText: t('core::import.revert'),
  })

  const id = history.id
  const recordsPerRequest = 500
  const totalRecords = history.imported
  const totalRequests = Math.ceil(totalRecords / recordsPerRequest)

  revertInProgress.value[id] = true

  const performRevert = async () => {
    try {
      for (let i = 1; i <= totalRequests; i++) {
        try {
          const { data } = await Innoclapps.request().delete(
            generateImportUri(id, '/revert?limit=' + recordsPerRequest)
          )

          imports.value[findIndex(imports.value, ['id', parseInt(id)])] = data
        } catch {
          break
        }
      }
    } finally {
      revertInProgress.value[id] = false
    }
  }

  performRevert()
}

/**
 * Delete the given history
 */
async function destroy(id, force) {
  if (usingSkipFile.value && force !== true) {
    importBeingMapped.value = null

    return
  }

  await Innoclapps.confirm()
  await Innoclapps.request().delete(generateImportUri(id))

  handleAfterDelete(id)
}

function handleAfterDelete(id) {
  imports.value.splice(findIndex(imports.value, ['id', id]), 1)

  if (importBeingMapped.value && id == importBeingMapped.value.id) {
    importBeingMapped.value = null
    usingSkipFile.value = false
    changeCurrentStep('01', 'current')
  }
}

/**
 * Continue mapping the given import
 */
function continueMapping(id) {
  setImportForMapping(find(imports.value, ['id', parseInt(id)]))
  changeCurrentStep('03', 'current')
}

/**
 * Set the import instance for mapping
 */
function setImportForMapping(instance) {
  importBeingMapped.value = instance
}

/**
 * Retrieve the current resource imports
 */
async function retrieveImports() {
  loadingImportHistory.value = true

  const { data } = await Innoclapps.request(`${props.resourceName}/import`)

  imports.value = data
  loadingImportHistory.value = false
}

/**
 * Check whether the given import column is required
 */
function isColumnRequired(column) {
  let columnField = find(importBeingMapped.value.fields, [
    'attribute',
    column.detected_attribute,
  ])

  if (!column.detected_attribute || !columnField) {
    return false
  }

  return columnField.isRequired
}

/**
 * Handle file uploaded
 */
function handleFileUploaded(importBeingMapped) {
  setImportForMapping(importBeingMapped)
  imports.value.push(importBeingMapped)
  changeCurrentStep('03', 'current')
  rowsExceededMessage.value = null
}

/**
 * Handle skip file uploaded
 */
function handleSkipFileUploaded(importBeingMapped) {
  setImportForMapping(importBeingMapped)
  let index = findIndex(imports.value, ['id', parseInt(importBeingMapped.id)])
  imports.value[index] = importBeingMapped
  changeCurrentStep('03', 'current')
  usingSkipFile.value = true
}

function continueImport(id) {
  importingViaBatch.value = true

  Innoclapps.request()
    .post(appendQueryString(generateImportUri(id)))
    .then(({ data }) => {
      imports.value[findIndex(imports.value, ['id', parseInt(data.id)])] = data

      if (data.next_batch) {
        // Allow the user to see the progress in the UI properly.
        setTimeout(() => (window.location.href = `?import_id=${data.id}`), 2000)
      } else {
        router.replace({ query: {} })
        importingViaBatch.value = false
        Innoclapps.success(t('core::import.imported'))
      }

      // In case of any custom options created, reset the
      // store state for the cached fields
      resetStoreState()
    })
    .catch(() => {
      router.replace({ query: {} })
      importingViaBatch.value = false
    })
}

/**
 * Perform the import for the current import instance
 */
function performImport() {
  importIsInProgress.value = true

  Innoclapps.request()
    .post(appendQueryString(generateImportUri(importBeingMapped.value.id)), {
      mappings: importBeingMapped.value.mappings,
    })
    .then(({ data }) => {
      let index = findIndex(imports.value, ['id', parseInt(data.id)])

      if (index !== -1) {
        imports.value[index] = data
      } else {
        imports.value.push(data)
      }

      if (data.next_batch) {
        setTimeout(() => (window.location.href = `?import_id=${data.id}`), 1000)

        return
      }

      Innoclapps.success(t('core::import.imported'))
      importBeingMapped.value = null

      changeCurrentStep('04', 'complete')

      // In case of any custom options created, reset the
      // store state for the cached fields
      resetStoreState()
    })
    .catch(error => {
      let data = error.response.data

      if (data.deleted || data.rows_exceeded) {
        if (data.deleted) {
          handleAfterDelete(importBeingMapped.value.id)
        }

        if (data.rows_exceeded) {
          rowsExceededMessage.value = error.response.data.message
        }
      } else {
        changeCurrentStep('04', 'current')
      }
    })
    .finally(() => {
      importIsInProgress.value = false
      usingSkipFile.value = false
    })
}

async function prepareComponent() {
  await retrieveImports()

  if (route.query.import_id) {
    importingViaBatch.value = true
    continueImport(route.query.import_id)
  }
}

prepareComponent()
</script>
