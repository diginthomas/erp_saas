<template>
  <MainLayout>
    <div class="mx-auto max-w-5xl">
      <ICard
        no-body
        :header="$t('core::api.personal_access_tokens')"
        :overlay="tokensAreBeingLoaded"
      >
        <template #actions>
          <IButton
            v-show="totalTokens > 0"
            icon="plus"
            :text="$t('core::api.create_token')"
            @click="showCreateTokenForm"
          />
        </template>

        <ITable v-if="totalTokens > 0" bordered-inner="y">
          <thead>
            <tr>
              <th v-t="'core::api.token_name'" class="text-left" />

              <th v-t="'core::api.token_last_used'" class="text-left" />

              <th v-t="'core::app.created_at'" class="text-left" />

              <th class="text-left" />
            </tr>
          </thead>

          <tbody>
            <tr v-for="token in tokens" :key="token.id">
              <td class="font-medium" v-text="token.name" />

              <td>
                {{
                  token.last_used_at
                    ? localizedDateTime(token.last_used_at)
                    : 'N/A'
                }}
              </td>

              <td>
                {{ localizedDateTime(token.created_at) }}
              </td>

              <td class="text-right">
                <IButton
                  variant="danger"
                  icon="Trash"
                  :text="$t('core::api.revoke_token')"
                  @click="$confirm(() => revoke(token))"
                />
              </td>
            </tr>
          </tbody>
        </ITable>

        <ICardBody v-else-if="!tokensAreBeingLoaded">
          <IEmptyState
            :title="$t('core::api.no_tokens')"
            :button-text="$t('core::api.create_token')"
            :description="$t('core::api.empty_state.description')"
            @click="showCreateTokenForm"
          />
        </ICardBody>
      </ICard>

      <IModal
        v-model:visible="showCreateTokenModal"
        form
        size="sm"
        :ok-disabled="form.busy"
        :ok-text="$t('core::app.create')"
        :title="$t('core::api.create_token')"
        @shown="() => $refs.createTokenNameRef.focus()"
        @submit="create"
      >
        <IFormGroup
          :label="$t('core::api.token_name')"
          label-for="name"
          required
        >
          <IFormInput
            id="name"
            ref="createTokenNameRef"
            v-model="form.name"
            name="name"
          />

          <IFormError :error="form.getError('name')" />
        </IFormGroup>
      </IModal>

      <IModal
        v-model:visible="showAccessTokenModal"
        static-backdrop
        hide-footer
        :title="$t('core::api.personal_access_token')"
      >
        <p
          v-t="'core::api.after_token_created_info'"
          class="mb-5 leading-tight text-warning-600"
        />

        <p
          class="select-all break-all rounded-md border border-neutral-300 px-3 py-1.5 text-neutral-900 dark:text-neutral-200"
          v-text="plainTextToken"
        />
      </IModal>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed, onBeforeMount, ref, shallowReactive } from 'vue'
import { useRouter } from 'vue-router'

import { useApp } from '@/Core/composables/useApp'
import { useDates } from '@/Core/composables/useDates'
import { useForm } from '@/Core/composables/useForm'

const plainTextToken = ref(null)
const showAccessTokenModal = ref(false)
const showCreateTokenModal = ref(false)
const tokensAreBeingLoaded = ref(false)
const tokens = shallowReactive([])

const { form } = useForm({ name: '' }, { resetOnSuccess: true })
const { localizedDateTime } = useDates()
const { currentUser } = useApp()
const router = useRouter()

const totalTokens = computed(() => tokens.length)

function prepareComponent() {
  getTokens()
}

function getTokens() {
  tokensAreBeingLoaded.value = true

  Innoclapps.request('/personal-access-tokens')
    .then(response => {
      tokens.push(...response.data)
    })
    .finally(() => (tokensAreBeingLoaded.value = false))
}

function showCreateTokenForm() {
  showCreateTokenModal.value = true
}

function create() {
  plainTextToken.value = null

  form.post('/personal-access-tokens').then(response => {
    tokens.push(response.accessToken)
    showAccessToken(response.plainTextToken)
  })
}

function showAccessToken(token) {
  showCreateTokenModal.value = false
  plainTextToken.value = token
  showAccessTokenModal.value = true
}

async function revoke(token) {
  await Innoclapps.request().delete(`/personal-access-tokens/${token.id}`)

  tokens.splice(
    tokens.findIndex(t => t.id === token.id),
    1
  )
}

onBeforeMount(() => {
  if (!currentUser.value.access_api) {
    router.push({ path: '/403' })
  } else {
    prepareComponent()
  }
})
</script>
