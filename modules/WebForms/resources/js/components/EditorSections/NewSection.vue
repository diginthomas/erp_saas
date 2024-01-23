<template>
  <ICard class="border border-primary-400">
    <template #header>
      <p
        v-t="'webforms::form.sections.new'"
        class="font-semibold text-neutral-800 dark:text-neutral-200"
      />
    </template>

    <template #actions>
      <IButtonIcon icon="X" icon-class="size-4" @click="requestSectionRemove" />
    </template>

    <IFormGroup
      :label="$t('webforms::form.sections.type')"
      label-for="section_type"
    >
      <ICustomSelect
        v-model="sectionType"
        label="label"
        field-id="section_type"
        :options="sectionTypes"
        :clearable="false"
        :reduce="type => type.id"
        @option-selected="
          $event.id === 'file' ? (fieldLabel = 'Attachment') : null,
            $event.id !== 'field' ? (field = null) : ''
        "
      />
    </IFormGroup>

    <IFormGroup
      v-if="sectionType === 'message'"
      :label="$t('webforms::form.sections.message.message')"
    >
      <Editor v-model="message" :with-image="false" />
    </IFormGroup>

    <div v-else>
      <IFormGroup
        :label="$t('webforms::form.sections.field.resourceName')"
        label-for="resourceName"
      >
        <ICustomSelect
          v-model="resourceName"
          label="label"
          field-id="resourceName"
          :clearable="false"
          :options="availableResources"
          :reduce="resource => resource.id"
          @option-selected="field = null"
        />
      </IFormGroup>

      <IFormGroup
        v-if="sectionType === 'field'"
        :label="$t('core::fields.field')"
        label-for="field"
      >
        <ICustomSelect
          v-model="field"
          label="label"
          field-id="field"
          :clearable="false"
          :selectable="field => field.disabled"
          :options="availableFields"
          @option-selected="handleFieldChanged"
        />
      </IFormGroup>

      <IFormGroup
        v-show="field !== null || sectionType === 'file'"
        :label="$t('core::fields.label')"
      >
        <Editor
          v-model="fieldLabel"
          :with-image="false"
          default-tag="div"
          toolbar="bold italic underline link removeformat"
        />
      </IFormGroup>

      <IFormGroup>
        <IFormCheckbox
          v-show="field !== null || sectionType === 'file'"
          id="new-is-required"
          v-model:checked="isRequired"
          name="new-is-required"
          :disabled="fieldMustBeRequired"
          :label="$t('core::fields.is_required')"
        />

        <IFormCheckbox
          v-show="sectionType === 'file'"
          id="new-file-multiple"
          v-model:checked="fileAcceptMultiple"
          name="new-file-multiple"
          :label="$t('webforms::form.sections.file.multiple')"
        />
      </IFormGroup>
    </div>

    <div class="space-x-2 text-right">
      <IButton
        variant="white"
        :text="$t('core::app.cancel')"
        @click="requestSectionRemove"
      />

      <IButton
        :disabled="saveIsDisabled"
        :text="$t('core::app.save')"
        variant="secondary"
        @click="requestNewSection"
      />
    </div>
  </ICard>
</template>

<script setup>
import { computed, ref, toRef } from 'vue'
import { useI18n } from 'vue-i18n'

import { useFieldSection } from '../../composables/useSectionField'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps({
  index: { type: Number },
  form: { type: Object, required: true },
  section: { required: true, type: Object },
  companiesFields: { required: true },
  contactsFields: { required: true },
  dealsFields: { required: true },
  availableResources: { required: true },
})

const emit = defineEmits(['createSectionRequested', 'removeSectionRequested'])

const { t } = useI18n()

const sectionType = ref('field')
const resourceName = ref('contacts')
const fileAcceptMultiple = ref(false)
const message = ref(null)

const currentResourceFields = computed(
  () => props[resourceName.value + 'Fields']
)

const {
  field,
  availableFields,
  handleFieldChanged,
  fieldLabel,
  isRequired,
  fieldMustBeRequired,
  generateRequestAttribute,
} = useFieldSection(
  resourceName,
  currentResourceFields,
  toRef(props.form, 'sections')
)

const sectionTypes = [
  {
    id: 'field',
    label: t('webforms::form.sections.types.input_field'),
  },
  {
    id: 'message',
    label: t('webforms::form.sections.types.message'),
  },
  {
    id: 'file',
    label: t('webforms::form.sections.types.file'),
  },
]

const saveIsDisabled = computed(() => {
  if (sectionType.value === 'field') {
    return (
      fieldLabel.value === null || fieldLabel.value == '' || field.value == null
    )
  } else if (sectionType.value === 'message') {
    return message.value === null || message.value == ''
  } else if (sectionType.value === 'file') {
    return fieldLabel.value === null || fieldLabel.value == ''
  }

  return false
})

function requestNewMessageSection() {
  emit('createSectionRequested', {
    type: 'message-section',
    message: message.value,
  })
}

function requestNewFieldSection() {
  emit('createSectionRequested', {
    type: 'field-section',
    isRequired: isRequired.value,
    label: fieldLabel.value,
    resourceName: resourceName.value,
    attribute: field.value.attribute,
    requestAttribute: generateRequestAttribute(),
  })
}

function requestNewFileSection() {
  emit('createSectionRequested', {
    type: 'file-section',
    isRequired: isRequired.value,
    label: fieldLabel.value,
    resourceName: resourceName.value,
    multiple: fileAcceptMultiple.value,
    requestAttribute: generateRequestAttribute(),
  })
}

function requestNewSection() {
  if (sectionType.value === 'message') {
    requestNewMessageSection()
  } else if (sectionType.value === 'file') {
    requestNewFileSection()
  } else {
    requestNewFieldSection()
  }
}

function requestSectionRemove() {
  emit('removeSectionRequested')
}
</script>
