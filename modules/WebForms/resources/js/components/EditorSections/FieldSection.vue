<template>
  <ICard
    class="group"
    :class="{
      'border border-primary-400': editing,
      'border border-danger-500': !originalField,
      'border border-transparent transition duration-75 hover:border-primary-400 dark:border dark:border-neutral-700':
        !editing && originalField,
    }"
  >
    <template #header>
      <p
        :class="[
          'font-semibold',
          !originalField
            ? 'text-danger-500'
            : 'text-neutral-800 dark:text-neutral-200',
        ]"
        v-text="sectionHeading"
      />
    </template>

    <template #actions>
      <div class="inline-flex space-x-2">
        <IButtonIcon
          v-show="canEditSection"
          icon="PencilAlt"
          class="block md:hidden md:group-hover:block"
          icon-class="size-4"
          @click="setEditingMode"
        />

        <IButtonIcon
          icon="Trash"
          class="block md:hidden md:group-hover:block"
          icon-class="size-4"
          @click="requestSectionRemove"
        />
      </div>
    </template>

    <div
      v-show="!editing"
      class="text-sm text-neutral-900 dark:text-neutral-300"
    >
      <!-- eslint-disable-next-line vue/no-v-html -->
      <p v-html="section.label"></p>
    </div>

    <div v-if="editing">
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

      <IFormGroup :label="$t('core::fields.field')" label-for="field">
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

      <IFormGroup v-show="field !== null" :label="$t('core::fields.label')">
        <Editor
          v-model="fieldLabel"
          :with-image="false"
          default-tag="div"
          toolbar="bold italic underline link removeformat"
        />
      </IFormGroup>

      <div class="text-right">
        <div class="flex items-center justify-between">
          <div>
            <IFormCheckbox
              v-show="field !== null"
              id="is-required"
              v-model:checked="isRequired"
              name="is-required"
              :disabled="fieldMustBeRequired"
              :label="$t('core::fields.is_required')"
            />
          </div>

          <div class="space-x-2">
            <IButton
              variant="white"
              :text="$t('core::app.cancel')"
              @click="editing = false"
            />

            <IButton
              :disabled="saveIsDisabled"
              :text="$t('core::app.save')"
              variant="secondary"
              @click="requestSectionSave"
            />
          </div>
        </div>
      </div>
    </div>
  </ICard>
</template>

<script setup>
import { computed, ref, toRef } from 'vue'
import { useI18n } from 'vue-i18n'
import find from 'lodash/find'

import { useFieldSection } from '../../composables/useSectionField'

const props = defineProps({
  index: { type: Number },
  form: { type: Object, required: true },
  section: { required: true, type: Object },
  companiesFields: { required: true },
  contactsFields: { required: true },
  dealsFields: { required: true },
  availableResources: { required: true },
})

const emit = defineEmits(['updateSectionRequested', 'removeSectionRequested'])

const { t } = useI18n()

const editing = ref(false)
const resourceName = ref(props.section.resourceName)

const currentResourceFields = computed(
  () => props[resourceName.value + 'Fields']
)

/**
 * Original field before edit
 */
const originalField = find(currentResourceFields.value, {
  attribute: props.section.attribute,
})

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

/**
 * Indicates whether the user can edit the section
 * Returns false if the field is deleted as well, when no original field found
 */
const canEditSection = computed(() => !editing.value && originalField)

const resourceSingularLabel = computed(() => {
  return find(props.availableResources, {
    id: props.section.resourceName,
  }).label
})

const sectionHeading = computed(() => {
  if (!originalField) {
    return t('core::fields.no_longer_available')
  }

  return (
    resourceSingularLabel.value +
    ' | ' +
    originalField.label +
    (!props.section.isRequired ? ' ' + t('core::fields.optional') : '')
  )
})

const saveIsDisabled = computed(
  () =>
    fieldLabel.value === null || fieldLabel.value == '' || field.value == null
)

function requestSectionSave() {
  let data = {
    isRequired: isRequired.value,
    label: fieldLabel.value,
    resourceName: resourceName.value,
    attribute: field.value.attribute,
  }

  // Field changed, re-generate request attribute data
  if (
    !originalField ||
    resourceName.value != props.section.resourceName ||
    field.value.attribute != originalField.attribute
  ) {
    data.requestAttribute = generateRequestAttribute()
  }

  emit('updateSectionRequested', data)

  editing.value = false
}

function setEditingMode() {
  field.value = originalField
  resourceName.value = props.section.resourceName
  fieldLabel.value = props.section.label
  isRequired.value = props.section.isRequired

  editing.value = true
}

function requestSectionRemove() {
  emit('removeSectionRequested')
}
</script>
