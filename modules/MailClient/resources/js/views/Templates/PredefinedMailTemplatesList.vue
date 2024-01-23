<template>
  <div>
    <div v-show="!isCreatingOrEditing">
      <div class="mb-3 text-right">
        <IButton
          variant="primary"
          :text="$t('mailclient::mail.templates.create')"
          @click="initiateCreate"
        />
      </div>

      <ITable bordered bordered-inner="y">
        <thead>
          <tr>
            <th
              v-t="'mailclient.mail.templates.name'"
              class="text-left"
              width="50%"
            />

            <th v-t="'core::app.created_by'" class="text-left" />

            <th v-t="'core::app.last_modified_at'" class="text-left" />
          </tr>
        </thead>

        <tbody>
          <tr v-for="(template, index) in templatesByName" :key="template.id">
            <td width="50%">
              <div class="flex">
                <div class="grow">
                  <p
                    class="text-neutral-800 dark:text-neutral-100"
                    v-text="template.name"
                  />

                  <p class="text-sm text-neutral-600 dark:text-neutral-300">
                    {{ $t('mailclient::mail.templates.subject') }}:
                    {{ template.subject }}
                  </p>
                </div>

                <div class="flex items-center space-x-2">
                  <IButton
                    variant="primary"
                    :text="$t('mailclient::mail.templates.select')"
                    @click="handleTemplateSelected(index)"
                  />

                  <IMinimalDropdown
                    v-if="
                      template.authorizations.update ||
                      template.authorizations.delete
                    "
                  >
                    <IDropdownItem
                      v-if="template.authorizations.update"
                      :text="$t('core::app.edit')"
                      @click="initiateEdit(template.id)"
                    />

                    <IDropdownItem
                      v-if="template.authorizations.delete"
                      :text="$t('core::app.delete')"
                      @click="$confirm(() => destroy(template.id))"
                    />
                  </IMinimalDropdown>
                </div>
              </div>
            </td>

            <td>
              {{ template.user.name }}
            </td>

            <td>
              {{ localizedDateTime(template.updated_at) }}
            </td>
          </tr>

          <tr v-show="!hasTemplates" class="bg-white">
            <td :colspan="3" class="p-5 text-center">
              <div v-t="'core::table.empty'" class="text-center"></div>
            </td>
          </tr>
        </tbody>
      </ITable>
    </div>

    <PredefinedMailTemplatesCreate
      v-if="creatingTemplate"
      @cancel-requested="creatingTemplate = false"
      @created="handleTemplateCreatedEvent"
    />

    <PredefinedMailTemplatesEdit
      v-if="templateBeingUpdated"
      :template-id="templateBeingUpdated"
      @updated="handleTemplateUpdatedEvent"
      @cancel-requested="templateBeingUpdated = null"
    />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import { useDates } from '@/Core/composables/useDates'

import { useMailTemplates } from '../../composables/useMailTemplates'

import PredefinedMailTemplatesCreate from './PredefinedMailTemplatesCreate.vue'
import PredefinedMailTemplatesEdit from './PredefinedMailTemplatesEdit.vue'

const emit = defineEmits([
  'selected',
  'updated',
  'created',
  'willEdit',
  'willCreate',
])

const { t } = useI18n()
const { localizedDateTime } = useDates()
const { templatesByName, deleteTemplate } = useMailTemplates()

const templateBeingUpdated = ref(null)
const creatingTemplate = ref(false)

const hasTemplates = computed(() => templatesByName.value.length > 0)

const isCreatingOrEditing = computed(
  () => templateBeingUpdated.value || creatingTemplate.value
)

function initiateEdit(id) {
  emit('willEdit', id)
  templateBeingUpdated.value = id
}

function initiateCreate() {
  creatingTemplate.value = true
  emit('willCreate')
}

function handleTemplateCreatedEvent(template) {
  creatingTemplate.value = false
  emit('created', template)
}

function handleTemplateUpdatedEvent(template) {
  templateBeingUpdated.value = false
  emit('updated', template)
}

async function destroy(id) {
  await deleteTemplate(id)

  Innoclapps.success(t('mailclient::mail.templates.deleted'))
}

function handleTemplateSelected(index) {
  emit('selected', templatesByName.value[index])
}
</script>
