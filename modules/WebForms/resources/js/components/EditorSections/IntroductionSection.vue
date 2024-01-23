<template>
  <ICard
    class="group"
    :class="{
      'border border-primary-400': editing,
      'border border-transparent transition duration-75 hover:border-primary-400 dark:border dark:border-neutral-700':
        !editing,
    }"
  >
    <template #header>
      <p
        v-t="'webforms::form.sections.introduction.introduction'"
        class="font-semibold text-neutral-800 dark:text-neutral-200"
      />
    </template>

    <template #actions>
      <IButtonIcon
        v-show="!editing"
        icon="PencilAlt"
        class="block md:hidden md:group-hover:block"
        icon-class="size-4"
        @click="setEditingMode"
      />
    </template>

    <div v-show="!editing">
      <h4
        class="text-lg font-medium text-neutral-800 dark:text-neutral-100"
        v-text="section.title"
      />
      <!-- eslint-disable -->
      <p
        class="text-sm text-neutral-600 dark:text-neutral-300"
        v-html="section.message"
      />
       <!-- eslint-enable -->
    </div>

    <div v-if="editing">
      <IFormGroup
        :label="$t('webforms::form.sections.introduction.title')"
        label-for="title"
      >
        <IFormInput id="title" v-model="title" />
      </IFormGroup>

      <IFormGroup :label="$t('webforms::form.sections.introduction.message')">
        <Editor v-model="message" :with-image="false" />
      </IFormGroup>

      <div class="space-x-2 text-right">
        <IButton
          variant="white"
          :text="$t('core::app.cancel')"
          @click="editing = false"
        />

        <IButton
          variant="secondary"
          :text="$t('core::app.save')"
          @click="requestSectionSave"
        />
      </div>
    </div>
  </ICard>
</template>

<script setup>
import { ref } from 'vue'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps({
  index: { type: Number },
  form: { type: Object, required: true },
  section: { required: true, type: Object },
})

const emit = defineEmits(['updateSectionRequested'])

const editing = ref(false)
const title = ref(null)
const message = ref(null)

function requestSectionSave() {
  emit('updateSectionRequested', {
    title: title.value,
    message: message.value,
  })

  editing.value = false
}

function setEditingMode() {
  title.value = props.section.title
  message.value = props.section.message
  editing.value = true
}
</script>
