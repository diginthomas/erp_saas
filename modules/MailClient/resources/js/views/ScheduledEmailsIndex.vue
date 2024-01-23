<template>
  <MainLayout>
    <ICard
      class="mx-auto max-w-6xl"
      :header="$t('mailclient::schedule.scheduled_emails')"
      no-body
    >
      <ScheduledEmailsTable />
    </ICard>
  </MainLayout>
</template>

<script setup>
import ScheduledEmailsTable from '../components/ScheduledEmailsTable.vue'
</script>
