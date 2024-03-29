<template>
  <ICard class="mb-3">
    <div class="my-1 flex flex-col sm:flex-row sm:items-center">
      <IFormLabel for="purchase-key" class="mb-1 shrink-0 sm:mb-0 sm:mr-4">
        {{ $t('core::app.purchase_key') }}
      </IFormLabel>

      <div class="flex grow">
        <div class="relative grow focus-within:z-10">
          <input
            id="purchase-key"
            v-model="updateData.purchase_key"
            type="text"
            :placeholder="$t('core::app.enter_purchase_key')"
            :class="[
              'form-input rounded-r-none',
              componentReady && !hasValidPurchaseKey
                ? 'text-danger-900 placeholder-danger-400 focus:ring-danger-500 dark:placeholder-danger-300 dark:focus:ring-danger-400'
                : '',
            ]"
          />
        </div>

        <IButton
          variant="white"
          rounded="right"
          :text="$t('core::app.save')"
          class="-ml-px shadow-sm"
          @click="savePurchaseKey"
        />
      </div>
    </div>
  </ICard>

  <IOverlay :show="!passesZipRequirement && componentReady">
    <template v-if="!passesZipRequirement" #overlay>
      {{ $t('core::update.update_zip_is_required') }}
    </template>

    <ICard :header="$t('core::update.system')" class="mb-3">
      <IOverlay :show="!componentReady">
        <div v-if="updateData.is_new_version_available">
          <div
            class="flex flex-col space-y-2 sm:flex-row sm:space-x-2 sm:space-y-0"
          >
            <div
              class="flex-1 rounded bg-warning-100 p-2 px-2 py-3 text-center text-warning-700"
            >
              <h4 v-t="'core::update.installed_version'" class="font-medium" />

              <h5 v-text="updateData.installed_version"></h5>
            </div>

            <div
              class="flex-1 rounded bg-success-100 p-2 px-2 py-3 text-center text-success-700"
            >
              <h4 v-t="'core::update.latest_version'" class="font-medium" />

              <h5 v-text="updateData.latest_available_version"></h5>
            </div>
          </div>
        </div>

        <div v-else>
          <h4
            v-show="componentReady"
            class="text-center text-lg font-semibold text-neutral-800 dark:text-neutral-100"
          >
            <Icon
              icon="EmojiHappy"
              class="m-auto mb-2 size-10 text-success-500"
            />
            {{ $t('core::update.not_available') }}
          </h4>

          <p
            v-show="componentReady"
            v-t="'core::update.using_latest_version'"
            class="text-center text-sm text-neutral-600 dark:text-neutral-300"
          />
        </div>
      </IOverlay>

      <template #footer>
        <div class="flex justify-end">
          <IButton
            variant="success"
            :text="updateButtonText"
            :disabled="
              !canPerformUpdate ||
              updateInProgress ||
              patchBeingApplied !== false
            "
            @click="update"
          />
        </div>
      </template>
    </ICard>
  </IOverlay>

  <IOverlay :show="!passesZipRequirement && componentReady">
    <template v-if="!passesZipRequirement" #overlay>
      {{ $t('core::update.patch_zip_is_required') }}
    </template>

    <!-- <IFormCheckbox
      v-model:checked="form.auto_apply_patches"
      class="mb-1 mt-6"
      :label="$t('core::update.auto_apply_patches')"
      @change="submit"
    /> -->

    <ICard :header="$t('core::update.patches')" no-body>
      <IOverlay :show="!componentReady">
        <ul
          v-if="hasPatches"
          class="divide-y divide-neutral-200 dark:divide-neutral-700"
        >
          <li
            v-for="(patch, index) in sortedPatches"
            :key="patch.token"
            class="px-4 py-4 sm:px-6"
          >
            <div class="flex items-center justify-between">
              <div>
                <!-- eslint-disable -->
                <p
                  class="text-sm font-medium text-neutral-800 dark:text-neutral-100"
                  v-html="patch.description"
                />
                <!-- eslint-enable -->
                <IBadge
                  v-if="patch.isApplied"
                  wrapper-class="mr-1"
                  variant="success"
                  :text="$t('core::update.patch_applied')"
                />

                <IBadge variant="neutral" :text="patch.token" />

                <small class="ml-2.5 text-neutral-500 dark:text-neutral-300">
                  {{ localizedDateTime(patch.date) }}
                </small>

                <br />
              </div>

              <div class="flex">
                <ILink
                  :href="
                    '/patches/' + patch.token + '/' + updateData.purchase_key
                  "
                  :class="[
                    'mr-3 mt-2',
                    {
                      'pointer-events-none opacity-70':
                        index > 0 ||
                        patchBeingApplied !== false ||
                        !hasValidPurchaseKey ||
                        !passesZipRequirement ||
                        updateInProgress ||
                        patch.isApplied,
                    },
                  ]"
                >
                  <Icon icon="DocumentDownload" class="size-5" />
                </ILink>

                <span
                  v-i-tooltip="
                    index === 0 || patch.isApplied
                      ? null
                      : $t('core::update.apply_oldest_first')
                  "
                  class="inline-block"
                  tabindex="-1"
                >
                  <IButton
                    :disabled="
                      index > 0 ||
                      patchBeingApplied !== false ||
                      !hasValidPurchaseKey ||
                      !passesZipRequirement ||
                      updateInProgress ||
                      patch.isApplied
                    "
                    @click="applyPatch(patch.token, index)"
                  >
                    {{
                      patchBeingApplied === index
                        ? $t('core::update.update_in_progress')
                        : $t('core::app.apply')
                    }}
                  </IButton>
                </span>
              </div>
            </div>
          </li>
        </ul>

        <ICardBody v-else>
          <p
            v-show="componentReady"
            v-t="'core::update.no_patches'"
            class="text-center text-sm text-neutral-500 dark:text-neutral-300"
          />
        </ICardBody>
      </IOverlay>
    </ICard>
  </IOverlay>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import orderBy from 'lodash/orderBy'

