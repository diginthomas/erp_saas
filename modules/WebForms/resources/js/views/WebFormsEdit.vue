<template>
  <div>
    <IOverlay :show="preparingComponent">
      <IModal
        id="requiresFieldsModal"
        size="sm"
        :title="$t('webforms::form.fields_action_required')"
        hide-footer
      >
        <p
          v-t="'webforms::form.required_fields_needed'"
          class="text-neutral-900 dark:text-neutral-100"
        />
      </IModal>

      <IModal
        id="nonOptionalFieldRequiredModal"
        size="sm"
        :ok-text="$t('core::app.continue')"
        :ok-disabled="
          hasContactEmailAddressField &&
          !acceptsRequiredFields.email &&
          hasContactPhoneField &&
          !acceptsRequiredFields.phones
        "
        :title="$t('webforms::form.fields_action_required')"
        @ok="acceptRequiredFields"
      >
        <p
          v-t="'webforms::form.must_requires_fields'"
          class="mb-3 text-neutral-800 dark:text-neutral-100"
        />

        <IFormCheckbox
          v-show="!contactEmailFieldIsRequired && hasContactEmailAddressField"
          id="require-contact-email"
          v-model:checked="acceptsRequiredFields.email"
          name="require-contact-email"
          :label="$t('contacts::fields.contacts.email')"
        />

        <IFormCheckbox
          v-show="!contactPhoneFieldIsRequired && hasContactPhoneField"
          id="require-contact-phone"
          v-model:checked="acceptsRequiredFields.phones"
          class="mt-2"
          name="require-contact-phone"
          :label="$t('contacts::fields.contacts.phone')"
        />
      </IModal>

      <form novalidate="true" @submit.prevent="beforeUpdateChecks">
        <ICard no-body actions-class="w-full sm:w-auto">
          <template #header>
            <div class="flex items-center">
              <ILink :to="{ name: 'web-forms-index' }" plain>
                <Icon
                  icon="ChevronLeft"
                  class="size-5 text-neutral-500 hover:text-neutral-800 dark:text-neutral-300 dark:hover:text-neutral-400"
                />
              </ILink>

              <div class="ml-3 w-full">
                <input
                  id="name"
                  v-model="form.title"
                  type="text"
                  name="name"
                  :class="[
                    'block w-full border-0 border-b-2 bg-neutral-50 text-sm font-medium focus:bg-neutral-100 focus:ring-0 dark:bg-neutral-700 dark:text-white dark:focus:bg-neutral-800',
                    form.getError('title')
                      ? 'border-danger-500 focus:border-danger-600'
                      : 'border-transparent focus:border-primary-500',
                  ]"
                />
              </div>
            </div>
          </template>

          <template #actions>
            <div
              class="mt-5 flex w-full items-center justify-end space-x-2 sm:mt-0 sm:w-auto"
            >
              <div class="flex">
                <IActionMessage
                  :show="form.recentlySuccessful"
                  :message="$t('core::app.saved')"
                  class="mr-2"
                />

                <IFormToggle
                  v-model="form.status"
                  value="active"
                  class="mr-2 border-r border-neutral-200 pr-4 dark:border-neutral-700"
                  unchecked-value="inactive"
                  :disabled="addingNewSection || form.busy"
                  :label="$t('webforms::form.active')"
                />
              </div>

              <ILink
                :text="$t('core::app.preview')"
                :href="form.public_url"
                class="btn-white btn-sm btn rounded"
                plain
              />

              <IButton
                :loading="form.busy"
                :disabled="form.busy || addingNewSection"
                :text="$t('core::app.save')"
                @click="beforeUpdateChecks"
              />
            </div>
          </template>

          <div class="form-wrapper overflow-auto">
            <div class="m-auto max-w-full">
              <ITabGroup>
                <ITabList
                  centered
                  class="sticky top-0 z-10 bg-white dark:bg-neutral-900"
                >
                  <ITab :title="$t('webforms::form.editor')" />

                  <ITab :title="$t('webforms::form.submit_options')" />

                  <ITab :title="$t('webforms::form.style.style')" />

                  <ITab :title="$t('webforms::form.sections.embed.embed')" />
                </ITabList>

                <ITabPanels>
                  <ITabPanel>
                    <div
                      v-for="(section, index) in form.sections"
                      :key="index + section.type + section.attribute"
                      class="m-auto max-w-sm"
                    >
                      <component
                        :is="sectionComponents[section.type]"
                        :form="form"
                        :companies-fields="companiesFields"
                        :contacts-fields="contactsFields"
                        :deals-fields="dealsFields"
                        :index="index"
                        :available-resources="availableResources"
                        :section="section"
                        @remove-section-requested="removeSection(index)"
                        @update-section-requested="
                          updateSectionRequestedEvent(index, $event)
                        "
                        @create-section-requested="createSection(index, $event)"
                      />

                      <div
                        v-if="totalSections - 1 != index"
                        class="group relative flex flex-col items-center justify-center"
                      >
                        <div v-show="!addingNewSection" class="absolute">
                          <IButton
                            class="block transition-opacity delay-75 md:opacity-0 md:group-hover:opacity-100"
                            icon="Plus"
                            variant="secondary"
                            @click="newSection(index)"
                          />
                        </div>

                        <svg height="56" width="360">
                          <line
                            x1="180"
                            y1="0"
                            x2="180"
                            y2="56"
                            class="stroke-current stroke-1 text-neutral-900 dark:text-neutral-700"
                          />
                          Sorry, your browser does not support inline SVG.
                        </svg>
                      </div>
                    </div>
                  </ITabPanel>

                  <ITabPanel>
                    <ICardBody>
                      <h5
                        v-t="'webforms::form.success_page.success_page'"
                        class="mb-3 text-lg font-semibold text-neutral-700 dark:text-neutral-100"
                      />

                      <IFormGroup
                        class="[&_div>label]:!text-neutral-600/90 [&_div>label]:dark:!text-neutral-400"
                        :label="
                          $t('webforms::form.success_page.success_page_info')
                        "
                      >
                        <IFormRadio
                          id="submitMessage"
                          v-model="form.submit_data.action"
                          class="mt-2"
                          :label="
                            $t('webforms::form.success_page.thank_you_message')
                          "
                          value="message"
                          name="submit-action"
                        />

                        <IFormRadio
                          id="submitRedirect"
                          v-model="form.submit_data.action"
                          class="mt-1"
                          :label="$t('webforms::form.success_page.redirect')"
                          value="redirect"
                          name="submit-action"
                        />

                        <IFormError
                          :error="form.getError('submit_data.action')"
                        />
                      </IFormGroup>

                      <div class="mb-3">
                        <div v-show="form.submit_data.action === 'message'">
                          <IFormGroup
                            :label="$t('webforms::form.success_page.title')"
                            label-for="success_title"
                            required
                          >
                            <IFormInput
                              v-model="form.submit_data.success_title"
                              :placeholder="
                                $t(
                                  'webforms::form.success_page.title_placeholder'
                                )
                              "
                            />

                            <IFormError
                              :error="
                                form.getError('submit_data.success_title')
                              "
                            />
                          </IFormGroup>

                          <IFormGroup
                            :label="$t('webforms::form.success_page.message')"
                            optional
                          >
                            <Editor
                              v-model="form.submit_data.success_message"
                              :with-image="false"
                            />
                          </IFormGroup>
                        </div>

                        <div v-show="form.submit_data.action === 'redirect'">
                          <IFormGroup
                            :label="
                              $t('webforms::form.success_page.redirect_url')
                            "
                            label-for="success_redirect_url"
                            required
                          >
                            <IFormInput
                              v-model="form.submit_data.success_redirect_url"
                              type="url"
                              :placeholder="
                                $t(
                                  'webforms::form.success_page.redirect_url_placeholder'
                                )
                              "
                            />

                            <IFormError
                              :error="
                                form.getError(
                                  'submit_data.success_redirect_url'
                                )
                              "
                            />
                          </IFormGroup>
                        </div>
                      </div>

                      <h5
                        v-t="
                          'webforms::form.saving_preferences.saving_preferences'
                        "
                        class="mb-3 mt-8 text-lg font-semibold text-neutral-700 dark:text-neutral-100"
                      />

                      <IFormGroup
                        label-for="title_prefix"
                        :label="
                          $t(
                            'webforms::form.saving_preferences.deal_title_prefix'
                          )
                        "
                        :description="
                          $t(
                            'webforms::form.saving_preferences.deal_title_prefix_info'
                          )
                        "
                        optional
                      >
                        <IFormInput
                          id="title_prefix')"
                          v-model="form.title_prefix"
                        />
                      </IFormGroup>

                      <IFormGroup
                        label-for="pipeline_id"
                        :label="$t('deals::fields.deals.pipeline.name')"
                        required
                      >
                        <ICustomSelect
                          v-model="pipeline"
                          :options="pipelines"
                          label="name"
                          input-id="pipeline_id"
                          :clearable="false"
                          @update:model-value="stage = $event.stages[0]"
                        />

                        <IFormError
                          :error="form.getError('submit_data.pipeline_id')"
                        />
                      </IFormGroup>

                      <IFormGroup
                        label-for="stage_id"
                        required
                        :label="$t('deals::fields.deals.stage.name')"
                      >
                        <ICustomSelect
                          v-model="stage"
                          :options="pipeline ? pipeline.stages : []"
                          label="name"
                          :clearable="false"
                          input-id="stage_id"
                        />

                        <IFormError
                          :error="form.getError('submit_data.stage_id')"
                        />
                      </IFormGroup>

                      <IFormGroup
                        label-for="user_id"
                        :label="$t('deals::fields.deals.user.name')"
                        required
                      >
                        <ICustomSelect
                          v-model="form.user_id"
                          :options="users"
                          :clearable="false"
                          label="name"
                          :reduce="user => user.id"
                          input-id="user_id"
                        />

                        <IFormError :error="form.getError('user_id')" />
                      </IFormGroup>

                      <IFormGroup
                        label-for="notifications"
                        :label="$t('webforms::form.notifications')"
                      >
                        <div
                          v-for="(email, index) in form.notifications"
                          :key="index"
                          class="mb-3 flex rounded-md shadow-sm"
                        >
                          <div
                            class="relative flex grow items-stretch focus-within:z-10"
                          >
                            <div
                              class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                            >
                              <Icon
                                icon="Mail"
                                class="size-5 text-neutral-500 dark:text-neutral-300"
                              />
                            </div>

                            <IFormInput
                              v-model="form.notifications[index]"
                              rounded="left"
                              class="pl-11"
                              type="email"
                              :placeholder="
                                $t(
                                  'webforms::form.notification_email_placeholder'
                                )
                              "
                            />

                            <IFormError
                              :error="form.getError('notifications.' + index)"
                            />
                          </div>

                          <IButton
                            type="button"
                            icon="X"
                            variant="white"
                            rounded="right"
                            class="-ml-px shadow-sm"
                            @click="removeNotification(index)"
                          />
                        </div>

                        <ILink
                          v-show="
                            !emptyNotificationsEmails ||
                            totalNotifications === 0
                          "
                          :text="$t('webforms::form.new_notification')"
                          @click="addNewNotification"
                        />
                      </IFormGroup>
                    </ICardBody>
                  </ITabPanel>

                  <ITabPanel>
                    <ICardBody>
                      <h5
                        v-t="'webforms::form.style.style'"
                        class="mb-3 text-lg font-semibold text-neutral-700 dark:text-neutral-100"
                      />

                      <IFormGroup
                        class="mt-3 w-full sm:w-[373px]"
                        :label="$t('core::app.locale')"
                        label-for="locale"
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

                      <IFormGroup
                        :label="$t('webforms::form.style.primary_color')"
                      >
                        <IColorSwatch
                          v-model="form.styles.primary_color"
                          :swatches="swatches"
                        />

                        <IFormError
                          :error="form.getError('styles.primary_color')"
                        />
                      </IFormGroup>

                      <IFormGroup
                        :label="$t('webforms::form.style.background_color')"
                      >
                        <IColorSwatch
                          v-model="form.styles.background_color"
                          :swatches="swatches"
                        />

                        <IFormError
                          :error="form.getError('styles.background_color')"
                        />
                      </IFormGroup>

                      <h3
                        v-t="'webforms::form.style.logo'"
                        class="mb-2 mt-4 text-sm font-medium text-neutral-800 dark:text-neutral-100"
                      />

                      <LogoType
                        v-model="form.styles.logo"
                        :background-color="form.styles.background_color"
                        :primary-color="form.styles.primary_color"
                      />
                    </ICardBody>
                  </ITabPanel>

                  <ITabPanel>
                    <ICardBody>
                      <WebFormEmbed :form="form" />
                    </ICardBody>
                  </ITabPanel>
                </ITabPanels>
              </ITabGroup>
            </div>
          </div>
        </ICard>
      </form>
    </IOverlay>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import find from 'lodash/find'
