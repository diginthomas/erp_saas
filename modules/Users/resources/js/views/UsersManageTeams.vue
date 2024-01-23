<template>
  <ICard
    :header="$t('users::team.teams')"
    no-body
    :overlay="teamsAreBeingFetched"
  >
    <template #actions>
      <IButton
        v-show="hasTeams"
        icon="Plus"
        :text="$t('users::team.add')"
        @click="teamIsBeingCreated = true"
      />
    </template>

    <ul
      v-if="hasTeams"
      role="list"
      class="divide-y divide-neutral-200 dark:divide-neutral-700"
    >
      <li v-for="team in teamsByName" :key="team.id">
        <ILink
          class="group block hover:bg-neutral-50 dark:hover:bg-neutral-800/60"
          plain
          @click="
            teamContentIsVisible[team.id] = !teamContentIsVisible[team.id]
          "
        >
          <div class="flex items-center px-4 py-4 sm:px-6">
            <div
              class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between"
            >
              <div class="truncate">
                <div class="flex items-center text-sm">
                  <p
                    class="truncate font-medium text-primary-600 dark:text-primary-100"
                    v-text="team.name"
                  />

                  <ILink
                    :text="$t('core::app.edit')"
                    class="ml-2 md:hidden md:group-hover:block"
                    @click.stop="prepareEdit(team)"
                  />

                  <ILink
                    :text="$t('core::app.delete')"
                    class="ml-2 md:hidden md:group-hover:block"
                    variant="danger"
                    @click.stop="$confirm(() => destroy(team.id))"
                  />
                </div>

                <div class="mt-2 flex">
                  <div
                    class="flex items-center text-sm text-neutral-500 dark:text-neutral-400"
                  >
                    <Icon
                      icon="Calendar"
                      class="mr-1.5 size-5 shrink-0 text-neutral-400 dark:text-neutral-300"
                    />

                    <p>
                      {{ $t('core::app.created_at') }}
                      {{ ' ' }}
                      <time :datetime="team.created_at">
                        {{ localizedDateTime(team.created_at) }}
                      </time>
                    </p>
                  </div>
                </div>
              </div>

              <div class="mt-4 shrink-0 sm:ml-5 sm:mt-0">
                <div class="flex -space-x-1 overflow-hidden">
                  <IAvatar
                    v-for="member in team.members"
                    :key="member.email"
                    v-i-tooltip="member.name"
                    :alt="member.name"
                    size="xs"
                    :src="member.avatar_url"
                    class="ring-2 ring-white dark:ring-neutral-900"
                  />
                </div>
              </div>
            </div>

            <div class="ml-5 shrink-0">
              <Icon icon="ChevronRight" class="size-5 text-neutral-400" />
            </div>
          </div>
        </ILink>

        <div v-show="teamContentIsVisible[team.id]" class="px-4 py-4 sm:px-6">
          <p
            v-t="'users::team.manager'"
            class="mb-1 text-sm font-medium text-neutral-800 dark:text-neutral-200"
          />

          <p
            class="mb-3 text-sm text-neutral-700 dark:text-neutral-300"
            v-text="team.manager.name"
          />

          <p
            v-t="'users::team.members'"
            class="my-2 text-sm font-medium text-neutral-800 dark:text-neutral-200"
          />

          <div
            v-for="member in team.members"
            :key="'info-' + member.email"
            class="mb-1 flex items-center space-x-1.5 last:mb-0"
          >
            <IAvatar :alt="member.name" size="xs" :src="member.avatar_url" />

            <p
              class="text-sm font-medium text-neutral-700 dark:text-neutral-300"
              v-text="member.name"
            />
          </div>

          <p
            v-show="team.description"
            v-t="'users::team.description'"
            class="mb-1 mt-3 text-sm font-medium text-neutral-800 dark:text-neutral-200"
          />

          <p
            class="text-sm text-neutral-700 dark:text-neutral-300"
            v-text="team.description"
          />
        </div>
      </li>
    </ul>

    <ICardBody v-else-if="!teamsAreBeingFetched">
      <IEmptyState
        :button-text="$t('users::team.add')"
        :title="$t('users::team.empty_state.title')"
        :description="$t('users::team.empty_state.description')"
        @click="teamIsBeingCreated = true"
      />
    </ICardBody>
  </ICard>

  <IModal
    :title="$t('users::team.create')"
    form
    :visible="teamIsBeingCreated"
    :ok-text="$t('core::app.create')"
    :ok-disabled="formCreate.busy"
    @hidden="teamIsBeingCreated = false"
    @submit="create"
    @shown="() => $refs.nameInputCreateRef.focus()"
  >
    <IFormGroup for="nameInputCreate" :label="$t('users::team.name')" required>
      <IFormInput
        id="nameInputCreate"
        ref="nameInputCreateRef"
        v-model="formCreate.name"
      />

      <IFormError :error="formCreate.getError('name')" />
    </IFormGroup>

    <IFormGroup label-for="user_id" :label="$t('users::team.manager')" required>
      <ICustomSelect
        v-model="formCreate.user_id"
        :options="users"
        :clearable="false"
        label="name"
        :reduce="user => user.id"
        input-id="user_id"
      />

      <IFormError :error="formCreate.getError('user_id')" />
    </IFormGroup>

    <IFormGroup for="membersInputCreate" :label="$t('users::team.members')">
      <ICustomSelect
        id="membersInputCreate"
        v-model="formCreate.members"
        :options="users"
        label="name"
        multiple
        :reduce="option => option.id"
      />
    </IFormGroup>

    <IFormGroup
      for="descriptionInputCreate"
      :label="$t('users::team.description')"
    >
      <IFormTextarea
        id="descriptionInputCreate"
        v-model="formCreate.description"
      />

      <IFormError :error="formCreate.getError('description')" />
    </IFormGroup>
  </IModal>

  <IModal
    form
    :visible="teamIsBeingEdited !== null"
    :ok-text="$t('core::app.save')"
    :ok-disabled="formUpdate.busy"
    :title="$t('users::team.edit')"
    @hidden=";(teamIsBeingEdited = null), formUpdate.reset()"
    @submit="update"
  >
    <IFormGroup for="nameInputEdit" :label="$t('users::team.name')" required>
      <IFormInput
        id="nameInputEdit"
        ref="nameInputEdit"
        v-model="formUpdate.name"
      />

      <IFormError :error="formUpdate.getError('name')" />
    </IFormGroup>

    <IFormGroup label-for="user_id" :label="$t('users::team.manager')" required>
      <ICustomSelect
        v-model="formUpdate.user_id"
        :options="users"
        :clearable="false"
        label="name"
        :reduce="user => user.id"
        input-id="user_id"
      />

      <IFormError :error="formUpdate.getError('user_id')" />
    </IFormGroup>

    <IFormGroup for="membersInputEdit" :label="$t('users::team.members')">
      <ICustomSelect
        id="membersInputEdit"
        v-model="formUpdate.members"
        :options="users"
        label="name"
        multiple
        :reduce="option => option.id"
      />
    </IFormGroup>

    <IFormGroup
      for="descriptionInputEdit"
      :label="$t('users::team.description')"
    >
      <IFormTextarea
        id="descriptionInputEdit"
        v-model="formUpdate.description"
      />

      <IFormError :error="formUpdate.getError('description')" />
    </IFormGroup>
  </IModal>
