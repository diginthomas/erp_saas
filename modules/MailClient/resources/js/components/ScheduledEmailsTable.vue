<template>
  <TableSimple
    ref="tableRef"
    table-id="scheduled-emails"
    :fields="fields"
    bordered="y"
    :searchable="false"
    request-uri="emails/scheduled"
  >
    <template #scheduled_at="{ row }">
      {{ localizedDateTime(row.scheduled_at) }}
      <span
        v-if="row.sent_at"
        class="block text-xs text-neutral-500 dark:text-neutral-300"
      >
        {{ $t('mailclient::schedule.sent_at') }}:
        {{ localizedDateTime(row.sent_at) }}
      </span>
    </template>

    <template #status="{ row }">
      <IBadge :variant="statusBadgeVariantMap[row.status]">
        {{ $t('mailclient.schedule.statuses.' + row.status) }}
      </IBadge>
    </template>

    <template #scheduled_by="{ row }">
      {{ row.user.name }}
      <span class="block text-xs text-neutral-500 dark:text-neutral-300">
        {{ localizedDateTime(row.created_at) }}
      </span>
    </template>

    <template #to="{ row }">
      <span
        v-for="to in row.to"
        :key="to.address"
        :title="to.address"
        class="block"
      >
        {{ to.name || to.address }}
      </span>
    </template>

    <template #actions="{ row }">
      <div
        v-show="
          !emailBeingSent || (emailBeingSent && emailBeingSent !== row.id)
        "
      >
        <IMinimalDropdown
          v-if="row.status !== 'sending'"
          :disabled="Boolean(emailBeingSent)"
        >
          <IDropdownItem
            v-if="row.authorizations.delete"
            :text="
              $t(
                row.status === 'pending'
                  ? 'mailclient::schedule.cancel_and_delete'
                  : 'core::app.delete'
              )
            "
            @click="destroy(row.id)"
          />

          <IDropdownItem
            v-if="row.status !== 'sent'"
            :text="$t('mailclient::schedule.send_now')"
            @click="sendNow(row.id)"
          />
        </IMinimalDropdown>
      </div>

      <ISpinner
        v-if="emailBeingSent && emailBeingSent == row.id"
        class="size-4 text-success-500"
      />
    </template>

    <template #before-row="{ row }">
      <tr v-if="row.fail_reason || row.retry_after">
        <td
          colspan="6"
          class="!py-1"
          :class="
            row.retry_after
              ? '!bg-warning-50 !text-warning-800'
              : '!bg-danger-50 !text-danger-800'
          "
        >
          <div v-if="row.retry_after" class="mb-1">
            {{
              $t('mailclient::schedule.will_retry_at', {
                date: localizedDateTime(row.retry_after),
              })
            }}
          </div>

          {{ row.fail_reason }}
        </td>
      </tr>
    </template>
  </TableSimple>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

import TableSimple from '@/Core/components/Table/TableSimple.vue'
import { useDates } from '@/Core/composables/useDates'

const emit = defineEmits(['sent', 'deleted'])

const statusBadgeVariantMap = {
  sent: 'success',
  sending: 'neutral',
  pending: 'info',
  failed: 'danger',
}

const { localizedDateTime } = useDates()
const { t } = useI18n()

const tableRef = ref(null)
const emailBeingSent = ref(null)

const fields = ref([
  {
    key: 'subject',
    label: t('mailclient::inbox.subject'),
    tdClass: 'font-medium',
  },
  { key: 'to', label: t('mailclient::inbox.to') },
  { key: 'scheduled_by', label: t('mailclient::schedule.scheduled_by') },
  {
    key: 'scheduled_at',
    label: t('mailclient::schedule.scheduled_at'),
    sortable: true,
  },
  { key: 'status', label: t('mailclient::schedule.status') },
  { key: 'actions', label: '' },
])

async function sendNow(id) {
  emailBeingSent.value = id
  await Innoclapps.request().post(`/emails/scheduled/${id}/send`)
  emailBeingSent.value = null
  emit('sent', id)
  tableRef.value.reload()
}

async function destroy(id) {
  await Innoclapps.confirm()
  await Innoclapps.request().delete(`/emails/scheduled/${id}`)
  emit('deleted', id)
  tableRef.value.reload()
}
</script>
