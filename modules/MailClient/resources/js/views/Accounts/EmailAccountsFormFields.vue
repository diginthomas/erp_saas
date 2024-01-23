<!-- eslint-disable vue/no-mutating-props -->
<template>
  <IAlert variant="info" class="mb-5" :show="accountConfigError !== null">
    <!-- eslint-disable-next-line vue/no-v-html -->
    <div v-html="accountConfigError" />
  </IAlert>

  <div
    :class="{
      'mb-3 rounded-md border border-warning-400 px-4 py-3':
        !form.connection_type,
    }"
  >
    <IFormGroup
      required
      :label="$t('mailclient::mail.account.type')"
      label-for="connection_type"
    >
      <ICustomSelect
        v-model="form.connection_type"
        :options="accountTypes"
        :clearable="false"
        :placeholder="$t('mailclient::mail.account.select_type')"
        :disabled="!isCreateView"
        @option-selected="handleAccountConnectionTypeChange"
      >
      </ICustomSelect>

      <IFormError :error="form.getError('connection_type')" />
    </IFormGroup>

    <div
      v-if="isCreateView && hasHangingOAuthAccounts"
      :class="{ 'mb-3': form.connection_type }"
    >
      <p
        v-t="'core::oauth.or_choose_existing'"
        class="mb-2 mt-4 text-sm text-neutral-800 dark:text-neutral-200"
      />

      <OAuthAccount
        v-for="oAuthAccount in notConnectedOAuthAccounts"
        :key="oAuthAccount.id"
        :account="oAuthAccount"
        :with-reconnect-link="false"
        class="mb-2"
      >
        <IButton
          class="ml-2"
          :disabled="oAuthAccount.requires_auth"
          :text="$t('core::oauth.connect')"
          @click="connectExistingOAuthAccount(oAuthAccount)"
        />
      </OAuthAccount>
    </div>
  </div>

  <div
    v-if="isCreateView"
    class="mb-3 rounded-md border border-neutral-200 p-3 dark:border-neutral-600"
  >
    <IFormLabel :label="$t('mailclient::mail.account.sync_emails_from')" />

    <div class="mt-3 flex flex-col items-center sm:flex-row sm:space-x-2">
      <IFormRadio
        v-for="initialSync in initialSyncOptions"
        :id="'initial-sync-' + initialSync.value"
        :key="initialSync.value"
        v-model="form.initial_sync_from"
        :label="initialSync.text"
        class="self-start"
        :value="initialSync.value"
        name="initial_sync_from"
      />
    </div>

    <IAlert v-if="showInitialSyncOptionWarning" class="mt-4">
      {{
        $t('mailclient::mail.account.sync_period_note', {
          date: localizedDate(form.initial_sync_from),
        })
      }}
    </IAlert>
  </div>

  <div
    :class="{
      'pointer-events-none blur-sm': shouldBlurServerConfigureableFields,
    }"
  >
    <IFormGroup
      :label="$t('mailclient::mail.account.email_address')"
      label-for="email"
      required
    >
      <IFormInput
        v-model="form.email"
        name="email"
        :disabled="!isCreateView"
        spellcheck="false"
        autocomplete="off"
        type="email"
      >
      </IFormInput>

      <IFormError :error="form.getError('email')" />
    </IFormGroup>

    <IFormGroup
      v-if="useAlias"
      :label="$t('mailclient::mail.account.enter_alias')"
      label-for="alias_email"
      :description="$t('mailclient::mail.account.use_aliass_info')"
    >
      <IFormInput
        v-model="form.alias_email"
        name="alias_email"
        spellcheck="false"
        autocomplete="off"
        type="email"
      >
      </IFormInput>

      <IFormError :error="form.getError('email')" />
    </IFormGroup>

    <IFormGroup>
      <IFormCheckbox
        v-if="!isCreateView"
        id="use_alias"
        v-model:checked="useAlias"
        name="use_alias"
        :label="$t('mailclient::mail.account.use_aliass')"
        @change="form.alias_email = null"
      />

      <IFormCheckbox
        id="create_contact"
        v-model:checked="form.create_contact"
        name="create_contact"
        :label="$t('mailclient::mail.account.create_contact')"
      />
    </IFormGroup>
  </div>

  <div
    v-show="shouldShowServerConfigureableFields || isCreateView"
    :class="{
      'pointer-events-none blur-sm': shouldBlurServerConfigureableFields,
    }"
  >
    <IFormGroup
      :label="$t('mailclient::mail.account.password')"
      label-for="password"
      required
    >
      <IFormInput
        v-model="form.password"
        :placeholder="form.id ? '•••••••••••' : ''"
        spellcheck="false"
        autocomplete="new-password"
        type="password"
      >
      </IFormInput>

      <IFormError :error="form.getError('password')" />
    </IFormGroup>

    <IFormGroup
      label-for="username"
      :label="$t('mailclient::mail.account.username')"
      optional
    >
      <IFormInput
        id="username"
        v-model="form.username"
        autocomplete="off"
        spellcheck="false"
        name="username"
      >
      </IFormInput>
    </IFormGroup>

    <div class="mb-3 mt-4">
      <h5
        v-t="'mailclient.mail.account.incoming_mail'"
        class="mb-3 font-medium text-neutral-700 dark:text-neutral-100"
      />

      <IFormGroup
        required
        :label="$t('mailclient::mail.account.server')"
        label-for="imap_server"
      >
        <IFormInput
          v-model="form.imap_server"
          name="imap_server"
          placeholder="imap.example.com"
          spellcheck="false"
          autocomplete="off"
        >
        </IFormInput>

        <IFormError :error="form.getError('imap_server')" />
      </IFormGroup>

      <div class="grid grid-cols-6 gap-6">
        <div class="col-span-2">
          <IFormGroup
            :label="$t('mailclient::mail.account.port')"
            label-for="imap_port"
            required
          >
            <IFormInput
              v-model="form.imap_port"
              name="imap_port"
              type="number"
              autocomplete="off"
            >
            </IFormInput>

            <IFormError :error="form.getError('imap_port')" />
          </IFormGroup>
        </div>

        <div class="col-span-4">
          <IFormGroup
            :label="$t('mailclient::mail.account.encryption')"
            label-for="imap_encryption"
          >
            <ICustomSelect
              v-model="form.imap_encryption"
              :options="encryptions"
              :placeholder="$t('mailclient::mail.account.without_encryption')"
            />

            <IFormError :error="form.getError('imap_encryption')" />
          </IFormGroup>
        </div>
      </div>
    </div>

    <h5
      v-t="'mailclient.mail.account.outgoing_mail'"
      class="mb-3 font-medium text-neutral-700 dark:text-neutral-100"
    />

    <IFormGroup
      required
      :label="$t('mailclient::mail.account.server')"
      label-for="smtp_server"
    >
      <IFormInput
        v-model="form.smtp_server"
        name="smtp_server"
        placeholder="smtp.example.com"
        spellcheck="false"
        autocomplete="off"
      >
      </IFormInput>

      <IFormError :error="form.getError('smtp_server')" />
    </IFormGroup>

    <div class="grid grid-cols-6 gap-6">
      <div class="col-span-2">
        <IFormGroup
          :label="$t('mailclient::mail.account.port')"
          label-for="smtp_port"
          required
        >
          <IFormInput
            v-model="form.smtp_port"
            name="smtp_port"
            type="number"
            autocomplete="off"
          >
          </IFormInput>

          <IFormError :error="form.getError('smtp_port')" />
        </IFormGroup>
      </div>

      <div class="col-span-4">
        <IFormGroup
          :label="$t('mailclient::mail.account.encryption')"
          label-for="smtp_encryption"
        >
          <ICustomSelect
            v-model="form.smtp_encryption"
            :options="encryptions"
            :placeholder="$t('mailclient::mail.account.without_encryption')"
          />

          <IFormError :error="form.getError('smtp_encryption')" />
        </IFormGroup>
      </div>
    </div>

    <IFormGroup>
      <IFormCheckbox
        id="validate_cert"
        v-model:checked="form.validate_cert"
        name="validate_cert"
        :label="$t('mailclient::mail.account.allow_non_secure_certificate')"
        :value="0"
        :unchecked-value="1"
      />

      <IFormError :error="form.getError('validate_cert')" />
    </IFormGroup>
  </div>
  <!-- Outlook account from custom header not working -->
  <div
    :class="{
      'pointer-events-none blur-sm': shouldBlurServerConfigureableFields,
      hidden: form.connection_type === 'Outlook',
    }"
  >
    <h5
      v-t="'mailclient.mail.from_header'"
      class="mb-3 font-medium text-neutral-700 dark:text-neutral-100"
    />

    <div
      class="mb-3 rounded-md border border-neutral-200 p-3 dark:border-neutral-600"
    >
      <IFormGroup
        :label="$t('mailclient::mail.from_name')"
        :description="
          $t('mailclient::mail.placeholders_info', {
            placeholders: '{agent}, {company}',
          })
        "
      >
        <IFormInput
          v-model="form.from_name_header"
          name="from_name_header"
          autocomplete="off"
        >
        </IFormInput>

        <IFormError :error="form.getError('from_name_header')" />
      </IFormGroup>

      <IFormGroup>
        <p
          v-t="'core::app.preview'"
          class="mb-1 font-medium text-neutral-700 dark:text-neutral-100"
        />

        <p
          v-t="'mailclient.mail.from_header_info'"
          class="mb-2 text-sm text-neutral-700 dark:text-neutral-300"
        />

        <div
          class="rounded-md border border-neutral-200 p-3 dark:border-neutral-600"
        >
          <div class="flex items-center">
            <div class="mr-4">
              <Icon
                icon="Mail"
                class="size-6 text-neutral-600 dark:text-neutral-300"
              />
            </div>

            <div>
              <h6
                class="font-medium text-neutral-800 dark:text-neutral-100"
                v-text="parsedFromNameHeader"
              />

              <p
                v-show="form.email"
                class="text-neutral-700 dark:text-neutral-300"
                v-text="'<' + form.email + '>'"
              />
            </div>
          </div>
        </div>
      </IFormGroup>
    </div>
  </div>

  <div
    v-show="shouldShowServerConfigureableFields"
    :class="{
      'pointer-events-none blur-sm': shouldBlurServerConfigureableFields,
    }"
  >
    <IFormGroup
      v-if="testConnectionForm.errors && testConnectionForm.errors.any()"
    >
      <IAlert variant="danger" class="mt-3" show>
        <p
          v-for="(error, field) in testConnectionForm.errors.all()"
          :key="field"
          class="mb-1"
          v-text="testConnectionForm.getError(field)"
        />
      </IAlert>
    </IFormGroup>
  </div>

  <div v-if="account">
    <EmailAccountsFormFolderTypeSelect
      v-model="form.sent_folder_id"
      :form="form"
      :folders="account.folders"
      field="sent_folder_id"
      :required="true"
      :label="$t('mailclient::mail.account.select_sent_folder')"
    />

    <EmailAccountsFormFolderTypeSelect
      v-model="form.trash_folder_id"
      :form="form"
      :required="true"
      :folders="account.folders"
      field="trash_folder_id"
      :label="$t('mailclient::mail.account.select_trash_folder')"
    />
  </div>

  <IFormGroup
    v-if="foldersFetched"
    :label="$t('mailclient::mail.account.active_folders')"
  >
    <EmailAccountsFormFolders :folders="form.folders" />
  </IFormGroup>
