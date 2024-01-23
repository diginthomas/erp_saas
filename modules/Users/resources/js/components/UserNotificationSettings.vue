<template>
  <ITable bordered-inner>
    <thead>
      <tr>
        <th
          v-for="channel in allAvailableChannels"
          :key="'heading-' + channel"
          v-i-tooltip="$t('core::notifications.channels.' + channel)"
          class="text-center"
          width="8%"
          scope="col"
        >
          <Icon class="size-5" :icon="iconMaps[channel]" />
        </th>

        <th
          class="text-left"
          width="92%"
          v-text="$t('core::notifications.notification')"
        />
      </tr>
    </thead>

    <tbody>
      <tr v-for="notification in settings" :key="notification.key">
        <td
          v-for="channel in allAvailableChannels"
          :key="channel"
          class="text-center"
        >
          <IFormCheckbox
            v-if="notification.channels.indexOf(channel) > -1"
            :id="channel + '-' + notification.key"
            :checked="modelValue[notification.key][channel]"
            @update:checked="
              $emit('update:modelValue', {
                ...modelValue,
                ...{
                  [notification.key]: {
                    ...modelValue[notification.key],
                    [channel]: $event,
                  },
                },
              })
            "
          />

          <Icon v-else class="size-5 text-neutral-500" icon="X" />
        </td>

        <td>
          <p
            class="text-neutral-700 dark:text-neutral-300"
            v-text="notification.name"
          />

          <p
            v-show="notification.description"
            class="mt-1 text-sm text-neutral-500 dark:text-neutral-300"
            v-text="notification.description"
          />
        </td>
      </tr>
    </tbody>
  </ITable>
</template>

<script setup>
import flatten from 'lodash/flatten'
import map from 'lodash/map'
import uniq from 'lodash/uniq'

import { useApp } from '@/Core/composables/useApp'

defineProps({
  modelValue: { required: true, type: Object },
})

defineEmits(['update:modelValue'])

const { scriptConfig } = useApp()

const settings = scriptConfig('notifications_settings')

const iconMaps = {
  mail: 'Mail',
  database: 'Bell',
}

const allAvailableChannels = uniq(flatten(map(settings, 'channels')))
</script>
