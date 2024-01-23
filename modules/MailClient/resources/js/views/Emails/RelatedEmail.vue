<template>
  <ICard
    v-observe-visibility="{
      callback: handleVisibilityChanged,
      once: true,
      throttle: 300,
      intersection: {
        threshold: 0.5,
      },
    }"
    :class="'email-' + email.id"
    condensed
  >
    <template #header>
      <div class="relative sm:flex">
        <div class="grow">
          <h3
            :class="[
              'truncate whitespace-normal text-base/6 font-medium text-neutral-700 dark:text-white',
              !email.is_read ? 'font-bold' : 'font-semibold',
            ]"
          >
            <span
              v-once
              v-text="
                email.subject
                  ? email.subject
                  : '(' + $t('mailclient::inbox.no_subject') + ')'
              "
            />
          </h3>

          <div class="inline-flex items-center space-x-3">
            <AssociationsPopover
              :associations-count="email.associations_count"
              :initial-associateables="relatedResource"
              :resource-id="email.id"
              :resource-name="resourceName"
              :primary-record="relatedResource"
              :primary-resource-name="viaResource"
              @synced="synchronizeResource({ emails: $event })"
            />

            <TagsSelectInput
              :simple="true"
              :type="TAGS_TYPE"
              class="!w-auto"
              :model-value="email.tags"
              :disabled="!email.authorizations?.update"
              @update:model-value="syncMessageTags"
            />
          </div>
        </div>

        <div class="flex shrink-0 items-center self-start sm:ml-3">
          <div class="mr-3 flex items-center space-x-2">
            <IButton
              v-once
              variant="white"
              class="!px-1.5 !py-1"
              icon="Reply"
              icon-class="size-4"
              :text="$t('mailclient::inbox.reply')"
              @click="reply(true)"
            />

            <IButton
              v-if="hasMoreReplyTo"
              variant="white"
              class="!px-1.5 !py-1"
              icon="Reply"
              icon-class="size-4"
              :text="$t('mailclient::inbox.reply_all')"
              @click="replyAll"
            />

            <IButton
              v-once
              variant="white"
              class="!px-1.5 !py-1"
              icon="Share"
              icon-class="size-4"
              :text="$t('mailclient::inbox.forward')"
              @click="forward(true)"
            />
          </div>

          <IMinimalDropdown class="mt-px self-center">
            <IDropdownItem
              :text="$t('mailclient::mail.view')"
              icon="Eye"
              :to="{
                name: 'inbox-message',
                params: {
                  id: email.id,
                  account_id: email.email_account_id,
                  folder_id: email.folders[0].id,
                },
              }"
            />

            <IDropdownItem
              icon="Trash"
              :text="$t('core::app.delete')"
              @click="$confirm(destroy)"
            />
          </IMinimalDropdown>
        </div>

        <IBadge
          v-if="email.opened_at"
          v-once
          class="absolute -bottom-6 right-0"
          variant="success"
        >
          <span class="flex">
            <Icon icon="Eye" class="mr-1 size-4" />

            <span class="mr-1"> ({{ email.opens }}) - </span>
            {{ localizedDateTime(email.opened_at) }}
          </span>
        </IBadge>
      </div>
    </template>

    <MessageAddresses
      v-once
      :label="$t('mailclient::inbox.from')"
      :addresses="email.from"
    />

    <div class="flex">
      <MessageAddresses
        v-once
        :label="$t('mailclient::inbox.to')"
        :addresses="email.to"
      />

      <div class="-mt-0.5 ml-3">
        <IPopover placement="top" class="w-72">
          <button type="button" class="link link-primary">
            {{ $t('core::app.details') }}
            <span aria-hidden="true">&rarr;</span>
          </button>

          <template #popper>
            <div class="flex flex-col px-4 py-2">
              <MessageAddresses
                v-once
                :label="$t('mailclient::inbox.from')"
                :addresses="email.from"
              />

              <MessageAddresses
                v-once
                :label="$t('mailclient::inbox.to')"
                :addresses="email.to"
              />

              <MessageAddresses
                v-once
                :label="$t('mailclient::inbox.reply_to')"
                :addresses="email.reply_to"
                :show-when-empty="false"
              />

              <MessageAddresses
                v-once
                :label="$t('mailclient::inbox.cc')"
                :addresses="email.cc"
                :show-when-empty="false"
              />

              <MessageAddresses
                v-once
                :label="$t('mailclient::inbox.bcc')"
                :addresses="email.bcc"
                :show-when-empty="false"
              />
            </div>
          </template>
        </IPopover>
      </div>
    </div>

    <div v-once class="text-sm">
      <span class="mr-1 font-semibold text-neutral-800 dark:text-neutral-100">
        {{ $t('mailclient::inbox.date') }}:
      </span>

      <span
        class="text-neutral-700 dark:text-neutral-300"
        v-text="localizedDateTime(email.date)"
      />
    </div>

    <div v-once class="mail-text all-revert">
      <div class="font-sans text-sm leading-[initial] dark:text-white">
        <TextCollapse
          v-if="email.visible_text"
          :text="email.visible_text"
          lightbox
          :length="250"
          class="mt-3"
        />

        <div class="clear-both"></div>

        <HiddenText :text="email.hidden_text" />
      </div>
    </div>

    <div
      v-if="email.media.length > 0"
      v-once
      class="-mx-6 mb-3 mt-4 border-t border-neutral-200 px-6 pt-4 dark:border-neutral-700"
    >
      <dd
        v-t="'mailclient.mail.attachments'"
        class="mb-2 text-sm font-medium leading-6 text-neutral-900 dark:text-neutral-100"
      />

      <MessageAttachments :attachments="email.media" />
    </div>

    <MessageReply
      :message="email"
      :visible="isReplying || isForwarding"
      :forward="isForwarding"
      :resource-name="viaResource"
      :resource-id="viaResourceId"
      :related-resource="relatedResource"
      :to-all="replyToAll"
      @hidden="handleReplyModalHidden"
    />
  </ICard>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import { ObserveVisibility } from 'vue-observe-visibility'
