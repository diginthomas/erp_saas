<template>
  <div>
    <h2
      class="flex items-center justify-between font-medium text-neutral-800 dark:text-white"
    >
      <span>
        {{ cardTitle }}

        <span
          v-show="total > 0"
          class="text-sm font-normal text-neutral-400"
          v-text="'(' + total + ')'"
        />
      </span>

      <span class="flex items-center">
        <ILink
          v-if="total > limit"
          :text="
            !showAll ? $t('core::app.show_all') : $t('core::app.show_less')
          "
          class="shrink-0"
          xs
          @click="showAll = !showAll"
        />

        <span
          v-if="total > limit"
          v-show="!showAll"
          v-t="{ path: 'core::app.has_more', args: { count: total - limit } }"
          class="ml-1 text-xs font-normal text-neutral-400"
        />

        <slot name="top-actions"></slot>
      </span>
    </h2>

    <ul
      class="divide-y divide-neutral-200 dark:divide-neutral-700"
      :class="{ '-mb-3': total > 0 }"
    >
      <li
        v-for="(deal, index) in iterable"
        v-show="index <= limit - 1 || showAll"
        :key="deal.id"
        class="group flex items-center space-x-2 py-3"
      >
        <div class="shrink-0">
          <IBadge
            :variant="
              deal.status === 'won'
                ? 'success'
                : deal.status === 'lost'
                  ? 'danger'
                  : 'neutral'
            "
            :text="$t('deals::deal.status.' + deal.status)"
          />
        </div>

        <div class="min-w-0 flex-1 truncate">
          <ILink
            :href="deal.path"
            :text="deal.display_name"
            class="font-medium"
            variant="neutral"
            @click="
              floatResource({
                resourceName: resourceName,
                resourceId: deal.id,
                mode: floatMode,
              })
            "
          />

          <div class="flex space-x-2">
            <p
              class="text-sm text-neutral-500 dark:text-neutral-300"
              v-text="deal.stage.name"
            />

            <span v-if="Object.hasOwn(deal, 'amount')" class="-mt-0.5">
              &ndash;
            </span>

            <p
              v-if="Object.hasOwn(deal, 'amount')"
              class="text-sm text-neutral-500 dark:text-neutral-300"
              v-text="formatMoney(deal.amount)"
            />
          </div>
        </div>

        <div class="block shrink-0 md:hidden md:group-hover:block">
          <div class="flex items-center space-x-2">
            <ILink
              v-if="deal.authorizations.view"
              class="btn btn-white btn-xs"
              :text="$t('core::app.view_record')"
              :to="deal.path"
              plain
            />

            <slot name="actions" :deal="deal"></slot>
          </div>
        </div>
      </li>
    </ul>

    <p
      v-show="!hasDeals"
      class="text-sm text-neutral-500 dark:text-neutral-300"
      v-text="emptyText"
    />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import castArray from 'lodash/castArray'
import orderBy from 'lodash/orderBy'

import { useAccounting } from '@/Core/composables/useAccounting'
import { useFloatingResourceModal } from '@/Core/composables/useFloatingResourceModal'

const props = defineProps({
  deals: { type: [Object, Array], required: true },
  limit: { type: Number, default: 3 },
  emptyText: String,
  title: String,
  floatMode: { type: String, default: 'detail' },
})

const resourceName = Innoclapps.resourceName('deals')

const { t } = useI18n()
const { floatResource } = useFloatingResourceModal()
const { formatMoney } = useAccounting()

const showAll = ref(false)

const localDeals = computed(() => castArray(props.deals))

const wonSorted = computed(() =>
  orderBy(
    localDeals.value.filter(deal => deal.status === 'won'),
    deal => new Date(deal.won_date),
    'desc'
  )
)

const lostSorted = computed(() =>
  orderBy(
    localDeals.value.filter(deal => deal.status === 'lost'),
    deal => new Date(deal.lost_date),
    'desc'
  )
)

const openSorted = computed(() =>
  orderBy(
    localDeals.value.filter(deal => deal.status === 'open'),
    deal => new Date(deal.created_at)
  )
)

const iterable = computed(() => [
  ...openSorted.value,
  ...lostSorted.value,
  ...wonSorted.value,
])

const total = computed(() => localDeals.value.length)

const hasDeals = computed(() => total.value > 0)

const cardTitle = computed(() => props.title || t('deals::deal.deals'))
</script>
