<template>
  <div v-show="visible" class="mx-auto max-w-3xl">
    <div class="relative">
      <ITabGroup>
        <ITabList>
          <ITab
            :title="$t('documents::document.document_details')"
            icon="AdjustmentsVertical"
          />

          <ITab
            :disabled="Object.keys(document).length === 0"
            :title="$t('documents::document.document_activity')"
            :badge="changelog.length"
            icon="DocumentText"
          />
        </ITabList>

        <ITabPanels>
          <ITabPanel>
            <IAlert
              v-if="document.status === 'accepted'"
              variant="info"
              class="mb-6"
              :text="$t('documents::document.limited_editing')"
            />

            <div
              class="mb-4 mt-3 flex items-center md:absolute md:right-1 md:top-3.5 md:my-0 md:space-x-2.5"
            >
              <div
                class="order-last ml-auto mr-0 place-self-end md:-order-none md:mr-2.5"
              >
                <slot name="actions"></slot>
              </div>

              <TextBackground
                v-if="selectedDocumentType"
                :color="selectedDocumentType.swatch_color"
                class="mr-2.5 inline-flex items-center justify-center rounded-md px-2.5 py-px text-sm/5 md:mr-0 dark:!text-white"
              >
                <Icon :icon="selectedDocumentType.icon" class="mr-1.5 size-4" />

                <span
                  class="max-w-[90px] truncate"
                  v-text="selectedDocumentType.name"
                />
              </TextBackground>

              <TextBackground
                :color="
                  document.status
                    ? statuses[document.status].color
                    : statuses.draft.color
                "
                class="mr-2.5 rounded-md px-2.5 py-px text-sm/5 md:mr-0 dark:!text-white"
              >
                {{
                  $t(
                    'documents::document.status.' +
                      (document.status ? document.status : statuses.draft.name)
                  )
                }}
              </TextBackground>
            </div>

            <slot name="top"></slot>

            <div class="sm:grid sm:grid-cols-12 sm:gap-x-4">
              <IFormGroup
                class="sm:col-span-6"
                :label="$t('brands::brand.brand')"
                label-for="brand_id"
                required
              >
                <ICustomSelect
                  v-model="selectedBrand"
                  :options="brands"
                  :clearable="false"
                  input-id="brand_id"
                  :disabled="document.status === 'accepted'"
                  label="name"
                  @update:model-value="form.brand_id = $event.id"
                />

                <IFormError :error="form.getError('brand_id')" />
              </IFormGroup>

              <IFormGroup
                :label="$t('documents::document.type.type')"
                class="sm:col-span-6"
                label-for="document_type_id"
                required
              >
                <ICustomSelect
                  v-model="selectedDocumentType"
                  :options="documentTypes"
                  :clearable="false"
                  input-id="document_type_id"
                  label="name"
                  @update:model-value="form.document_type_id = $event.id"
                />

                <IFormError :error="form.getError('document_type_id')" />
              </IFormGroup>
            </div>

            <IFormGroup
              :label="$t('documents::fields.documents.user.name')"
              label-for="user_id"
              required
            >
              <ICustomSelect
                v-model="selectedUser"
                label="name"
                input-id="user_id"
                :clearable="false"
                :options="users"
                :disabled="document.status === 'accepted'"
                @update:model-value="form.user_id = $event ? $event.id : null"
              />

              <IFormError :error="form.getError('user_id')" />
            </IFormGroup>

            <IFormGroup
              :label="$t('documents::document.title')"
              label-for="title"
              required
            >
              <IFormInput
                id="title"
                v-model="form.title"
                :disabled="document.status === 'accepted'"
              />

              <IFormError :error="form.getError('title')" />
            </IFormGroup>

            <IFormGroup
              :label="$t('core::app.locale')"
              label-for="locale"
              required
            >
              <ICustomSelect
                v-model="form.locale"
                input-id="locale"
                :clearable="false"
                :options="locales"
              >
              </ICustomSelect>

              <IFormError :error="form.getError('locale')" />
            </IFormGroup>

            <h3
              v-t="'documents::document.view_type.html_view_type'"
              class="my-2 text-sm font-medium text-neutral-800 dark:text-neutral-100"
            />

            <FormViewTypes v-model="form.view_type" />

            <IFormError :error="form.getError('view_type')" />
          </ITabPanel>

          <ITabPanel>
            <ul role="list" class="space-y-6">
              <li
                v-for="(log, idx) in changelog"
                :key="log.id"
                class="relative flex gap-x-4"
              >
                <div
                  :class="[
                    idx === changelog.length - 1 ? 'h-6' : '-bottom-6',
                    'absolute left-0 top-0 flex w-6 justify-center',
                  ]"
                >
                  <div class="w-px bg-neutral-200" />
                </div>

                <div
                  class="relative flex size-6 flex-none items-center justify-center bg-white"
                >
                  <div
                    class="h-1.5 w-1.5 rounded-full ring-1"
                    :class="
                      log.properties.type === 'success'
                        ? 'bg-success-200 ring-success-500'
                        : 'bg-neutral-100 ring-neutral-300'
                    "
                  />
                </div>

                <template v-if="log.properties.section">
                  <div
                    class="flex-auto rounded-md p-3 ring-1 ring-inset ring-neutral-200"
                  >
                    <div class="flex justify-between gap-x-4">
                      <div
                        class="py-0.5 text-sm leading-5 text-neutral-500"
                        v-text="
                          $t(log.properties.lang.key, log.properties.lang.attrs)
                        "
                      />

                      <time
                        :datetime="log.dateTime"
                        class="flex-none py-0.5 text-xs leading-5 text-neutral-500"
                        v-text="localizedDateTime(log.created_at)"
                      />
                    </div>

                    <div
                      class="mt-1.5 py-0.5 text-sm leading-5 text-neutral-500"
                    >
                      <p class="font-medium">
                        {{
                          $t(
                            log.properties.section.lang.key,
                            log.properties.section.lang.attrs || {}
                          )
                        }}
                      </p>

                      <ul class="flex-none">
                        <li
                          v-for="(data, sIdx) in log.properties.section.list"
                          :key="sIdx"
                          class="text-neutral-600 dark:text-neutral-400"
                          v-text="$t(data.lang.key, data.lang.attrs || {})"
                        />
                      </ul>
                    </div>
                  </div>
                </template>

                <template v-else>
                  <p
                    class="flex-auto py-0.5 text-sm leading-5 text-neutral-500"
                    v-text="
                      $t(log.properties.lang.key, log.properties.lang.attrs)
                    "
                  />

                  <time
                    :datetime="log.dateTime"
                    class="flex-none py-0.5 text-xs leading-5 text-neutral-500"
                    v-text="localizedDateTime(log.created_at)"
                  />
                </template>
              </li>
            </ul>
          </ITabPanel>
        </ITabPanels>
      </ITabGroup>
    </div>
  </div>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import find from 'lodash/find'

