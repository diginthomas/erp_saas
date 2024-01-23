<template>
  <div v-show="visible" class="mx-auto max-w-3xl">
    <IAlert
      v-if="Boolean(form.originalData.send_at)"
      variant="info"
      class="mb-5"
    >
      {{
        $t('documents::document.send.is_scheduled', {
          date: localizedDateTime(form.originalData.send_at),
        })
      }}
    </IAlert>

    <div v-if="form.requires_signature" class="mb-6">
      <h3
        v-t="'documents::document.send.send_to_signers'"
        :class="[
          'mb-3 text-base font-medium text-neutral-800 dark:text-neutral-100',
          {
            hidden:
              filledSigners.length === 0 && document.status === 'accepted',
          },
        ]"
      />

      <p
        v-show="filledSigners.length === 0 && document.status !== 'accepted'"
        v-t="'documents::document.send.send_to_signers_empty'"
        class="-mt-3 text-sm text-neutral-500 dark:text-neutral-400"
      />

      <IFormCheckbox
        v-for="signer in filledSigners"
        :key="signer.email"
        v-model:checked="signer.send_email"
      >
        {{ signer.name + ' (' + signer.email + ')' }}

        <span
          v-if="signer.sent_at"
          class="text-neutral-500 dark:text-neutral-300"
        >
          -
          {{
            $t('documents::document.sent_at', {
              date: localizedDateTime(signer.sent_at),
            })
          }}
        </span>
      </IFormCheckbox>
    </div>

    <div class="mb-3 inline-flex items-center">
      <h3
        v-t="'documents::document.recipients.recipients'"
        class="text-base font-medium text-neutral-800 dark:text-neutral-100"
      />

      <span class="ml-1 mt-1 text-xs text-neutral-500 dark:text-neutral-400">
        ({{ $t('documents::document.recipients.additional_recipients') }})
      </span>
    </div>

    <div class="table-responsive">
      <div
        class="overflow-auto border border-neutral-200 sm:rounded-md dark:border-neutral-800"
      >
        <table
          class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700"
        >
          <thead class="bg-neutral-50 dark:bg-neutral-800">
            <tr>
              <th
                class="bg-neutral-50 p-2 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-200"
              />

              <th
                v-t="'documents::document.recipients.recipient_name'"
                class="bg-neutral-50 p-2 text-left text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-800 dark:text-neutral-200"
              />

              <th
                v-t="'documents::document.recipients.recipient_email'"
                class="bg-neutral-50 p-2 text-left text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-800 dark:text-neutral-200"
              />

              <th
                v-t="'documents::document.recipients.is_sent'"
                class="bg-neutral-50 p-2 text-center text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-800 dark:text-neutral-200"
              />

              <th />
            </tr>
          </thead>

          <tbody
            class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800"
          >
            <tr v-if="form.recipients.length === 0">
              <td
                v-t="'documents::document.recipients.no_recipients'"
                colspan="5"
                class="bg-white p-3 align-middle text-sm text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400"
              />
            </tr>

            <tr v-for="(recipient, index) in form.recipients" :key="index">
              <td
                class="border-r border-neutral-200 bg-white p-2 text-neutral-900 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100"
              >
                <span
                  class="ml-1 inline-flex min-w-full items-center justify-center"
                >
                  <IFormCheckbox v-model:checked="recipient.send_email" />
                </span>
              </td>

              <td
                class="bg-white p-2 align-top text-sm font-medium text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100"
              >
                <IFormInput
                  ref="recipientNameInputRef"
                  v-model="recipient.name"
                  :placeholder="
                    $t('documents::document.recipients.enter_full_name')
                  "
                />

                <IFormError
                  :error="form.getError('recipients.' + index + '.name')"
                />
              </td>

              <td
                class="bg-white p-2 align-top text-sm font-medium text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100"
              >
                <IFormInput
                  v-model="recipient.email"
                  type="email"
                  :placeholder="
                    $t('documents::document.recipients.enter_email')
                  "
                  @keyup.enter="insertEmptyRecipient"
                />

                <IFormError
                  :error="form.getError('recipients.' + index + '.email')"
                />
              </td>

              <td
                class="bg-white p-2 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100"
              >
                <span class="inline-flex min-w-full justify-center">
                  <span
                    v-i-tooltip="
                      recipient.sent_at
                        ? localizedDateTime(recipient.sent_at)
                        : null
                    "
                    :class="[
                      'inline-block size-4 rounded-full',
                      recipient.sent_at ? 'bg-success-400' : 'bg-danger-400',
                    ]"
                  />
                </span>
              </td>

              <td
                class="bg-white p-2 text-sm font-medium text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100"
              >
                <IButtonIcon icon="X" @click="removeRecipient(index)" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <ILink
      v-show="!emptyRecipientsExists"
      class="mt-3 inline-block font-medium"
      @click="insertEmptyRecipient"
    >
      &plus; {{ $t('documents::document.recipients.add') }}
    </ILink>

    <h3
      v-t="'documents::document.send.send'"
      class="mb-3 mt-6 text-base font-medium text-neutral-800 dark:text-neutral-100"
    />

    <p
      v-show="!selectedBrand"
      v-t="'documents::document.send.select_brand'"
      class="mt-3 text-sm text-neutral-500"
    />

    <div v-if="selectedBrand">
      <IFormGroup
        :label="$t('documents::document.send.send_from_account')"
        label-for="send_mail_account_id"
      >
        <ICustomSelect
          v-if="mailAccounts.length"
          :model-value="mailAccount"
          input-id="send_mail_account_id"
          :clearable="false"
          :options="mailAccounts"
          label="email"
          @update:model-value="form.send_mail_account_id = $event.id"
          @option-selected="setActiveMailAccount(mailAccounts)"
        />

        <IFormText v-else class="mt-2 inline-flex items-center">
          <Icon
            icon="ExclamationTriangle"
            class="mr-1 size-5 text-warning-500"
          />
          {{ $t('documents::document.send.connect_an_email_account') }}
        </IFormText>
      </IFormGroup>

      <IFormGroup
        :label="$t('documents::document.send.send_subject')"
        label-for="send_mail_subject"
        class="mt-4"
      >
        <IFormInput id="send_mail_subject" v-model="form.send_mail_subject" />
      </IFormGroup>

      <IFormGroup
        :label="$t('documents::document.send.send_body')"
        label-for="send_mail_body"
      >
        <Editor
          id="send_mail_body"
          v-model="form.send_mail_body"
          :with-image="false"
        />
      </IFormGroup>

      <div class="mb-4">
        <div class="inline-block">
          <IFormCheckbox
            v-model:checked="scheduleSend"
            v-i-tooltip="
              !document.id
                ? $t('documents::document.send.save_to_schedule')
                : null
            "
            :disabled="!document.id"
            :label="$t('documents::document.send.send_later')"
            @update:checked="!$event ? (form.send_at = null) : ''"
          />
        </div>

        <DatePicker
          v-if="scheduleSend && document.id"
          v-model="form.send_at"
          class="mt-3"
          min-date="now"
          mode="dateTime"
          :placeholder="$t('documents::document.send.select_schedule_date')"
          :required="true"
        />
      </div>

      <span
        v-i-tooltip="
          document.authorizations && !document.authorizations.update
            ? $t('core::app.action_not_authorized')
            : ''
        "
        class="inline-block"
      >
        <IButton
          v-show="!scheduleSend"
          :loading="sending"
          icon="Mail"
          :text="$t('core::app.send')"
          :disabled="
            sending ||
            !isEligibleForSending ||
            (document.authorizations && !document.authorizations.update)
          "
          @click="$emit('sendRequested')"
        />

        <IButton
          v-show="scheduleSend"
          :text="$t('documents::document.send.schedule')"
          :disabled="
            form.busy ||
            !isEligibleForSending ||
            !form.send_at ||
            (document.authorizations && !document.authorizations.update)
          "
          icon="Clock"
          @click="$emit('saveRequested')"
        />
      </span>
    </div>
  </div>
