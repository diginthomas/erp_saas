<template>
  <div v-if="show">
    <div class="mb-4">
      <p
        v-t="'core::app.record_view.sections.edit_heading'"
        class="font-medium text-neutral-700 dark:text-white"
      />

      <p
        v-t="'core::app.record_view.sections.edit_subheading'"
        class="text-sm text-neutral-500 dark:text-neutral-300"
      />
    </div>

    <SortableDraggable
      :model-value="sections"
      item-key="id"
      class="space-y-2"
      handle=".section-reorder-handle"
      v-bind="$draggable.common"
      @update:model-value="$emit('update:sections', $event)"
    >
      <template #item="{ element }">
        <div
          class="flex items-center rounded-md border border-neutral-200 bg-white px-4 py-3 dark:border-neutral-700 dark:bg-neutral-900"
        >
          <div class="grow">
            <IFormCheckbox
              :id="'section-' + element.id"
              v-model:checked="checked[element.id]"
              :name="'section-' + element.id"
            >
              {{ element.heading || element.id }}
            </IFormCheckbox>
          </div>

          <IButtonIcon
            icon="Selector"
            class="section-reorder-handle cursor-move"
          />
        </div>
      </template>
    </SortableDraggable>

    <div class="mt-3 flex items-center justify-end space-x-2">
      <IButton
        variant="white"
        :text="$t('core::app.cancel')"
        @click="$emit('update:show', false)"
      />

      <IButton
        variant="primary"
        :disabled="sectionsAreBeingSaved"
        :loading="sectionsAreBeingSaved"
        :text="$t('core::app.save')"
        @click="save"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  sections: { type: Array, required: true },
  show: Boolean,
  identifier: { type: String, required: true },
})

const emit = defineEmits(['saved', 'update:show', 'update:sections'])

const checked = ref({})

props.sections.forEach(section => {
  checked.value[section.id] = section.enabled
})

const sectionsAreBeingSaved = ref(false)

function save() {
  sectionsAreBeingSaved.value = true

  Innoclapps.request()
    .post('/settings', {
      [props.identifier + '_view_sections']: props.sections.map(
        (section, index) => ({
          id: section.id,
          order: index + 1,
          enabled: checked.value[section.id],
        })
      ),
    })
    .then(() => {
      emit('saved')

      const newValue = props.sections.map((section, index) =>
        Object.assign({}, section, {
          order: index + 1,
          enabled: checked.value[section.id],
        })
      )

      emit('update:sections', newValue)
    })
    .finally(() => (sectionsAreBeingSaved.value = false))
}
</script>
