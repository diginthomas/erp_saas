<template>
  <IDropdownSelect
    v-if="authorizeUpdate"
    :items="dropdownOptions"
    :model-value="owner?.id"
    value-key="id"
    label-key="name"
    condensed
    @change="$emit('change', $event)"
  >
    <template #label="{ label }">
      <span
        v-if="owner"
        v-i-tooltip.top="$t('deals::fields.deals.user.name')"
        class="inline-flex items-center"
      >
        <IAvatar size="xs" class="mr-1.5" :src="owner.avatar_url" />
        {{ label }}
      </span>

      <span
        v-else
        v-t="'core::app.no_owner'"
        class="text-neutral-500 dark:text-neutral-300"
      />
    </template>
  </IDropdownSelect>

  <p
    v-else-if="owner"
    class="inline-flex items-center text-sm text-neutral-800 dark:text-neutral-200"
  >
    <IAvatar size="xs" class="mr-1.5" :src="owner.avatar_url" />
    {{ owner.name }}
  </p>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

import { useApp } from '@/Core/composables/useApp'

const props = defineProps(['owner', 'authorizeUpdate'])

defineEmits(['change'])

const { users } = useApp()
const { t } = useI18n()

const dropdownOptions = computed(() => {
  if (props.owner) {
    return [
      ...users.value,
      {
        id: null,
        icon: 'X',
        prependIcon: true,
        name: t('core::app.no_owner'),
        class: 'border-t border-neutral-200 dark:border-neutral-700',
      },
    ].map(user => ({
      id: user.id,
      name: user.name,
      class: user.class || undefined,
      prependIcon: user.prependIcon || undefined,
      icon: user.icon || undefined,
    }))
  }

  return users.value
})
</script>
