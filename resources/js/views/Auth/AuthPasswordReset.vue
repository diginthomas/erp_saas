<template>
  <form class="space-y-6" method="POST" @submit.prevent="submit">
    <IAlert variant="success" :show="successMessage !== null">
      {{ successMessage }}

      <!-- We will redirect to login as the user is already logged in and will be redirected to the HOME route -->
      <a
        :href="appUrl + '/login'"
        class="link link-primary mt-2 font-medium"
        v-text="$t('core::dashboard.dashboard')"
      />
    </IAlert>

    <IFormGroup :label="$t('auth.email_address')" label-for="email">
      <IFormInput
        id="email"
        v-model="form.email"
        type="email"
        name="email"
        autocomplete="email"
        autofocus
        required
      />

      <IFormError :error="form.getError('email')" />
    </IFormGroup>

    <IFormGroup :label="$t('auth.password')" label-for="password">
      <IFormInput
        id="password"
        v-model="form.password"
        type="password"
        name="password"
        required
        autocomplete="new-password"
      />

      <IFormError :error="form.getError('password')" />
    </IFormGroup>

    <IFormGroup
      :label="$t('auth.confirm_password')"
      label-for="password-confirm"
    >
      <IFormInput
        id="password-confirm"
        v-model="form.password_confirmation"
        type="password"
        name="password_confirmation"
        required
        autocomplete="new-password"
      />
    </IFormGroup>

    <IButton
      type="submit"
      size="md"
      block
      :disabled="requestInProgress"
      :loading="requestInProgress"
      :text="$t('passwords.reset_password')"
      @click="resetPassword"
    />
  </form>
</template>

<script setup>
import { ref } from 'vue'

import { useApp } from '@/Core/composables/useApp'
import { useForm } from '@/Core/composables/useForm'

const props = defineProps({
  email: String,
  token: { required: true, type: String },
})

const { appUrl } = useApp()

const requestInProgress = ref(false)
const successMessage = ref(null)

const { form } = useForm({
  token: props.token,
  email: props.email,
  password: null,
  password_confirmation: null,
})

async function resetPassword() {
  requestInProgress.value = true

  await Innoclapps.request(appUrl + '/sanctum/csrf-cookie')

  form
    .post(appUrl + '/password/reset')
    .then(data => (successMessage.value = data.message))
    .finally(() => (requestInProgress.value = false))
}
</script>
