<template>
  <div v-show="visible" class="relative">
    <IButtonIcon
      icon="X"
      class="absolute right-4 top-5 hidden sm:block"
      @click="$emit('update:visible', false)"
    />

    <ITabGroup>
      <ITabList>
        <ITab
          v-for="(group, groupName) in placeholders"
          :key="groupName"
          :title="group.label"
        />
      </ITabList>

      <ITabPanels>
        <ITabPanel v-for="(group, groupName) in placeholders" :key="groupName">
          <PlaceholdersList
            :placeholders="group.placeholders"
            @insert-requested="insertPlaceholder($event, group.label)"
          >
            <IButtonIcon
              icon="X"
              class="text-right sm:hidden"
              @click="$emit('update:visible', false)"
            />
          </PlaceholdersList>
        </ITabPanel>
      </ITabPanels>
    </ITabGroup>
  </div>
</template>

<script setup>
import { useTimeoutFn } from '@vueuse/core'

import PlaceholdersList from './MailEditorPlacehodersList.vue'

defineProps({
  placeholders: { type: Object },
  visible: { type: Boolean, default: true },
})

const emit = defineEmits(['update:visible', 'inserted'])

function insertPlaceholder(placeholder, groupLabel) {
  let html = ''

  if (!placeholder.newlineable) {
    html = `<input type="text"
                        class="_placeholder"
                        data-tag="${placeholder.tag}"
                        placeholder="${placeholder.description} (${groupLabel})" /> `
  } else {
    html = `<textarea class="_placeholder"
                        rows="1"
                        data-tag="${placeholder.tag}"
                        placeholder="${placeholder.description} (${groupLabel})"></textarea> `
  }

  tinymce.activeEditor.execCommand('mceInsertContent', false, html)

  // Wait till the editor content is updated
  useTimeoutFn(() => emit('inserted'), 500)
}
</script>
