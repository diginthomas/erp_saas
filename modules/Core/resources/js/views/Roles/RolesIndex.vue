<template>
  <ICard
    :header="$t('core::role.roles')"
    no-body
    :overlay="rolesAreBeingFetched"
  >
    <template #actions>
      <IButton
        v-show="hasRoles"
        icon="Plus"
        :to="{ name: 'create-role' }"
        :text="$t('core::role.create')"
      />
    </template>

    <ITable v-if="hasRoles" bordered-inner="y">
      <thead>
        <tr>
          <th v-t="'core::app.id'" class="text-left" width="5%" />

          <th v-t="'core::role.name'" class="text-left" />

          <th class="text-left" />
        </tr>
      </thead>

      <tbody>
        <tr v-for="role in rolesByName" :key="role.id">
          <td v-text="role.id" />

          <td>
            <ILink
              :to="{ name: 'edit-role', params: { id: role.id } }"
              :text="role.name"
            />
          </td>

          <td class="flex justify-end">
            <IMinimalDropdown>
              <IDropdownItem
                :to="{ name: 'edit-role', params: { id: role.id } }"
                :text="$t('core::app.edit')"
              />

              <IDropdownItem
                :text="$t('core::app.delete')"
                @click="$confirm(() => destroy(role.id))"
              />
            </IMinimalDropdown>
          </td>
        </tr>
      </tbody>
    </ITable>

    <ICardBody v-else-if="!rolesAreBeingFetched">
      <IEmptyState
        :to="{ name: 'create-role' }"
        :button-text="$t('core::role.create')"
        :title="$t('core::role.empty_state.title')"
        :description="$t('core::role.empty_state.description')"
      />
    </ICardBody>
  </ICard>

  <RouterView />
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

import { useRoles } from '../../composables/useRoles'

const { t } = useI18n()

const { rolesByName, rolesAreBeingFetched, deleteRole } = useRoles()

const hasRoles = computed(() => rolesByName.value.length > 0)

async function destroy(id) {
  await deleteRole(id)

  Innoclapps.success(t('core::role.deleted'))
}
</script>