</template>

<!-- eslint-disable vue/no-mutating-props -->
<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'
import { watchOnce } from '@vueuse/core'
import find from 'lodash/find'
import reject from 'lodash/reject'

import { useApp } from '@/Core/composables/useApp'
import { useDates } from '@/Core/composables/useDates'
import OAuthAccount from '@/Core/views/OAuth/OAuthAccount.vue'

import EmailAccountsFormFolders from './EmailAccountsFormFolders.vue'
import EmailAccountsFormFolderTypeSelect from './EmailAccountsFormFolderTypeSelect.vue'

const props = defineProps({
  account: Object,
  type: { required: true, type: String },
  form: { required: true, type: Object },
  testConnectionForm: { required: true, type: Object },
})

const emit = defineEmits(['submit', 'ready', 'configError'])

const { t } = useI18n()
const store = useStore()

const {
  scriptConfig,
  currentUser,
  setting,
  isMicrosoftGraphConfigured,
  isGoogleApiConfigured,
} = useApp()

const { localizedDate, UTCDateTimeInstance } = useDates()

const initialSyncOptions = [
  {
    text: t('mailclient::mail.account.sync_period_now'),
    value: UTCDateTimeInstance.toISO(),
  },
  {
    text: t('mailclient::mail.account.sync_period_1_month_ago'),
    value: UTCDateTimeInstance.minus({ months: 1 }).toISO(),
  },
  {
    text: t('mailclient::mail.account.sync_period_3_months_ago'),
    value: UTCDateTimeInstance.minus({ months: 3 }).toISO(),
  },
  {
    text: t('mailclient::mail.account.sync_period_6_months_ago'),
    value: UTCDateTimeInstance.minus({ months: 6 }).toISO(),
    warning: true,
  },
]