import findIndex from 'lodash/findIndex'
import get from 'lodash/get'

import { useApp } from '@/Core/composables/useApp'
import { useForm } from '@/Core/composables/useForm'
import { usePageTitle } from '@/Core/composables/usePageTitle'
import { isBlank } from '@/Core/utils'

import { usePipelines } from '@/Deals/composables/usePipelines'

import FieldSection from '../components/EditorSections/FieldSection.vue'
import FileSection from '../components/EditorSections/FileSection.vue'
import IntroductionSection from '../components/EditorSections/IntroductionSection.vue'
import MessageSection from '../components/EditorSections/MessageSection.vue'
import NewSection from '../components/EditorSections/NewSection.vue'
import SubmitButtonSection from '../components/EditorSections/SubmitButtonSection.vue'
import { useWebForms } from '../composables/useWebForms'

import WebFormEmbed from './WebFormsEditEmbed.vue'
import LogoType from './WebFormsEditLogoType.vue'

const sectionComponents = {
  'field-section': FieldSection,
  'introduction-section': IntroductionSection,
  'submit-button-section': SubmitButtonSection,
  'file-section': FileSection,
  'new-section': NewSection,
  'message-section': MessageSection,
}

const route = useRoute()
const { t } = useI18n()
const { scriptConfig, users, currentUser, locales } = useApp()
const { fetchWebForm, setWebForm, findWebForm } = useWebForms()
const { orderedPipelines: pipelines } = usePipelines()
usePageTitle(computed(() => findWebForm(route.params.id)?.title))

