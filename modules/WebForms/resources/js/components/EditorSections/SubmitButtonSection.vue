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
        v-t="'webforms::form.sections.submit.button'"
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

    <div
      v-show="!editing"
      class="text-sm text-neutral-900 dark:text-neutral-300"
    >
      <p v-text="section.text"></p>
    </div>

    <div v-if="editing">
      <IFormGroup
        :label="$t('webforms::form.sections.submit.button_text')"
        label-for="text"
      >
        <IFormInput id="text" v-model="text" />
      </IFormGroup>

      <IFormGroup v-show="$scriptConfig('reCaptcha.configured')">
        <IFormCheckbox
          id="spam_protected"
          v-model:checked="spamProtected"
          name="spam_protected"
          :label="$t('webforms::form.sections.submit.spam_protected')"
        />
      </IFormGroup>

      <IFormGroup>
        <IFormCheckbox
          id="require_privacy_policy"
          v-model:checked="privacyPolicyAcceptIsRequired"
          name="require_privacy_policy"
          :label="$t('webforms::form.sections.submit.require_privacy_policy')"
        />
      </IFormGroup>

      <IFormGroup
        v-show="privacyPolicyAcceptIsRequired"
        :label="$t('webforms::form.sections.submit.privacy_policy_url')"
        label-for="privacy_policy_url"
      >
        <IFormInput id="privacy_policy_url" v-model="privacyPolicyUrl" />
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

import { useApp } from '@/Core/composables/useApp'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps({
  index: { type: Number },
  form: { type: Object, required: true },
  section: { required: true, type: Object },
})

const emit = defineEmits(['updateSectionRequested'])

const { scriptConfig } = useApp()

const editing = ref(false)
const text = ref(null)
const spamProtected = ref(false)
const privacyPolicyAcceptIsRequired = ref(false)
const privacyPolicyUrl = ref(scriptConfig('privacyPolicyUrl'))

function requestSectionSave() {
  emit('updateSectionRequested', {
    text: text.value,
    spamProtected: spamProtected.value,
    privacyPolicyAcceptIsRequired: privacyPolicyAcceptIsRequired.value,
    privacyPolicyUrl: privacyPolicyUrl.value,
  })

  editing.value = false
}

function setEditingMode() {
  text.value = props.section.text
  spamProtected.value = props.section.spamProtected

  privacyPolicyAcceptIsRequired.value =
    props.section.privacyPolicyAcceptIsRequired
  privacyPolicyUrl.value = props.section.privacyPolicyUrl

  editing.value = true
}
</script>
