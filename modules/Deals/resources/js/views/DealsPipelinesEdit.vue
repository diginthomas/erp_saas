<template>
  <ICard
    no-body
    :header="$t('deals::deal.pipeline.edit')"
    footer-class="text-right"
    :overlay="!componentReady"
  >
    <form @submit.prevent="update">
      <ICardBody>
        <IFormGroup
          label-for="name"
          :label="$t('deals::deal.pipeline.name')"
          required
        >
          <IFormInput id="name" v-model="form.name" name="name" type="text" />

          <IFormError :error="form.getError('name')" />
        </IFormGroup>

        <IFormGroup class="mt-4">
          <VisibilityGroupSelector
            v-model:type="form.visibility_group.type"
            v-model:dependsOn="form.visibility_group.depends_on"
            :disabled="pipeline.is_primary"
          />
        </IFormGroup>

        <IAlert
          variant="info"
          :show="componentReady && pipeline.is_primary"
          class="mt-4"
          :text="
            $t('deals::deal.pipeline.visibility_group.primary_restrictions')
          "
        />
      </ICardBody>

      <ITable bordered="y">
        <thead>
          <tr>
            <th v-t="'deals::deal.stage.name'" class="text-left" />

            <th v-t="'deals::deal.stage.win_probability'" class="text-left" />
          </tr>
        </thead>

        <SortableDraggable
          v-model="form.stages"
          tag="tbody"
          :item-key="(item, index) => index"
          v-bind="$draggable.common"
          handle=".draggable-handle"
        >
          <template #item="{ element, index }">
            <tr>
              <td class="w-full sm:w-auto">
                <div class="flex rounded-md shadow-sm">
                  <div
                    class="relative flex grow items-stretch focus-within:z-10"
                  >
                    <div
                      class="absolute inset-y-0 left-0 flex items-center pl-3"
                    >
                      <IButtonIcon
                        icon="Selector"
                        class="draggable-handle cursor-move"
                      />
                    </div>

                    <div
                      v-if="element.id"
                      class="absolute inset-y-0 left-11 hidden w-14 truncate px-1 sm:flex sm:items-center sm:justify-center"
                    >
                      ID: {{ element.id }}
                    </div>

                    <IFormInput
                      ref="stageNameInputRef"
                      v-model="form.stages[index].name"
                      rounded="left"
                      :class="[element.id ? 'pl-10 sm:pl-[6.7rem]' : 'pl-10']"
                      @keydown.enter="newStage"
                    />
                  </div>

                  <IButton
                    variant="white"
                    :text="$t('core::app.delete')"
                    rounded="right"
                    class="-ml-px shadow-sm"
                    @click="deleteStage(index)"
                  />
                </div>

                <IFormError
                  :error="form.getError('stages.' + index + '.name')"
                />
              </td>

              <td>
                <div class="mt-sm-0 mt-4 flex items-center">
                  <div class="mr-4 grow">
                    <input
                      v-model="form.stages[index].win_probability"
                      type="range"
                      class="h-2 w-full appearance-none rounded-md border border-neutral-200 bg-neutral-200 dark:border-neutral-500 dark:bg-neutral-700"
                      :min="1"
                      :max="100"
                    />
                  </div>

                  <div>
                    {{ form.stages[index].win_probability }}
                  </div>
                </div>

                <IFormError
                  :error="form.getError('stages.' + index + '.win_probability')"
                />
              </td>
            </tr>
          </template>
        </SortableDraggable>

        <tfoot>
          <tr>
            <td colspan="2" class="px-6 py-3">
              <IButtonMinimal
                variant="primary"
                :text="$t('deals::deal.stage.add')"
                @click="newStage"
              />
            </td>
          </tr>
        </tfoot>
      </ITable>
    </form>

    <template #footer>
      <IButton
        type="button"
        :disabled="form.busy"
        :text="$t('core::app.save')"
        @click="update"
      />
    </template>
  </ICard>
</template>

<script setup>
import { nextTick, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import map from 'lodash/map'

import VisibilityGroupSelector from '@/Core/components/VisibilityGroupSelector.vue'
import { useApp } from '@/Core/composables/useApp'
import { useForm } from '@/Core/composables/useForm'
import { useResourceable } from '@/Core/composables/useResourceable'

import { usePipelines } from '../composables/usePipelines'

const { t } = useI18n()
const route = useRoute()
const { resetStoreState } = useApp()
const { setPipeline, fetchPipeline } = usePipelines()
const { updateResource } = useResourceable('pipelines')

const stageNameInputRef = ref(null)

const pipeline = ref({})
const componentReady = ref(false)

const { form } = useForm({
  name: null,
  stages: [],
  visibility_group: {
    type: 'all',
    depends_on: [],
  },
})

async function update() {
  form.stages = map(form.stages, (stage, index) => {
    stage.display_order = index

    return stage
  })

  const pipeline = await updateResource(form, route.params.id, {
    params: {
      with: 'visibilityGroup.users;visibilityGroup.teams',
    },
  })

  setPipeline(pipeline.id, pipeline)
  resetStoreState()
  // Update the stages in case new stages are created so we can have the ID's
  form.stages = pipeline.stages

  Innoclapps.success(t('deals::deal.pipeline.updated'))
}

function newStage() {
  form.stages.push({
    name: '',
    win_probability: 100,
  })

  nextTick(() => {
    stageNameInputRef.value.focus()
  })
}

function removeStageFromForm(index) {
  form.stages.splice(index, 1)
}

async function deleteStage(index) {
  let stageId = form.stages[index].id

  // Form not yet saved, e.q. user added new stage then want to
  // delete before saving the form
  if (!stageId) {
    removeStageFromForm(index)

    return
  }

  await Innoclapps.confirm()
  await Innoclapps.request().delete(`/pipeline-stages/${stageId}`)

  resetStoreState()
  removeStageFromForm(index)
}

async function prepareComponent() {
  try {
    const response = await fetchPipeline(route.params.id, {
      params: {
        with: 'visibilityGroup.users;visibilityGroup.teams',
      },
    })

    pipeline.value = response

    form.fill('name', response.name)
    form.fill('stages', response.stages)

    if (response.visibility_group) {
      form.fill('visibility_group', response.visibility_group)
    }

    if (form.stages.length === 0) {
      newStage()
    }
  } finally {
    componentReady.value = true
  }
}

prepareComponent()
</script>