</template>

<!-- eslint-disable vue/no-mutating-props -->
<script setup>
import { computed, inject, nextTick, ref, watch } from 'vue'
import { useStore } from 'vuex'
import find from 'lodash/find'

import { useDates } from '@/Core/composables/useDates'
import { isBlank } from '@/Core/utils'

import propsDefinition from './formSectionProps'

const props = defineProps({
  ...propsDefinition,
  sending: Boolean,
})

defineEmits(['sendRequested', 'saveRequested'])

const store = useStore()

const { localizedDateTime } = useDates()

const brands = inject('brands')

const recipientNameInputRef = ref(null)
const mailAccount = ref(null)
const scheduleSend = ref(Boolean(props.form.send_at))

const selectedBrand = computed(() => {
  if (!props.form.brand_id) {
    return null
  }

  return find(brands.value, ['id', parseInt(props.form.brand_id)])
})

const mailAccounts = computed(() => store.getters['emailAccounts/accounts'])

const filledSigners = computed(() =>
  props.form.signers.filter(
    signer => !isBlank(signer.name) && !isBlank(signer.email)
  )
)

const filledAndEnabledRecipients = computed(() =>
  props.form.recipients.filter(
    recipient =>
      recipient.send_email &&
      !isBlank(recipient.name) &&
      !isBlank(recipient.email)
  )
)

