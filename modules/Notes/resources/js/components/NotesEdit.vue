<template>
  <ICard footer-class="text-right space-x-2">
    <Editor v-model="form.body" :with-mention="true" />

    <IFormError :error="form.getError('body')" />

    <template #footer>
      <IButton
        variant="white"
        :text="$t('core::app.cancel')"
        @click="$emit('cancelled')"
      />

      <IButton
        variant="primary"
        :text="$t('core::app.save')"
        :disabled="form.busy"
        @click="update"
      />
    </template>
  </ICard>
</template>

<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'

import { useForm } from '@/Core/composables/useForm'
import { useResourceable } from '@/Core/composables/useResourceable'

const props = defineProps({
  noteId: { required: true, type: Number },
  body: { required: true, type: String },
  viaResource: { required: true, type: String },
  viaResourceId: { required: true, type: [String, Number] },
})

const emit = defineEmits(['updated', 'cancelled'])

const synchronizeResource = inject('synchronizeResource')

const { t } = useI18n()
const { updateResource } = useResourceable(Innoclapps.resourceName('notes'))
const { form } = useForm({ body: props.body })

async function update() {
  let updatedNote = await updateResource(
    form.withQueryString({
      via_resource: props.viaResource,
      via_resource_id: props.viaResourceId,
    }),
    props.noteId
  )

  synchronizeResource({ notes: updatedNote })

  emit('updated', updatedNote)

  Innoclapps.success(t('notes::note.updated'))
}
</script>