const swatches = scriptConfig('favourite_colors')

const acceptsRequiredFields = ref({
  email: true,
  phones: true,
})

const contactsFields = ref([])
const companiesFields = ref([])
const dealsFields = ref([])
const pipeline = ref(null)
const stage = ref(null)
const preparingComponent = ref(false)
const addingNewSection = ref(false)

const { form } = useForm({
  notifications: [],
  sections: [],
  styles: [],
  submit_data: [],
})

const availableResources = [
  {
    id: 'contacts',
    label: t('contacts::contact.contact'),
  },
  {
    id: 'companies',
    label: t('contacts::company.company'),
  },
  {
    id: 'deals',
    label: t('deals::deal.deal'),
  },
]

const totalNotifications = computed(() => form.notifications.length)

const emptyNotificationsEmails = computed(
  () => form.notifications.filter(isBlank).length > 0
)

const totalSections = computed(() => form.sections.length)

const hasContactEmailAddressField = computed(
  () =>
    find(form.sections, {
      resourceName: Innoclapps.resourceName('contacts'),
      attribute: 'email',
    }) !== undefined
)

const hasContactPhoneField = computed(
  () =>
    find(form.sections, {
      resourceName: Innoclapps.resourceName('contacts'),
      attribute: 'phones',
    }) !== undefined
)

