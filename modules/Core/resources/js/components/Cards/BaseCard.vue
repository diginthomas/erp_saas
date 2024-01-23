<template>
  <ICard :overlay="isCardLoading">
    <div class="group text-lg sm:flex sm:flex-col sm:items-center md:flex-row">
      <div class="truncate md:grow">
        <div class="flex items-center px-6 pt-4 md:pb-4">
          <ICardHeading class="truncate">{{ card.name }}</ICardHeading>

          <div class="ml-2 flex items-center space-x-1.5">
            <span
              v-if="card.helpText"
              v-i-tooltip.bottom.light="card.helpText"
              class="flex"
            >
              <IButtonIcon
                class="pointer-events-none"
                icon="QuestionMarkCircle"
                icon-class="size-4"
              />
            </span>

            <IButtonIcon
              icon="Refresh"
              :class="['group-hover:block', isCardLoading ? 'block' : 'hidden']"
              :icon-class="['size-4', isCardLoading ? 'animate-spin' : '']"
              @click="fetchCard(true)"
            />
          </div>
        </div>
      </div>

      <div
        class="-mt-2 ml-3.5 flex shrink-0 space-x-2 px-3 py-2 sm:-mt-0 sm:ml-0 sm:px-6 sm:py-4"
      >
        <slot name="actions"></slot>

        <IDropdownSelect
          v-if="card.withUserSelection"
          v-model="user"
          :items="usersForSelection"
          label-key="name"
          value-key="id"
          placement="bottom-end"
          condensed
          @change="fetchCard()"
        />

        <IDropdownSelect
          v-if="hasRanges"
          v-model="selectedRange"
          placement="bottom-end"
          :items="card.ranges"
          condensed
          @change="fetchCard()"
        />
      </div>
    </div>

    <slot />
  </ICard>
</template>

<script setup>
import { computed, ref, unref, watch } from 'vue'
import { onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import find from 'lodash/find'

import { useApp } from '@/Core/composables/useApp'
import { useGlobalEventListener } from '@/Core/composables/useGlobalEventListener'
import { useLoader } from '@/Core/composables/useLoader'

const props = defineProps({
  card: Object,
  loading: Boolean,
  reloadOnQueryStringChange: { type: Boolean, default: true },
  requestQueryString: Object,
})

const emit = defineEmits(['retrieved'])

const { t } = useI18n()
const { setLoading, isLoading } = useLoader()
const { users } = useApp()

const user = ref(null)

const selectedRange = ref(
  find(props.card.ranges, ['value', props.card.range]) || props.card.ranges[0]
)

const usersForSelection = computed(() =>
  [
    {
      id: null,
      name: t('core::app.all'),
    },
    ...(props.card.users || users.value),
  ].map(user => ({ id: user.id, name: user.name }))
)

const isCardLoading = computed(() => props.loading || isLoading.value)
const hasRanges = computed(() => props.card.ranges.length > 0)

if (
  props.card.withUserSelection !== false &&
  typeof props.card.withUserSelection === 'number'
) {
  user.value = find(usersForSelection.value, [
    'id',
    props.card.withUserSelection,
  ])
}

watch(
  () => props.requestQueryString,
  () => {
    props.reloadOnQueryStringChange && fetchCard()
  },
  { deep: true }
)

async function fetchCard(reloadCache = false) {
  setLoading(true)

  let queryString = {
    range: unref(selectedRange)?.value,
    ...(props.requestQueryString || {}),
    reload_cache: reloadCache === true,
  }

  if (props.card.withUserSelection) {
    queryString.user_id = user.value ? user.value.id : null
  }

  try {
    const { data: card } = await Innoclapps.request(
      `/cards/${props.card.uriKey}`,
      {
        params: queryString,
      }
    )

    emit('retrieved', {
      card: card,
      requestQueryString: queryString,
    })
  } finally {
    setLoading(false)
  }
}

onMounted(() => {
  if (props.card.refreshOnActionExecuted) {
    useGlobalEventListener('action-executed', fetchCard)
  }

  useGlobalEventListener('refresh-cards', fetchCard)

  if (props.card.floatingResource) {
    useGlobalEventListener(
      ['floating-resource-updated', 'floating-resource-action-executed'],
      updatedData => {
        if (
          updatedData.resourceName === props.card.floatingResource.resourceName
        ) {
          fetchCard(true)
        }
      }
    )
  }
})
</script>
