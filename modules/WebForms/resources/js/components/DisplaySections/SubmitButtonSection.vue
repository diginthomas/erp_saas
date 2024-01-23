<template>
  <div>
    <IFormGroup v-if="validateWithReCaptcha">
      <VueRecaptcha
        :sitekey="reCaptcha.siteKey"
        @verify="handleReCaptchaVerified"
      />

      <IFormError :error="form.getError('g-recaptcha-response')" />
    </IFormGroup>

    <div v-if="section.privacyPolicyAcceptIsRequired" class="mb-3 flex">
      <IFormCheckbox
        id="acceptPrivacyPolicy"
        v-model:checked="privacyPolicyAccepted"
        @change="form.fill('_privacy-policy', $event)"
      />

      <div class="text-sm text-neutral-600 dark:text-neutral-300">
        <I18nT
          scope="global"
          :keypath="'core::app.agree_to_privacy_policy'"
          class="inline-block w-full"
        >
          <template #privacyPolicyLink>
            <a
              v-t="'core::app.privacy_policy'"
              :href="section.privacyPolicyUrl"
              class="link link-primary"
            />
          </template>
        </I18nT>

        <div>
          <IFormError :error="form.getError('_privacy-policy')" />
        </div>
      </div>
    </div>

    <IButton
      id="submitButton"
      type="submit"
      size="md"
      :disabled="form.busy"
      :loading="form.busy"
      block
      :text="section.text"
    />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { VueRecaptcha } from 'vue-recaptcha'

import { useApp } from '@/Core/composables/useApp'

import propsDefinition from './props'

const props = defineProps(propsDefinition)

const emit = defineEmits({
  fillFormAttribute: ({ attribute, value }) => {
    if (attribute && typeof value != 'undefined') {
      return true
    } else {
      console.warn('Invalid "fillFormAttribute" event payload!')

      return false
    }
  },
})

const { scriptConfig } = useApp()

const reCaptcha = scriptConfig('reCaptcha') || {}
const privacyPolicyAccepted = ref(false)

const validateWithReCaptcha = computed(() => {
  if (!props.section.spamProtected) {
    return false
  }

  return reCaptcha.validate && reCaptcha.configured
})

function handleReCaptchaVerified(response) {
  emit('fillFormAttribute', {
    attribute: 'g-recaptcha-response',
    value: response,
  })
}
</script>

<style>
#submitButton {
  color: var(--primary-contrast);
}
</style>
