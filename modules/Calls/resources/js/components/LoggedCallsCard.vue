<template>
  <IModal
    id="readCallOutcomeModal"
    :title="$t('calls.call.outcome.call_outcome')"
    hide-footer
  >
    <!-- eslint-disable-next-line vue/no-v-html -->
    <div v-html="outcomeBeingRead"></div>
  </IModal>

  <CardAsyncTable :card="card">
    <template #date="{ formatted, row }">
      <p class="text-neutral-600 dark:text-neutral-300">
        {{ row.user.name }} -
        <ILink class="inline-flex items-center" @click="readOutcome(row.body)">
          <span v-text="formatted"></span>

          <Icon icon="Window" class="ml-2 size-4" />
        </ILink>
      </p>

      <div
        v-for="(associations, resourceName) in row.associations"
        :key="resourceName"
      >
        <div
          v-for="association in associations"
          :key="association.id"
          class="flex space-x-2"
        >
          <ILink
            class="font-normal underline underline-offset-2"
            variant="neutral"
            :to="association.path"
            :text="association.display_name"
          />
        </div>
      </div>
    </template>
    <!-- eslint-disable-next-line vue/valid-v-slot -->
    <template #outcome.name="{ row, formatted }">
      <TextBackground
        :color="row.outcome.swatch_color"
        class="shrink-0 rounded-md px-2.5 py-px text-sm/5 dark:!text-white"
      >
        {{ formatted }}
      </TextBackground>
    </template>
  </CardAsyncTable>
</template>

<script setup>
import { ref } from 'vue'

defineProps({ card: Object })

const outcomeBeingRead = ref(null)

function readOutcome(outcome) {
  outcomeBeingRead.value = outcome
  Innoclapps.dialog().show('readCallOutcomeModal')
}
</script>