import { useStore } from 'vuex'

import AssociationsPopover from '@/Core/components/AssociationsPopover.vue'
import TagsSelectInput from '@/Core/components/TagsSelectInput.vue'
import { useDates } from '@/Core/composables/useDates'
import { useResourceable } from '@/Core/composables/useResourceable'

import { useMessageTags } from '../../composables/useMessageTags'

import MessageAddresses from './MessageAddresses.vue'
import MessageAttachments from './MessageAttachments.vue'
import HiddenText from './MessageHiddenText.vue'
import MessageReply from './MessageReply.vue'

const props = defineProps({
  viaResource: { type: String, required: true },
  viaResourceId: { type: [String, Number], required: true },
  relatedResource: { type: Object, required: true },
  email: { type: Object, required: true },
})

const resourceName = Innoclapps.resourceName('emails')

const synchronizeResource = inject('synchronizeResource')
const decrementResourceCount = inject('decrementResourceCount')

const vObserveVisibility = ObserveVisibility

const store = useStore()
const { deleteResource } = useResourceable(resourceName)
const { localizedDateTime } = useDates()
const { TAGS_TYPE, syncTags } = useMessageTags()

const isReplying = ref(false)
const isForwarding = ref(false)
const replyToAll = ref(false)

const hasMoreReplyTo = computed(
  () => props.email.cc && props.email.cc.length > 0
)

function handleReplyModalHidden() {
  reply(false)
  forward(false)
}

function handleVisibilityChanged(isVisible) {
  if (isVisible && !props.email.is_read) {
    Innoclapps.request()
      .post(`/emails/${props.email.id}/read`)
      .then(({ data }) => {
        synchronizeResource({ emails: data })
        decrementResourceCount('unread_emails_for_user_count')
        store.dispatch('emailAccounts/decrementUnreadCountUI')
      })
  }
}

async function destroy() {
  await deleteResource(props.email.id)

  if (!props.email.is_read) {
    decrementResourceCount('unread_emails_for_user_count')
    store.dispatch('emailAccounts/decrementUnreadCountUI')
  }

  synchronizeResource({ emails: { id: props.email.id, _delete: true } })
}

function reply(state = true) {
  isReplying.value = state
  replyToAll.value = false
}

function forward(state = true) {
  isForwarding.value = state
}

function replyAll() {
  isReplying.value = true
  replyToAll.value = true
}

/**
 * Sync the message tags.
 */
async function syncMessageTags(tags) {
  let data = await syncTags(props.email.id, tags)
  synchronizeResource({ emails: { id: data.id, tags: { _reset: data.tags } } })
}
</script>
