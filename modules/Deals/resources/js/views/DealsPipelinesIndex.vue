<template>
  <ICard :header="$t('deals::deal.pipeline.pipelines')" no-body>
    <template #actions>
      <IButton
        :to="{ name: 'create-pipeline' }"
        icon="plus"
        :text="$t('deals::deal.pipeline.create')"
      />
    </template>

    <ITable bordered-inner="y" :responsive="false">
      <thead>
        <tr>
          <th v-t="'core::app.id'" class="text-left" width="5%" />

          <th v-t="'deals::deal.pipeline.pipeline'" class="text-left" />

          <th />
        </tr>
      </thead>

      <SortableDraggable
        v-bind="$draggable.common"
        v-model="pipelines"
        tag="tbody"
        item-key="id"
      >
        <template #item="{ element: pipeline }">
          <tr>
            <td v-text="pipeline.id" />

            <td>
              <ILink
                :to="{ name: 'edit-pipeline', params: { id: pipeline.id } }"
                class="font-medium"
                :text="pipeline.name"
              />
            </td>

            <td class="flex justify-end">
              <IMinimalDropdown>
                <IDropdownItem
                  :to="{ name: 'edit-pipeline', params: { id: pipeline.id } }"
                  :text="$t('core::app.edit')"
                />

                <IDropdownItem
                  :text="$t('core::app.delete')"
                  @click="$confirm(() => destroy(pipeline.id))"
                />
              </IMinimalDropdown>
            </td>
          </tr>
        </template>
      </SortableDraggable>

      <tbody></tbody>
    </ITable>
  </ICard>

  <RouterView />
</template>

<script setup>
import { useI18n } from 'vue-i18n'

import { usePipelines } from '../composables/usePipelines'

const { t } = useI18n()

const { orderedPipelinesForDraggable: pipelines, deletePipeline } =
  usePipelines()

async function destroy(id) {
  await deletePipeline(id)

  Innoclapps.success(t('deals::deal.pipeline.deleted'))
}
</script>
