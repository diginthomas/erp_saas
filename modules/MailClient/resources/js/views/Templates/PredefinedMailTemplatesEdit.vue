<template>
  <PredefinedMailTemplatesForm
    v-if="form.keys().length"
    v-model:is-shared="form.is_shared"
    v-model:body="form.body"
    v-model:subject="form.subject"
    v-model:name="form.name"
    :form="form"
  >
    <template #bottom>
      <div
        class="space-x-2 divide-y divide-neutral-200 border-t border-neutral-200 pt-3 text-right dark:divide-neutral-700 dark:border-neutral-700"
      >
        <IButton
          variant="white"
          :text="$t('core::app.cancel')"
          @click="$emit('cancelRequested')"
        />

        <IButton
          type="submit"
          variant="primary"
          :text="$t('core::app.save')"
          @click="update"
        />
      </div>
    </template>
  </PredefinedMailTemplatesForm>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import pick from 'lodash/pick'

import { useForm } from '@/Core/composables/useForm'

import { useMailTemplates } from '../../composables/useMailTemplates'

import PredefinedMailTemplatesForm from './PredefinedMailTemplatesForm.vue'

const props = defineProps({
  templateId: { required: true, type: Number },
})

const emit = defineEmits(['updated', 'cancelRequested'])

const { t } = useI18n()
const { setTemplate, findTemplateById } = useMailTemplates()

const { form } = useForm()

function update() {
  form.put(`/mails/templates/${props.templateId}`).then(template => {
    setTemplate(template.id, template)

    emit('updated', template)

    Innoclapps.success(t('mailclient::mail.templates.updated'))
  })
}

function prepareComponent() {
  form.set(
    pick(findTemplateById(props.templateId), [
      'subject',
      'body',
      'name',
      'is_shared',
    ])
  )
}

prepareComponent()
</script>