const filledAndEnabledSignersRecipients = computed(() =>
  props.form.signers.filter(
    signer =>
      signer.send_email && !isBlank(signer.name) && !isBlank(signer.email)
  )
)

const emptyRecipientsExists = computed(
  () =>
    props.form.recipients.filter(
      recipient => isBlank(recipient.name) || isBlank(recipient.email)
    ).length > 0
)

const isEligibleForSending = computed(
  () =>
    !(
      !props.form.send_mail_body ||
      !props.form.send_mail_subject ||
      !props.form.send_mail_account_id ||
      (filledAndEnabledRecipients.value.length === 0 &&
        filledAndEnabledSignersRecipients.value.length === 0)
    )
)

function fetchAccounts() {
  return store.dispatch('emailAccounts/fetch')
}

function removeRecipient(index) {
  props.form.recipients.splice(index, 1)
}

function insertEmptyRecipient() {
  props.form.recipients.push({ name: '', email: '', send_email: true })

  nextTick(() => {
    recipientNameInputRef.value[props.form.recipients.length - 1].focus()
  })
}

function setActiveMailAccount(accounts) {
  if (props.form.send_mail_account_id) {
    mailAccount.value = find(accounts, [
      'id',
      parseInt(props.form.send_mail_account_id),
    ])
  } else {
    mailAccount.value = accounts.length ? accounts[0] : null

    if (mailAccount.value) {
      props.form.send_mail_account_id = mailAccount.value.id
    }
  }
}

watch(
  selectedBrand,
  (newVal, oldVal) => {
    if (
      (newVal && isBlank(props.form.send_mail_body)) ||
      (oldVal &&
        props.form.send_mail_body ===
          oldVal.config.document.mail_message[props.form.locale])
    ) {
      props.form.send_mail_body =
        newVal.config.document.mail_message[props.form.locale]
    }

    if (
      (newVal && isBlank(props.form.send_mail_subject)) ||
      (oldVal &&
        oldVal.config.document.mail_subject[props.form.locale] ===
          props.form.send_mail_subject)
    ) {
      props.form.send_mail_subject =
        newVal.config.document.mail_subject[props.form.locale]
    }
  },
  { immediate: true }
)

fetchAccounts().then(setActiveMailAccount)
</script>