const useAlias = ref(false)

watchOnce(
  () => props.account?.alias_email,
  newVal => {
    if (newVal) {
      useAlias.value = true
    }
  }
)

const encryptions = scriptConfig('mail.accounts.encryptions')
const accountTypes = scriptConfig('mail.accounts.connections')

const connectedUserOAuthAccounts = ref([])
const accountConfigError = ref(null)

const accounts = computed(() => store.getters['emailAccounts/accounts'])

/**
 * Get all the user not connected OAuth accounts to email account
 */
const notConnectedOAuthAccounts = computed(() =>
  reject(connectedUserOAuthAccounts.value, account =>
    find(accounts.value, ['email', account.email])
  )
)

/**
 * Check whether the user has OAuth accounts that are not connected as email account
 */
const hasHangingOAuthAccounts = computed(
  () => notConnectedOAuthAccounts.value.length > 0
)

const showInitialSyncOptionWarning = computed(() => {
  let option = find(initialSyncOptions, ['value', props.form.initial_sync_from])

  if (option) {
    return option.warning === true
  }

  return false
})

/**
 * Get the FROM NAME header for the preview
 */
const parsedFromNameHeader = computed(() => {
  if (!props.form.from_name_header) {
    return ''
  }

  return props.form.from_name_header
    .replace('{agent}', currentUser.value.name)
    .replace('{company}', setting('company_name') || '')
})

