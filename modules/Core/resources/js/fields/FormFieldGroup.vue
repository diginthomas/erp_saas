<template>
  <IFormGroup :description="description">
    <ILink
      v-if="isToggleable"
      class="flex items-center"
      @click="toggleIsVisible = true"
    >
      <Icon icon="Plus" class="mr-1 size-4" />

      <span v-text="label" />
    </ILink>

    <div v-show="!isToggleable">
      <IFormLabel
        v-if="label && !field.hideLabel"
        :for="!asParagraphLabel ? fieldId : undefined"
        :tag="asParagraphLabel ? 'p' : undefined"
        class="mb-1 inline-flex items-center"
        :required="field.isRequired"
      >
        <Icon
          v-if="displayHelpAsIcon"
          v-i-tooltip="field.helpText"
          icon="QuestionMarkCircle"
          class="mr-1 size-4 text-neutral-500 hover:text-neutral-700 dark:text-white dark:hover:text-neutral-300"
        />
        <!-- eslint-disable-next-line vue/no-v-html -->
        <span v-html="label"></span>
      </IFormLabel>

      <slot />
    </div>

    <IAlert
      v-if="displayHelpAsAlert"
      variant="info"
      class="mt-3"
      :text="field.helpText"
    />

    <IFormError v-if="validationError" :error="validationError" />
  </IFormGroup>
</template>

<script setup>
import { computed, ref } from 'vue'

import { isBlank } from '@/Core/utils'

const props = defineProps({
  field: { required: true, type: Object },
  validationErrors: Object,
  fieldId: String,
  label: String,
  asParagraphLabel: Boolean,
})

const toggleIsVisible = ref(false)

const validationError = computed(() => {
  if (
    props.validationErrors &&
    props.validationErrors.messages &&
    props.validationErrors.messages[0]
  ) {
    return props.validationErrors.messages[0]
  }

  return null
})

const isToggleable = computed(
  () => props.field.toggleable && !toggleIsVisible.value && !hasValue.value
)

const displayHelpAsIcon = computed(
  () => props.field.helpText && props.field.helpTextDisplay === 'icon'
)

const displayHelpAsText = computed(
  () => props.field.helpText && props.field.helpTextDisplay === 'text'
)

const displayHelpAsAlert = computed(
  () => props.field.helpText && props.field.helpTextDisplay === 'alert'
)

const hasValue = computed(() => !isBlank(props.field.value))

const description = computed(() =>
  !isToggleable.value && displayHelpAsText.value ? props.field.helpText : null
)
</script>
