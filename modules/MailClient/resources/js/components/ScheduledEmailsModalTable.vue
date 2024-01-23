<template>
  <IModal
    :title="$t('mailclient::schedule.scheduled_emails')"
    size="xxl"
    hide-footer
  >
    <div
      class="overflow-hidden rounded-md border-x border-t border-neutral-200 dark:border-neutral-800"
    >
      <ScheduledEmailsTable
        :request-query-string="requestQueryString"
        @sent="$emit('sent', $event)"
        @deleted="$emit('deleted', $event)"
      />
    </div>
  </IModal>
</template>

<script setup>
import ScheduledEmailsTable from './ScheduledEmailsTable.vue'

defineProps({ requestQueryString: Object })

defineEmits(['sent', 'deleted'])
</script>