/**
 * Check whether the form is for create
 */
const isCreateView = computed(() => !props.account)

/**
 * Check whether the selected acount is IMAP
 */
const isImapAccount = computed(() => props.form.connection_type === 'Imap')

/**
 * Check whether the server configurable fields should be blurred
 */
const shouldBlurServerConfigureableFields = computed(
  () => isCreateView.value && !isImapAccount.value
)

/**
 * Check whether the server configurable fields should be hidden
 */
const shouldShowServerConfigureableFields = computed(() => isImapAccount.value)

/**
 * Check whether the IMAP account folders are fetched
 */
const foldersFetched = computed(() => {
  if (!props.form.folders) {
    return false
  }

  return props.form.folders.length > 0
})

function setAccountConfigError(error) {
  accountConfigError.value = error
}

/**
 * Handle account connection type changes
 *
 * @param  {String} val
 *
 * @return {Void}
 */
function handleAccountConnectionTypeChange(val) {
  setAccountConfigError(null)

  if (val == 'Outlook' && !isMicrosoftGraphConfigured()) {
    setAccountConfigError(`Microsoft application not configured,
                        you must <a href="/settings/integrations/microsoft" rel="noopener noreferrer" target="_blank" class="font-medium underline text-danger-700 hover:text-danger-600 focus:outline-none">configure</a> your
                        Microsoft application in order to connect Outlook mail client.`)
  } else if (val == 'Gmail' && !isGoogleApiConfigured()) {
    setAccountConfigError(`Google application project not configured,
                        you must <a href="/settings/integrations/google" rel="noopener noreferrer" target="_blank" class="font-medium underline text-danger-700 hover:text-danger-600 focus:outline-none">configure</a> your
                        Google application project in order to connect Gmail mail client.`)
  } else if (val === 'Imap' && !scriptConfig('requirements.imap')) {
    setAccountConfigError(
      `In order to use IMAP account type, you will need to enable the PHP extension "imap".`
    )
  }
}

/**
 * Retrieve the oAuth accounts for the user
 *
 * @return {Void}
 */
function retrieveUserConnectedOAuthAccounts() {
  Innoclapps.request('oauth/accounts').then(
    ({ data }) => (connectedUserOAuthAccounts.value = data)
  )
}

/**
 * Connect the existing OAuth account
 *
 * @param  {Object} account
 *
 * @return {Void}
 */
function connectExistingOAuthAccount(account) {
  switch (account.type) {
    case 'microsoft':
      props.form.fill('connection_type', 'Outlook')
      break
    case 'google':
      props.form.fill('connection_type', 'Gmail')
      break
    default:
      props.form.fill('connection_type', account.type)
  }

  emit('submit')
}

onMounted(() => {
  retrieveUserConnectedOAuthAccounts()
  emit('ready')
})

defineExpose({ initialSyncOptions })
</script>
