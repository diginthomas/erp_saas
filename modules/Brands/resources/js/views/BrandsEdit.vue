<template>
  <ICard
    tag="form"
    :header="$t('brands::brand.update')"
    :overlay="!componentReady"
    footer-class="text-right"
    no-body
    @submit.prevent="save"
  >
    <div v-if="componentReady">
      <ITabGroup>
        <ITabList class="px-4 sm:px-6">
          <ITab :title="$t('brands::brand.form.sections.general')" />

          <ITab :title="$t('brands::brand.form.sections.navigation')" />

          <ITab :title="$t('brands::brand.form.sections.email')" />

          <ITab :title="$t('brands::brand.form.sections.thank_you')" />

          <ITab :title="$t('brands::brand.form.sections.pdf')" />

          <ITab :title="$t('brands::brand.form.sections.signature')" />
        </ITabList>

        <ITabPanels class="px-4 sm:px-6">
          <ITabPanel>
            <IFormGroup v-if="canChangeDefaultState" class="mb-6">
              <IFormCheckbox
                id="is_default"
                v-model:checked="form.is_default"
                name="is_default"
                :label="$t('brands::brand.form.is_default')"
              />

              <IFormError :error="form.getError('is_default')" />
            </IFormGroup>

            <IFormGroup
              label-for="name"
              :label="$t('brands::brand.form.name')"
              required
            >
              <IFormInput id="name" v-model="form.name" />

              <IFormError :error="form.getError('name')" />
            </IFormGroup>

            <IFormGroup
              label-for="display_name"
              :label="$t('brands::brand.form.display_name')"
              required
            >
              <IFormInput id="display_name" v-model="form.display_name" />

              <IFormError :error="form.getError('display_name')" />
            </IFormGroup>

            <IFormGroup :label="$t('brands::brand.form.primary_color')">
              <IColorSwatch
                v-model="form.config.primary_color"
                :swatches="swatches"
              />

              <IFormError :error="form.getError('config.primary_color')" />
            </IFormGroup>

            <IFormGroup class="mt-4">
              <VisibilityGroupSelector
                v-model:type="form.visibility_group.type"
                v-model:dependsOn="form.visibility_group.depends_on"
              />
            </IFormGroup>
          </ITabPanel>

          <ITabPanel>
            <IFormGroup
              :label="$t('brands::brand.form.upload_logo')"
              class="mb-5 max-w-xl"
            >
              <CropsAndUploadsImage
                name="logo_view"
                :upload-url="`${$scriptConfig('apiURL')}/brands/${
                  brand.id
                }/logo/view`"
                :image="brand.logo_view_url"
                :show-delete="Boolean(brand.logo_view)"
                :cropper-options="{ aspectRatio: null }"
                :choose-text="
                  !form.logo_view
                    ? $t('core::settings.choose_logo')
                    : $t('core::app.change')
                "
                @cleared="deleteLogo('view')"
                @success="logoUploadedHandler($event, 'view')"
              >
                <template #image="{ src }">
                  <img :src="src" class="h-8 w-auto" />
                </template>
              </CropsAndUploadsImage>

              <p
                v-t="'brands::brand.form.navigation.upload_logo_info'"
                class="mt-4 text-sm text-neutral-500 dark:text-neutral-300"
              />
            </IFormGroup>

            <IFormGroup
              :label="$t('brands::brand.form.navigation.background_color')"
            >
              <IColorSwatch
                v-model="form.config.navigation.background_color"
                :swatches="swatches"
              />

              <IFormError
                :error="form.getError('config.navigation.background_color')"
              />
            </IFormGroup>
          </ITabPanel>

          <ITabPanel>
            <IFormGroup
              :label="$t('brands::brand.form.upload_logo')"
              class="mb-5 max-w-xl"
            >
              <CropsAndUploadsImage
                name="logo_mail"
                :upload-url="`${$scriptConfig('apiURL')}/brands/${
                  brand.id
                }/logo/mail`"
                :image="brand.logo_mail_url"
                :show-delete="Boolean(brand.logo_mail)"
                :cropper-options="{ aspectRatio: null }"
                :choose-text="
                  !form.logo_mail
                    ? $t('core::settings.choose_logo')
                    : $t('core::app.change')
                "
                @cleared="deleteLogo('mail')"
                @success="logoUploadedHandler($event, 'mail')"
              >
                <template #image="{ src }">
                  <img :src="src" class="h-8 w-auto" />
                </template>
              </CropsAndUploadsImage>

              <p
                v-t="'brands::brand.form.email.upload_logo_info'"
                class="mt-4 text-sm text-neutral-500 dark:text-neutral-300"
              />
            </IFormGroup>

            <h3
              v-t="'brands::brand.form.document.send.info'"
              class="mb-4 text-lg font-medium"
            />

            <IFormGroup
              :label="$t('brands::brand.form.document.send.subject')"
              label-for="document_mail_subject"
            >
              <template #label="{ label, labelFor }">
                <TranslateableLabel
                  v-model:locale="selectedLocales.documentSendMailSubject"
                  :label="label"
                  :label-for="labelFor"
                />
              </template>

              <IFormInput
                id="document_mail_subject"
                v-model="
                  form.config.document.mail_subject[
                    selectedLocales.documentSendMailSubject
                  ]
                "
              />

              <IFormError
                :error="form.getError('config.document.mail_subject')"
              />
            </IFormGroup>

            <IFormGroup :label="$t('brands::brand.form.document.send.message')">
              <template #label="{ label, labelFor }">
                <TranslateableLabel
                  v-model:locale="selectedLocales.documentSendMailMessage"
                  :label="label"
                  :label-for="labelFor"
                />
              </template>

              <Editor
                v-model="
                  form.config.document.mail_message[
                    selectedLocales.documentSendMailMessage
                  ]
                "
                :with-image="false"
              />

              <IFormError
                :error="form.getError('config.document.mail_message')"
              />
            </IFormGroup>

            <IFormGroup
              :label="$t('brands::brand.form.document.send.button_text')"
              label-for="document_mail_button_text"
            >
              <template #label="{ label, labelFor }">
                <TranslateableLabel
                  v-model:locale="selectedLocales.documentSendMailButton"
                  :label="label"
                  :label-for="labelFor"
                />
              </template>

              <IFormInput
                id="document_mail_button_text"
                v-model="
                  form.config.document.mail_button_text[
                    selectedLocales.documentSendMailButton
                  ]
                "
              />

              <IFormError
                :error="form.getError('config.document.mail_button_text')"
              />
            </IFormGroup>

            <h3
              v-t="'brands::brand.form.document.sign.info'"
              class="mb-4 mt-6 text-lg font-medium"
            />

            <IFormGroup
              :label="$t('brands::brand.form.document.sign.subject')"
              label-for="signed_mail_subject"
            >
              <template #label="{ label, labelFor }">
                <TranslateableLabel
                  v-model:locale="selectedLocales.documentSignedMailSubject"
                  :label="label"
                  :label-for="labelFor"
                />
              </template>

              <IFormInput
                id="signed_mail_subject"
                v-model="
                  form.config.document.signed_mail_subject[
                    selectedLocales.documentSignedMailSubject
                  ]
                "
              />

              <IFormError
                :error="form.getError('config.document.signed_mail_subject')"
              />
            </IFormGroup>

            <IFormGroup :label="$t('brands::brand.form.document.sign.message')">
              <template #label="{ label, labelFor }">
                <TranslateableLabel
                  v-model:locale="selectedLocales.documentSignedMailMessage"
                  :label="label"
                  :label-for="labelFor"
                />
              </template>

              <Editor
                v-model="
                  form.config.document.signed_mail_message[
                    selectedLocales.documentSignedMailMessage
                  ]
                "
                :with-image="false"
              />

              <IFormError
                :error="form.getError('config.document.signed_mail_message')"
              />
            </IFormGroup>
          </ITabPanel>

          <ITabPanel>
            <IFormGroup
              :label="$t('brands::brand.form.document.sign.after_sign_message')"
            >
              <template #label="{ label, labelFor }">
                <TranslateableLabel
                  v-model:locale="selectedLocales.documentSignedThankyouMessage"
                  :label="label"
                  :label-for="labelFor"
                />
              </template>

              <Editor
                v-model="
                  form.config.document.signed_thankyou_message[
                    selectedLocales.documentSignedThankyouMessage
                  ]
                "
                :with-image="false"
              />

              <IFormError
                :error="
                  form.getError('config.document.signed_thankyou_message')
                "
              />
            </IFormGroup>

            <IFormGroup
              :label="
                $t('brands::brand.form.document.accept.after_accept_message')
              "
            >
              <template #label="{ label, labelFor }">
                <TranslateableLabel
                  v-model:locale="
                    selectedLocales.documentAcceptedThankyouMessage
                  "
                  :label="label"
                  :label-for="labelFor"
                />
              </template>

              <Editor
                v-model="
                  form.config.document.accepted_thankyou_message[
                    selectedLocales.documentAcceptedThankyouMessage
                  ]
                "
                :with-image="false"
              />

              <IFormError
                :error="
                  form.getError('config.document.accepted_thankyou_message')
                "
              />
            </IFormGroup>
          </ITabPanel>

          <ITabPanel>
            <IFormGroup
              :label="$t('brands::brand.form.pdf.default_font')"
              :description="
                $t('brands::brand.form.pdf.default_font_info', {
                  fontName: 'DejaVu Sans',
                })
              "
              label-for="pdf-font"
              required
            >
              <ICustomSelect
                v-model="form.config.pdf.font"
                :clearable="false"
                input-id="pdf-font"
                :options="fontNames"
              />

              <IFormError :error="form.getError('config.pdf.font')" />
            </IFormGroup>

            <IFormGroup
              :label="$t('brands::brand.form.pdf.size')"
              label-for="pdf-size"
              required
            >
              <ICustomSelect
                v-model="form.config.pdf.size"
                :clearable="false"
                input-id="pdf-size"
                :options="['a4', 'letter']"
              />

              <IFormError :error="form.getError('config.pdf.size')" />
            </IFormGroup>

            <IFormGroup
              :label="$t('brands::brand.form.pdf.orientation')"
              label-for="pdf-orientation"
              required
            >
              <ICustomSelect
                v-model="form.config.pdf.orientation"
                :clearable="false"
                input-id="pdf-orientation"
                :options="['portrait', 'landscape']"
              >
                <template #label="{ option }">
                  {{ $t('brands::brand.form.pdf.orientation_' + option) }}
                </template>

                <template #option="option">
                  {{ $t('brands::brand.form.pdf.orientation_' + option.label) }}
                </template>
              </ICustomSelect>

              <IFormError :error="form.getError('config.pdf.orientation')" />
            </IFormGroup>
          </ITabPanel>

          <ITabPanel>
            <IFormGroup
              :label="$t('brands::brand.form.signature.bound_text')"
              label-for="bound_text"
              required
            >
              <template #label="{ label, labelFor }">
                <TranslateableLabel
                  v-model:locale="selectedLocales.signatureBoundText"
                  :label="label"
                  :label-for="labelFor"
                />
              </template>

              <IFormTextarea
                id="bound_text"
                v-model="
                  form.config.signature.bound_text[
                    selectedLocales.signatureBoundText
                  ]
                "
              />

              <IFormError
                :error="form.getError('config.signature.bound_text')"
              />
            </IFormGroup>
          </ITabPanel>
        </ITabPanels>
      </ITabGroup>
    </div>

    <template #footer>
      <IButton
        type="submit"
        :disabled="form.busy"
        :text="$t('core::app.save')"
      />
    </template>
  </ICard>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'

import VisibilityGroupSelector from '@/Core/components/VisibilityGroupSelector.vue'
import { useApp } from '@/Core/composables/useApp'
import { useForm } from '@/Core/composables/useForm'

import TranslateableLabel from '../components/TranslateableLabel.vue'
import { useBrands } from '../composables/useBrands'

const { t } = useI18n()
const route = useRoute()
const { scriptConfig, currentUser } = useApp()
const { brands, setBrand, fetchBrand, patchBrand } = useBrands()

const componentReady = ref(false)
const brand = ref(null)

const swatches = scriptConfig('favourite_colors')
const fonts = scriptConfig('contentbuilder.fonts')

const selectedLocales = reactive({
  documentSendMailSubject: currentUser.value.locale,
  documentSendMailMessage: currentUser.value.locale,
  documentSendMailButton: currentUser.value.locale,
  documentSignedMailSubject: currentUser.value.locale,
  documentSignedMailMessage: currentUser.value.locale,
  documentSignedThankyouMessage: currentUser.value.locale,
  documentAcceptedThankyouMessage: currentUser.value.locale,
  signatureBoundText: currentUser.value.locale,
})

const { form } = useForm({
  visibility_group: {
    type: 'all',
    depends_on: [],
  },
})

const fontNames = computed(() => fonts.map(font => font['font-family']))

const canChangeDefaultState = computed(() => {
  // Allow to set as default on the last dashboard which is not default
  if (brands.value.length === 1 && brand.value.is_default === false) {
    return true
  }

  return brands.value.length > 1 && brand.value.is_default === false
})

function prepareComponent(id) {
  fetchBrand(id, {
    params: {
      with: 'visibilityGroup.users;visibilityGroup.teams',
    },
  })
    .then(data => {
      let braindBeingEdited = data

      form.set({
        name: braindBeingEdited.name,
        display_name: braindBeingEdited.display_name,
        config: braindBeingEdited.config,
        is_default: braindBeingEdited.is_default,
      })

      if (braindBeingEdited.visibility_group) {
        form.set('visibility_group', braindBeingEdited.visibility_group)
      }

      brand.value = braindBeingEdited
    })
    .finally(() => (componentReady.value = true))
}

function save() {
  form
    .put(`/brands/${brand.value.id}`, {
      params: {
        with: 'visibilityGroup.users;visibilityGroup.teams',
      },
    })
    .then(updatedBrand => {
      brand.value = updatedBrand
      setBrand(updatedBrand.id, updatedBrand)
      Innoclapps.success(t('brands::brand.updated'))
    })
    .catch(e => {
      if (e.isValidationError()) {
        Innoclapps.error(
          t('core::app.form_validation_failed_with_sections'),
          3000
        )
      }
    })
}

function deleteLogo(type) {
  Innoclapps.request()
    .delete(`/brands/${brand.value.id}/logo/${type}`)
    .then(() => {
      updateCurrentBrandLogo(type, null)
    })
}

function logoUploadedHandler(response, type) {
  // TODO
  // Not reactive, vuejs cannot detect the update for some reason?
  // But that's okay as the user can re-upload new logo if needed without deleting the current
  updateCurrentBrandLogo(type, response.path)
}

function updateCurrentBrandLogo(type, value) {
  brand.value['logo_' + type] = value
  brand.value['logo_' + type + '_url'] = value

  patchBrand(brand.value.id, {
    ['logo_' + type]: value,
    ['logo_' + type + '_url']: value,
  })
}

prepareComponent(route.params.id)
</script>