const contactEmailFieldIsRequired = computed(() => {
  if (!hasContactEmailAddressField.value) {
    return false
  }

  return (
    find(form.sections, {
      resourceName: Innoclapps.resourceName('contacts'),
      attribute: 'email',
    }).isRequired === true
  )
})

const contactPhoneFieldIsRequired = computed(() => {
  if (!hasContactPhoneField.value) {
    return false
  }

  return (
    find(form.sections, {
      resourceName: Innoclapps.resourceName('contacts'),
      attribute: 'phones',
    }).isRequired === true
  )
})

const requiresFields = computed(
  () => !hasContactEmailAddressField.value && !hasContactPhoneField.value
)

const requiresNonOptionalFields = computed(
  () => !contactEmailFieldIsRequired.value && !contactPhoneFieldIsRequired.value
)

function updateSectionRequestedEvent(index, data) {
  updateSection(index, data, false)

  if (requiresFields.value || requiresNonOptionalFields.value) {
    beforeUpdateChecks()
  } else {
    update()
  }
}

function removeCreateSection() {
  const newSectionIndex = findIndex(form.sections, {
    type: 'new-section',
  })

  if (newSectionIndex !== -1) {
    removeSection(newSectionIndex)
  }
}

function newSection(index) {
  addingNewSection.value = true

  form.sections.splice(index + 1, 0, {
    type: 'new-section',
    label: t('webforms::form.sections.new'),
  })
}

async function removeSection(index) {
  if (form.sections[index].type === 'new-section') {
    addingNewSection.value = false
    form.sections.splice(index, 1)
  } else {
    await Innoclapps.confirm()
    form.sections.splice(index, 1)
    updateSilentlyIfPossible()
  }
}

function updateSection(index, data, forceUpdate = true) {
  form.sections[index] = Object.assign({}, form.sections[index], data)

  if (forceUpdate) {
    update(true)
  }
}

