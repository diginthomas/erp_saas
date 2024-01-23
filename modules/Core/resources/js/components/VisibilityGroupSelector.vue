<template>
  <div>
    <label
      v-t="'core::app.visibility_group.visible_to'"
      class="text-sm font-medium text-neutral-700 dark:text-neutral-100"
    />

    <fieldset class="mt-2">
      <legend class="sr-only">Visibility group</legend>

      <div class="space-y-3 sm:flex sm:items-center sm:space-x-4 sm:space-y-0">
        <IFormRadio
          :model-value="type"
          :disabled="disabled"
          :label="$t('core::app.visibility_group.all')"
          value="all"
          @update:model-value="$emit('update:type', $event)"
          @change="$emit('update:dependsOn', [])"
        />

        <IFormRadio
          :model-value="type"
          :disabled="disabled"
          :label="$t('users::team.teams')"
          value="teams"
          @update:model-value="$emit('update:type', $event)"
          @change="$emit('update:dependsOn', [])"
        />

        <IFormRadio
          :model-value="type"
          :disabled="disabled"
          :label="$t('users::user.users')"
          value="users"
          @update:model-value="$emit('update:type', $event)"
          @change="$emit('update:dependsOn', [])"
        />
      </div>
    </fieldset>

    <div v-show="type === 'users'" class="mt-4">
      <ICustomSelect
        :model-value="dependsOn"
        :options="usersWithoutAdministrators"
        :placeholder="$t('users::user.select')"
        label="name"
        multiple
        :reduce="option => option.id"
        @update:model-value="$emit('update:dependsOn', $event)"
      />

      <span
        class="mt-0.5 block text-right text-xs italic text-neutral-500 dark:text-neutral-300"
      >
        * {{ $t('users::user.admin_users_excluded') }}
      </span>
    </div>

    <div v-show="type === 'teams'" class="mt-4">
      <ICustomSelect
        :model-value="dependsOn"
        :options="teams"
        :placeholder="$t('users::team.select')"
        label="name"
        multiple
        :reduce="option => option.id"
        @update:model-value="$emit('update:dependsOn', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

import { useApp } from '@/Core/composables/useApp'

import { useTeams } from '@/Users/composables/useTeams'

defineProps({ disabled: Boolean, dependsOn: Array, type: String })

defineEmits(['update:type', 'update:dependsOn'])

const { users } = useApp()

const { teamsByName: teams } = useTeams()

const usersWithoutAdministrators = computed(() =>
  users.value.filter(user => !user.super_admin)
)
</script>