</template>

<script setup>
import { computed, onBeforeMount, ref } from 'vue'
import { useStore } from 'vuex'

import { useApp } from '@/Core/composables/useApp'
import { useDates } from '@/Core/composables/useDates'
import { useForm } from '@/Core/composables/useForm'

import { useTeams } from '../composables/useTeams'

const store = useStore()

const { users } = useApp()

const { localizedDateTime } = useDates()

const { teamsByName, teamsAreBeingFetched, addTeam, deleteTeam, setTeam } =
  useTeams()

const teamIsBeingCreated = ref(false)
const teamIsBeingEdited = ref(null)
const modificationsPerformed = ref(false)
const teamContentIsVisible = ref({})

const { form: formCreate } = useForm(
  {
    name: null,
    description: null,
    user_id: null,
    members: [],
  },
  { resetOnSuccess: true }
)

const { form: formUpdate } = useForm(
  {
    name: null,
    description: null,
    user_id: null,
    members: [],
  },
  { resetOnSuccess: true }
)

const hasTeams = computed(() => teamsByName.value.length > 0)

function create() {
  formCreate.post('/teams').then(team => {
    addTeam(team)
    teamIsBeingCreated.value = false
    modificationsPerformed.value = true
  })
}

function update() {
  formUpdate.put(`/teams/${teamIsBeingEdited.value}`).then(team => {
    setTeam(team.id, team)
    teamIsBeingEdited.value = null
    modificationsPerformed.value = true
  })
}

async function destroy(id) {
  await deleteTeam(id)

  modificationsPerformed.value = true
}

function prepareEdit(team) {
  teamIsBeingEdited.value = team.id
  formUpdate.fill('name', team.name)
  formUpdate.fill('user_id', team.user_id)

  formUpdate.fill(
    'members',
    team.members.map(member => member.id)
  )
  formUpdate.fill('description', team.description)
}

onBeforeMount(() => {
  if (modificationsPerformed.value) {
    store.commit('table/RESET_SETTINGS')
  }
})
</script>