import { useApp } from '@/Core/composables/useApp'
import { useDates } from '@/Core/composables/useDates'

import { useDocumentTypes } from '../composables/useDocumentTypes'

import FormViewTypes from './DocumentFormViewTypes.vue'
import propsDefinition from './formSectionProps'

const props = defineProps(propsDefinition)

const { localizedDateTime } = useDates()
const { scriptConfig, users, currentUser, locales } = useApp()
const { typesByName: documentTypes } = useDocumentTypes()

const statuses = scriptConfig('documents.statuses')
const selectedUser = ref(null)
const selectedDocumentType = ref(null)
const selectedBrand = ref(null)

const brands = inject('brands')

const changelog = computed(() => {
  if (!props.document.changelog) {
    return []
  }

  return props.document.changelog.slice().reverse()
})

function prepareComponent() {
  if (Object.keys(props.document).length === 0) {
    selectedBrand.value = find(brands.value, brand => brand.is_default)

    if (selectedBrand.value) {
      props.form.set('brand_id', selectedBrand.value.id)
    }

    selectedDocumentType.value = find(documentTypes.value, [
      'id',
      parseInt(scriptConfig('documents.default_document_type')),
    ])

    if (selectedDocumentType.value) {
      props.form.set('document_type_id', selectedDocumentType.value.id)
    }

    selectedUser.value = currentUser.value
    props.form.set('user_id', selectedUser.value.id)
  } else {
    selectedBrand.value = props.document.brand
    selectedDocumentType.value = props.document.type
    selectedUser.value = props.document.user
  }
}

prepareComponent()
</script>
