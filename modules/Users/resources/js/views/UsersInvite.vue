<template>
  <IModal
    :ok-text="$t('users::user.send_invitation')"
    :ok-disabled="form.busy"
    :title="$t('users::user.invite')"
    size="sm"
    form
    static-backdrop
    visible
    @submit="invite"
    @hidden="$router.back"
    @shown="handleModalShown"
  >
    <p class="text-sm text-neutral-700 dark:text-white">
      {{
        $t('users::user.invitation_expires_after_info', {
          total: $scriptConfig('invitation.expires_after'),
        })
      }}
    </p>

    <div
      class="mb-4 border-b border-neutral-200 pt-4 dark:border-neutral-700"
    />

    <div class="mb-3 flex">
      <IFormLabel
        class="grow text-neutral-900 dark:text-neutral-100"
        :label="$t('users::user.email')"
        for="email0"
        required
      />

      <IButtonIcon
        v-i-tooltip="$t('core::app.add_another')"
        icon="Plus"
        @click="addEmail"
      />
    </div>

    <div
      v-for="(email, index) in form.emails"
      :key="index"
      class="relative mb-3"
    >
      <IFormInput
        :id="'email' + index"
        ref="emailsRef"
        v-model="form.emails[index]"
        type="email"
        :placeholder="$t('users::user.email')"
        @keydown.enter.prevent.stop="addEmail"
      />

      <IButtonIcon
        v-show="index > 0"
        class="absolute right-3 top-2"
        icon="X"
        icon-class="size-4"
        @click="removeEmail(index)"
      />

      <IFormError :error="form.getError('emails.' + index)" />
    </div>

    <IFormGroup :label="$t('core::role.roles')" label-for="roles" class="mt-3">
      <ICustomSelect
        v-model="form.roles"
        input-id="roles"
        :placeholder="$t('users::user.roles')"
        :options="rolesNames"
        label="name"
        :multiple="true"
      />
    </IFormGroup>

    <IFormGroup :label="$t('users::team.teams')" label-for="teams">
      <ICustomSelect
        v-model="form.teams"
        input-id="teams"
        :placeholder="$t('users::team.teams')"
        :options="teams"
        label="name"
        :reduce="team => team.id"
        :multiple="true"
      />
    </IFormGroup>

    <div
      :class="[
        'flex items-center rounded-md border-2 px-5 py-4 shadow-sm',
        form.super_admin
          ? 'border-primary-400'
          : 'border-neutral-200 dark:border-neutral-400',
      ]"
    >
      <div class="grow">
        <p
          v-t="'users::user.super_admin'"
          class="text-sm font-medium text-neutral-900 dark:text-white"
        />

        <span
          v-t="'users::user.as_super_admin_info'"
          class="text-sm text-neutral-600 dark:text-neutral-300"
        />
      </div>

      <div class="ml-3">
        <IFormToggle
          v-model="form.super_admin"
          @change="handleSuperAdminChange"
        />
      </div>
    </div>

    <div
      :class="[
        'mt-3 flex items-center rounded-md border-2 px-5 py-4 shadow-sm',
        form.access_api
          ? 'border-primary-400'
          : 'border-neutral-200 dark:border-neutral-400',
      ]"
    >
      <div class="grow">
        <p
          v-t="'users::user.enable_api'"
          class="text-sm font-medium text-neutral-900 dark:text-white"
        />

        <span
          v-t="'users::user.allow_api_info'"
          class="text-sm text-neutral-600 dark:text-neutral-300"
        />
      </div>

      <div class="ml-3">
        <IFormToggle v-model="form.access_api" :disabled="form.super_admin" />
      </div>
    </div>
  </IModal>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

import { useForm } from '@/Core/composables/useForm'
import { useRoles } from '@/Core/composables/useRoles'

import { useTeams } from '../composables/useTeams'

const { t } = useI18n()
const router = useRouter()

const emailsRef = ref([])

const { rolesNames } = useRoles()
const { teamsByName: teams } = useTeams()

const { form } = useForm({
  emails: [''],
  access_api: false,
  super_admin: false,
  roles: [],
})

const totalEmails = computed(() => form.emails.length)

function addEmail() {
  form.emails.push('')

  nextTick(() => {
    emailsRef.value[totalEmails.value - 1].focus()
  })
}

function removeEmail(index) {
  form.emails.splice(index, 1)

  nextTick(() => {
    if (form.emails[totalEmails.value - 1] === '') {
      emailsRef.value[totalEmails.value - 1].focus()
    }
  })
}

function handleModalShown() {
  nextTick(() => {
    emailsRef.value[0].focus()
  })
}

function handleSuperAdminChange(val) {
  if (val) {
    form.access_api = true
  }
}

function invite() {
  form.post('/users/invite').then(() => {
    Innoclapps.success(t('users::user.invited'))
    router.back()
  })
}
</script>