import { useApp } from '@/Core/composables/useApp'
import { useDates } from '@/Core/composables/useDates'
import { isPurchaseKey } from '@/Core/utils'

import { useSettings } from '../../../composables/useSettings'

const { t } = useI18n()
const { form, submit } = useSettings()
const { localizedDateTime } = useDates()
const { scriptConfig } = useApp()

const passesZipRequirement = scriptConfig('requirements.zip')
const updateData = ref({})
const patches = ref([])
const updateInProgress = ref(false)
const patchBeingApplied = ref(false)
const componentReady = ref(false)

const sortedPatches = computed(() =>
  orderBy(
    patches.value.map(patch => {
      // For date sorting
      patch._date = new Date(patch.date)

      return patch
    }),
    ['isApplied', '_date'],
    ['asc', 'asc']
  )
)

const hasPatches = computed(() => patches.value.length > 0)

const updateButtonText = computed(() =>
  updateInProgress.value
    ? t('core::update.update_in_progress')
    : t('core::update.perform')
)

const hasValidPurchaseKey = computed(() =>
  isPurchaseKey(updateData.value.purchase_key)
)

const canPerformUpdate = computed(
  () =>
    updateData.value.is_new_version_available &&
    hasValidPurchaseKey.value &&
    passesZipRequirement
)

function savePurchaseKey() {
  form.purchase_key = updateData.value.purchase_key
  submit()
}

function handleUpdateErrorResponse(response) {
  if (response.data === 'Incorrect files permissions.') {
    window.location.href = '/errors/permissions'
  } else {
    Innoclapps.error(response.data)
  }
}

function update() {
  updateInProgress.value = true

  Innoclapps.request()
    .post(`/update/${updateData.value.purchase_key}`)
    .then(() => window.location.reload())
    .catch(({ response }) => handleUpdateErrorResponse(response))
    .finally(() => (updateInProgress.value = false))
}

function applyPatch(token, index) {
  patchBeingApplied.value = index

  Innoclapps.request()
    .post(`/patches/${token}/${updateData.value.purchase_key}`)
    .then(() => window.location.reload())
    .catch(({ response }) => handleUpdateErrorResponse(response))
    .finally(() => (patchBeingApplied.value = false))
}

function prepareComponent() {
  Promise.all([Innoclapps.request('/update'), Innoclapps.request('/patches')])
    .then(values => {
      updateData.value = values[0].data
      patches.value = values[1].data
    })
    .catch(({ response }) => handleUpdateErrorResponse(response))
    .finally(() => (componentReady.value = true))
}

prepareComponent()
</script>
