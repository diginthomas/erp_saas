<template>
  <ITab
    v-memo="[resource.unread_emails_for_user_count]"
    :badge="resource.unread_emails_for_user_count"
    badge-variant="info"
    :title="$t('mailclient::mail.emails')"
    icon="Mail"
  />
</template>

<script setup>
defineProps({
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: [String, Number] },
  resource: { required: true, type: Object },
})
</script>
