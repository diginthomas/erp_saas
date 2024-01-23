<template>
  <IFormGroup label-for="name" :label="$t('core::role.name')" required>
    <IFormInput
      id="name"
      ref="nameRef"
      :model-value="name"
      name="name"
      type="text"
      @update:model-value="$emit('update:name', $event)"
    />

    <IFormError :error="form.getError('name')" />
  </IFormGroup>

  <IOverlay :show="isLoading" class="mt-5">
    <div v-show="availablePermissions.all.length > 0">
      <h3
        v-t="'core::role.permissions'"
        class="my-4 whitespace-nowrap text-base/6 font-medium text-neutral-800 dark:text-neutral-200"
      />

      <div
        v-for="(group, index) in availablePermissions.grouped"
        :key="index"
        class="mb-4"
      >
        <p
          class="mb-1 text-sm font-medium text-neutral-700 dark:text-neutral-200"
          v-text="group.as"
        />

        <div v-for="view in group.views" :key="view.group">
          <div class="flex justify-between">
            <p class="text-sm text-neutral-600 dark:text-neutral-300">
              {{
                view.as
                  ? view.as
                  : view.single
                    ? view.permissions[view.keys[0]]
                    : ''
              }}
            </p>

            <IDropdown
              placement="bottom-end"
              tag="button"
              type="button"
              class="link link-primary inline-flex items-center"
            >
              <template #toggle-content>
                {{ getSelectedPermissionTextByView(view) }}
              </template>

              <div class="py-1">
                <IDropdownItem
                  v-show="view.revokeable"
                  :text="$t('core::role.revoked')"
                  @click="revokePermission(view)"
                />

                <IDropdownItem
                  v-if="view.single"
                  :text="$t('core::role.granted')"
                  @click="setSelectedPermission(view, view.keys[0])"
                />

                <IDropdownItem
                  v-for="(permission, key) in view.permissions"
                  v-else
                  :key="key"
                  :disabled="permissions.indexOf(key) > -1"
                  :text="permission"
                  @click="setSelectedPermission(view, key)"
                />
              </div>
            </IDropdown>
          </div>
        </div>
      </div>
    </div>
  </IOverlay>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

import { useLoader } from '@/Core/composables/useLoader'

const props = defineProps({
  inCreateMode: Boolean,
  name: { required: true, type: String },
  permissions: { required: true, type: Array },
  form: { required: true, type: Object },
})

const emit = defineEmits(['update:name', 'update:permissions'])

const { t } = useI18n()
const { setLoading, isLoading } = useLoader()

const nameRef = ref(null)
const availablePermissions = ref({ all: [], grouped: {} })

function getSelectedPermissionTextByView(view) {
  // For single view, check the first key's presence in permissions
  if (view.single) {
    return props.permissions.includes(view.keys[0])
      ? t('core::role.granted')
      : t('core::role.revoked')
  }

  // For non-single view, find the first matching permission and return its text
  const selectedPermission = view.keys.find(key =>
    props.permissions.includes(key)
  )

  // For non-single view, find the first matching permission and return its text
  return selectedPermission
    ? view.permissions[selectedPermission]
    : t('core::role.revoked')
}

function setSelectedPermission(view, permissionKey) {
  // Revoke any previously view permissions
  let permissions = revokePermission(view, false)

  emit('update:permissions', [...permissions, permissionKey])
}

function revokePermission(view, emitEvent = true) {
  const newPermissions = props.permissions.filter(
    permission => !view.keys.includes(permission)
  )

  if (emitEvent) {
    emit('update:permissions', newPermissions)
  }

  return newPermissions
}

function setDefaultSelectedPermissions(permissions) {
  const defaultPermissions = Object.values(permissions.grouped).flatMap(group =>
    group.views
      .filter(view => !view.single) // when it's not a single permission, set the first as selected.
      .map(view => Object.keys(view.permissions)[0])
  )

  emit('update:permissions', defaultPermissions)
}

async function fetchAndSetPermissions() {
  setLoading(true)

  try {
    const { data } = await Innoclapps.request('/permissions')
    availablePermissions.value = data

    if (props.inCreateMode) {
      setDefaultSelectedPermissions(data)
    }
  } finally {
    setLoading(false)
  }
}

fetchAndSetPermissions()

defineExpose({ nameRef })
</script>