function createSection(fromIndex, data) {
  form.sections.splice(fromIndex + 1, 0, data)
  updateSilentlyIfPossible()
  removeCreateSection()
}

/**
 * Update the form if possible
 *
 * The function will check if the required fields criteria is met
 * If yes, will silently perform update, used when user is creating, updating and removed section
 * So the form is automatically saved with click on SAVE on the section button
 */
function updateSilentlyIfPossible() {
  if (!requiresFields.value && !requiresNonOptionalFields.value) {
    update(true)
  }
}

function setDefaultSectionsIfNeeded() {
  if (totalSections.value === 0) {
    form.sections.push({
      type: 'introduction-section',
      message: '',
      title: '',
    })

    form.sections.push({
      type: 'submit-button-section',
      text: t('webforms::form.sections.submit.default_text'),
    })
  }
}

function removeNotification(index) {
  form.notifications.splice(index, 1)
}

function addNewNotification() {
  form.notifications.push('')

  if (form.notifications.length === 1) {
    form.notifications[0] = currentUser.value.email
  }
}

function beforeUpdateChecks() {
  if (requiresFields.value) {
    Innoclapps.dialog().show('requiresFieldsModal')

    return
  } else if (requiresNonOptionalFields.value) {
    Innoclapps.dialog().show('nonOptionalFieldRequiredModal')

    return
  }

  update()
}

function acceptRequiredFields() {
  if (hasContactEmailAddressField.value && acceptsRequiredFields.value.email) {
    updateSection(
      findIndex(form.sections, {
        resourceName: Innoclapps.resourceName('contacts'),
        attribute: 'email',
      }),
      {
        isRequired: true,
      },
      false
    )
  }

  if (hasContactPhoneField.value && acceptsRequiredFields.value.phones) {
    updateSection(
      findIndex(form.sections, {
        resourceName: Innoclapps.resourceName('contacts'),
        attribute: 'phones',
      }),
      {
        isRequired: true,
      },
      false
    )
  }

  update()

  Innoclapps.dialog().hide('nonOptionalFieldRequiredModal')
}

function update(silent = false) {
  form.submit_data.pipeline_id = pipeline.value ? pipeline.value.id : null
  form.submit_data.stage_id = stage.value ? stage.value.id : null

  removeCreateSection()

  form
    .put(`/forms/${route.params.id}`)
    .then(webForm => {
      setWebForm(webForm.id, webForm)

      if (!silent) {
        Innoclapps.success(t('webforms::form.updated'))
      }
    })
    .catch(e => {
      if (e.isValidationError()) {
        Innoclapps.error(
          t('core::app.form_validation_failed_with_sections'),
          3000
        )
      }

      return Promise.reject(e)
    })
}

function isReadOnly(field) {
  return field.readonly || get(field, 'attributes.readonly')
}

function filterFields(fields, excludedAttributes) {
  return fields.filter(
    field =>
      field.showOnCreation === true &&
      (excludedAttributes.indexOf(field.attribute) === -1 || isReadOnly(field))
  )
}

async function getResourcesFields() {
  let { data } = await Innoclapps.request(
    '/fields/settings/bulk/create?intent=create',
    {
      params: {
        groups: ['contacts', 'companies', 'deals'],
      },
    }
  )

  contactsFields.value = filterFields(data.contacts, [
    'user_id',
    'source_id',
    'tags',
    'deals',
    'companies',
  ])

  dealsFields.value = filterFields(data.deals, [
    'user_id',
    'pipeline_id',
    'stage_id',
    'tags',
    'contacts',
    'companies',
  ])

  companiesFields.value = filterFields(data.companies, [
    'user_id',
    'parent_company_id',
    'source_id',
    'tags',
    'contacts',
    'deals',
  ])
}

function prepareComponent() {
  // We will get the fields from settings as these
  // are the fields the user is allowed to interact and use them in forms
  preparingComponent.value = true

  getResourcesFields().finally(() => {
    fetchWebForm(route.params.id).then(webForm => {
      form.clear().set(webForm)

      pipeline.value = pipelines.value.filter(
        pipeline => pipeline.id == webForm.submit_data.pipeline_id
      )[0]

      stage.value = pipeline.value.stages.filter(
        stage => stage.id == webForm.submit_data.stage_id
      )[0]

      setDefaultSectionsIfNeeded()
      preparingComponent.value = false
    })
  })
}

prepareComponent()
</script>

<style>
@media (min-width: 640px) {
  .form-wrapper {
    height: calc(100vh - (var(--navbar-height) + 220px));
  }
}
</style>
