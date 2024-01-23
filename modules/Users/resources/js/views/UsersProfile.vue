<template>
  <MainLayout>
    <div class="m-auto max-w-7xl">
      <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
          <h3
            v-t="'core::app.avatar'"
            class="text-base/6 font-medium text-neutral-700 dark:text-white"
          />

          <p
            v-t="'users::profile.avatar_info'"
            class="mt-1 text-sm text-neutral-500 dark:text-neutral-300"
          />
        </div>

        <div class="mt-5 md:col-span-2 md:mt-0">
          <ICard>
            <CropsAndUploadsImage
              name="avatar"
              :upload-url="`${$scriptConfig('apiURL')}/users/${
                currentUser.id
              }/avatar`"
              :image="currentUser.uploaded_avatar_url"
              :cropper-options="{ aspectRatio: 1 / 1 }"
              :choose-text="
                currentUser.uploaded_avatar_url
                  ? $t('core::app.change')
                  : $t('core::app.upload_avatar')
              "
              @cleared="clearAvatar"
              @success="handleAvatarUploaded"
            />
          </ICard>
        </div>
      </div>

      <div class="hidden sm:block" aria-hidden="true">
        <div class="py-5">
          <div class="border-t border-neutral-200 dark:border-neutral-600" />
        </div>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <h3
              v-t="'users::profile.profile'"
              class="text-base/6 font-medium text-neutral-700 dark:text-white"
            />

            <p
              v-t="'users::profile.profile_info'"
              class="mt-1 text-sm text-neutral-500 dark:text-neutral-300"
            />
          </div>

          <div class="mt-5 md:col-span-2 md:mt-0">
            <ICard
              footer-class="text-right"
              tag="form"
              @submit.prevent="update"
            >
              <IFormGroup :label="$t('users::user.name')" label-for="name">
                <IFormInput id="name" v-model="form.name" name="name" />

                <IFormError :error="form.getError('name')" />
              </IFormGroup>

              <IFormGroup :label="$t('users::user.email')" label-for="email">
                <IFormInput
                  id="email"
                  v-model="form.email"
                  name="email"
                  type="email"
                >
                </IFormInput>

                <IFormError :error="form.getError('email')" />
              </IFormGroup>

              <IFormGroup
                :label="$t('mailclient::mail.signature')"
                label-for="mail_signature"
                :description="$t('mailclient::mail.signature_info')"
              >
                <MailEditor v-model="form.mail_signature" />

                <IFormError :error="form.getError('mail_signature')" />
              </IFormGroup>

              <template #footer>
                <IButton
                  :disabled="form.busy"
                  :text="$t('users::profile.update')"
                  @click="update"
                />
              </template>
            </ICard>
          </div>
        </div>
      </div>

      <div class="hidden sm:block" aria-hidden="true">
        <div class="py-5">
          <div class="border-t border-neutral-200 dark:border-neutral-600" />
        </div>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <h3
              v-t="'users::user.localization'"
              class="text-base/6 font-medium text-neutral-700 dark:text-white"
            />

            <p
              v-t="'users::profile.localization_info'"
              class="mt-1 text-sm text-neutral-500 dark:text-neutral-300"
            />
          </div>

          <div class="mt-5 md:col-span-2 md:mt-0">
            <ICard footer-class="text-right">
              <LocalizationFields
                :form="form"
                @update:time-format="form.time_format = $event"
                @update:date-format="form.date_format = $event"
                @update:locale="form.locale = $event"
                @update:timezone="form.timezone = $event"
              />

              <template #footer>
                <IButton
                  :disabled="form.busy"
                  :text="$t('core::app.save')"
                  @click="update"
                />
              </template>
            </ICard>
          </div>
        </div>
      </div>

      <div class="hidden sm:block" aria-hidden="true">
        <div class="py-5">
          <div class="border-t border-neutral-200 dark:border-neutral-600" />
        </div>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <h3
              v-t="'core::notifications.notifications'"
              class="text-base/6 font-medium text-neutral-700 dark:text-white"
            />

            <p
              v-t="'users::profile.notifications_info'"
              class="mt-1 text-sm text-neutral-500 dark:text-neutral-300"
            />
          </div>

          <div class="mt-5 md:col-span-2 md:mt-0">
            <ICard id="notifications" footer-class="text-right" no-body>
              <NotificationSettings
                v-model="form.notifications_settings"
                class="-mt-px"
              />

              <template #footer>
                <IButton
                  :disabled="form.busy"
                  :text="$t('core::app.save')"
                  @click="update"
                />
              </template>
            </ICard>
          </div>
        </div>
      </div>

      <div class="hidden sm:block" aria-hidden="true">
        <div class="py-5">
          <div class="border-t border-neutral-200 dark:border-neutral-600" />
        </div>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <h3
              v-t="'auth.password'"
              class="text-base/6 font-medium text-neutral-700 dark:text-white"
            />

            <p
              v-t="'users::profile.password_info'"
              class="mt-1 text-sm text-neutral-500 dark:text-neutral-300"
            />
          </div>

          <div class="mt-5 md:col-span-2 md:mt-0">
            <ICard
              footer-class="text-right"
              tag="form"
              @submit.prevent="updatePassword"
            >
              <IFormGroup
                :label="$t('auth.current_password')"
                label-for="old_password"
              >
                <IFormInput
                  id="old_password"
                  v-model="formPassword.old_password"
                  name="old_password"
                  type="password"
                  autocomplete="current-password"
                >
                </IFormInput>

                <IFormError :error="formPassword.getError('old_password')" />
              </IFormGroup>

              <IFormGroup>
                <template #label>
                  <div class="flex">
                    <IFormLabel
                      class="mb-1 grow"
                      for="password"
                      :label="$t('auth.new_password')"
                    />

                    <ILink
                      :text="$t('core::app.password_generator.heading')"
                      @click="showGeneratePassword = !showGeneratePassword"
                    />
                  </div>
                </template>

                <IFormInput
                  id="password"
                  v-model="formPassword.password"
                  name="password"
                  type="password"
                  autocomplete="new-password"
                >
                </IFormInput>

                <IFormError :error="formPassword.getError('password')" />
              </IFormGroup>

              <IFormGroup
                :label="$t('auth.confirm_password')"
                label-for="password_confirmation"
              >
                <IFormInput
                  id="password_confirmation"
                  v-model="formPassword.password_confirmation"
                  name="password_confirmation"
                  type="password"
                  autocomplete="new-password"
                >
                </IFormInput>

                <IFormError
                  :error="formPassword.getError('password_confirmation')"
                />
              </IFormGroup>

              <PasswordGenerator v-show="showGeneratePassword" />

              <template #footer>
                <IButton
                  type="submit"
                  :disabled="formPassword.busy"
                  :text="$t('auth.change_password')"
                />
              </template>
            </ICard>
          </div>
        </div>
      </div>

      <div
        v-if="managedTeams.length > 0"
        class="hidden sm:block"
        aria-hidden="true"
      >
        <div class="py-5">
          <div class="border-t border-neutral-200 dark:border-neutral-600" />
        </div>
      </div>

      <div v-if="managedTeams.length > 0" class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <h3
              class="text-base/6 font-medium text-neutral-700 dark:text-white"
            >
              {{ $t('users::team.your_teams', managedTeams.length) }}
            </h3>

            <p
              v-t="'users::team.managing_teams'"
              class="mt-1 text-sm text-neutral-500 dark:text-neutral-300"
            />
          </div>

          <div class="mt-5 md:col-span-2 md:mt-0">
            <ICard>
              <ul
                role="list"
                class="space-y-4 divide-y divide-neutral-200 dark:divide-neutral-700"
              >
                <li
                  v-for="team in managedTeams"
                  :key="team.id"
                  class="pt-4 first:pt-0"
                >
                  <p
                    class="truncate text-base font-medium text-neutral-800 dark:text-neutral-100"
                    v-text="team.name"
                  />

                  <p
                    v-t="'users::team.members'"
                    class="mb-2 mt-2 text-sm font-medium text-neutral-500 dark:text-neutral-300"
                  />

                  <div
                    v-for="member in team.members"
                    :key="'info-' + member.email"
                    class="mb-1 flex items-center space-x-1.5 last:mb-0"
                  >
                    <IAvatar
                      :alt="member.name"
                      size="xs"
                      :src="member.avatar_url"
                    />

                    <p
                      class="text-sm font-medium text-neutral-700 dark:text-neutral-300"
                      v-text="member.name"
                    />
                  </div>
                </li>
              </ul>
            </ICard>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed, ref, unref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'
import cloneDeep from 'lodash/cloneDeep'
import reduce from 'lodash/reduce'

import LocalizationFields from '@/Core/components/LocalizationFields.vue'
import PasswordGenerator from '@/Core/components/PasswordGenerator.vue'
import { useApp } from '@/Core/composables/useApp'
import { useForm } from '@/Core/composables/useForm'

import MailEditor from '@/MailClient/components/MailEditor.vue'

import NotificationSettings from '../components/UserNotificationSettings.vue'
import { useTeams } from '../composables/useTeams'

const { t } = useI18n()
const store = useStore()
const { currentUser, resetStoreState } = useApp()

const user = unref(currentUser)

const { form } = useForm()
const { form: formPassword } = useForm({}, { resetOnSuccess: true })

const { teams } = useTeams()

const showGeneratePassword = ref(false)

const managedTeams = computed(() =>
  teams.value.filter(team => team.manager.id === currentUser.value.id)
)

let originalLocale = null

function handleAvatarUploaded(updatedUser) {
  store.commit('users/UPDATE', {
    id: updatedUser.id,
    item: updatedUser,
  })

  user.avatar = updatedUser.avatar
  user.avatar_url = updatedUser.avatar_url
  // Update form avatar with new value
  // to prevent using the old value if the user saves the profile
  form.avatar = user.avatar
}

function clearAvatar() {
  if (!user.avatar) {
    return
  }

  Innoclapps.request()
    .delete(`/users/${user.id}/avatar`)
    .then(({ data }) => {
      form.avatar = data.avatar
      store.commit('users/UPDATE', { id: user.id, item: data })
    })
}

function update() {
  form.put('/profile').then(user => {
    store.commit('users/UPDATE', { id: user.id, item: user })

    Innoclapps.success(t('users::profile.updated'))

    if (originalLocale !== form.locale) {
      window.location.reload()
    } else {
      resetStoreState()
    }
  })
}

function updatePassword() {
  formPassword.put('/profile/password').then(() => {
    Innoclapps.success(t('users::profile.password_updated'))
  })
}

function prepareComponent() {
  originalLocale = user.locale

  form.set({
    name: user.name,
    email: user.email,
    mail_signature: user.mail_signature,
    date_format: user.date_format,
    time_format: user.time_format,
    timezone: user.timezone,
    locale: user.locale,
    notifications_settings: reduce(
      cloneDeep(user.notifications.settings),
      (obj, val) => {
        obj[val.key] = val.availability

        return obj
      },
      {}
    ),
  })

  formPassword.set({
    old_password: null,
    password: null,
    password_confirmation: null,
  })
}

prepareComponent()
</script>
