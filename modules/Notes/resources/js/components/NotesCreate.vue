<template>
  <ICard tag="form" method="POST" @submit.prevent="create">
    <Editor
      ref="editorRef"
      v-model="form.body"
      :placeholder="$t('notes::note.write')"
      :with-mention="true"
      @init="() => $refs.editorRef.focus()"
    />

    <IFormError :error="form.getError('body')" />

    <template #footer>
      <div class="flex flex-col sm:flex-row sm:items-center">
        <CreateFollowUpTask
          ref="createFollowUpTaskRef"
          v-model="form.task_date"
          class="grow"
        />

        <div class="mt-2 space-y-2 sm:mt-0 sm:space-x-2 sm:space-y-0">
          <IButton
            class="w-full sm:w-auto"
            variant="white"
            :text="$t('core::app.cancel')"
            @click="$emit('cancel')"
          />

          <IButton
            class="w-full sm:w-auto"
            :text="$t('notes::note.add')"
            :disabled="form.busy"
            @click="create"
          />
        </div>
      </div>
    </template>
  </ICard>
</template>

<script setup>
import { inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import { useForm } from '@/Core/composables/useForm'
import { useResourceable } from '@/Core/composables/useResourceable'

import CreateFollowUpTask from '@/Activities/components/CreateFollowUpTask.vue'
import { useActivities } from '@/Activities/composables/useActivities'

const props = defineProps({
  viaResource: { required: true, type: String },
  viaResourceId: { required: true, type: [String, Number] },
  relatedResourceDisplayName: { required: true, type: String },
})

defineEmits(['cancel'])

const synchronizeResource = inject('synchronizeResource')
const incrementResourceCount = inject('incrementResourceCount')

const { t } = useI18n()

const { createFollowUpActivity } = useActivities()
const { createResource } = useResourceable(Innoclapps.resourceName('notes'))

const { form } = useForm({
  body: '',
  task_date: null,
})

const createFollowUpTaskRef = ref(null)

async function handleNoteCreated(note) {
  if (form.task_date) {
    let activity = await createFollowUpActivity(
      form.task_date,
      props.viaResource,
      props.viaResourceId,
      props.relatedResourceDisplayName,
      {
        note: t('notes::note.follow_up_task_body', {
          content: note.body,
        }),
      }
    )

    createFollowUpTaskRef.value.reset()

    if (activity) {
      synchronizeResource({ activities: [activity] })
      incrementResourceCount('incomplete_activities_for_user_count')
    }
  }

  synchronizeResource({ notes: [note] })
  incrementResourceCount('notes_count')

  Innoclapps.success(t('notes::note.created'))
  form.reset()
}

function create() {
  createResource(
    form.set(props.viaResource, [props.viaResourceId]).withQueryString({
      via_resource: props.viaResource,
      via_resource_id: props.viaResourceId,
    })
  ).then(handleNoteCreated)
}
</script>
