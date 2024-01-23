<template>
  <IAlert
    v-if="isSyncDisabled"
    class="mb-4 border border-warning-200"
    variant="warning"
  >
    <p v-text="account.sync_state_comment" />

    <ILink
      :to="{ name: 'email-accounts-index' }"
      variant="warning"
      class="font-medium"
    >
      {{ $t('mailclient::mail.account.manage') }}
      <span aria-hidden="true">&rarr;</span>
    </ILink>
  </IAlert>

  <IAlert
    v-if="!hasPrimaryAccount && totalAccounts > 1"
    class="mb-4 border border-warning-200"
    variant="warning"
  >
    <p v-t="'mailclient::mail.account.missing_primary_account'" />

    <ILink
      :to="{ name: 'email-accounts-index' }"
      variant="warning"
      class="font-medium"
    >
      {{ $t('mailclient::mail.account.manage') }}
      <span aria-hidden="true">&rarr;</span>
    </ILink>
  </IAlert>

  <IAlert
    v-if="!account.sent_folder_id || !account.sent_folder"
    class="mb-4 border border-warning-200"
    variant="warning"
  >
    <p v-t="'mailclient::mail.account.missing_sent_folder'" />

    <ILink
      :to="{ name: 'edit-email-account', params: { id: account.id } }"
      variant="warning"
      class="font-medium"
    >
      {{ $t('mailclient::mail.account.edit') }}
      <span aria-hidden="true">&rarr;</span>
    </ILink>
  </IAlert>

  <IAlert
    v-if="!account.trash_folder_id || !account.trash_folder"
    class="mb-4 border border-warning-200"
    variant="warning"
  >
    <p v-t="'mailclient::mail.account.missing_trash_folder'" />

    <ILink
      :to="{ name: 'edit-email-account', params: { id: account.id } }"
      variant="warning"
      class="font-medium"
    >
      {{ $t('mailclient::mail.account.edit') }}
      <span aria-hidden="true">&rarr;</span>
    </ILink>
  </IAlert>
</template>

<script setup>
defineProps({
  account: { type: Object, required: true },
  totalAccounts: { required: true, type: Number },
  hasPrimaryAccount: { required: true, type: Boolean },
  isSyncDisabled: { required: true, type: Boolean },
})
</script>
