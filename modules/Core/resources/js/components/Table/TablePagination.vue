<template>
  <div class="flex items-center justify-between">
    <div class="flex flex-1 justify-between sm:hidden">
      <IButton
        :variant="variant"
        :size="size"
        :disabled="!hasPreviousPage || loading"
        :text="$t('pagination.previous')"
        @click="$emit('goToPrevious')"
      />

      <IButton
        :variant="variant"
        :size="size"
        :disabled="!hasNextPage || loading"
        :text="$t('pagination.next')"
        @click="$emit('goToNext')"
      />
    </div>

    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p
          v-t="{
            path: 'core::table.info',
            args: {
              from: from,
              to: to,
              total: total,
            },
          }"
          class="text-sm text-neutral-700 dark:text-neutral-200"
        />
      </div>

      <div>
        <nav
          class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm"
          aria-label="Pagination"
        >
          <template v-if="renderLinks">
            <IButtonGroup>
              <IButton
                icon="ChevronLeft"
                :size="size"
                :variant="variant"
                :disabled="!hasPreviousPage || loading"
                @click="$emit('goToPrevious')"
              />

              <template v-for="(page, index) in links" :key="index">
                <IButton
                  v-if="page === '...'"
                  :disabled="true"
                  :variant="variant"
                  :size="size"
                >
                  ...
                </IButton>

                <IButton
                  v-else
                  aria-current="page"
                  :text="page"
                  :variant="variant"
                  :active="isCurrentPageCheck(page)"
                  :disabled="loading"
                  @click="
                    isCurrentPageCheck(page)
                      ? undefined
                      : $emit('goToPage', page)
                  "
                />
              </template>

              <IButton
                icon="ChevronRight"
                :size="size"
                :variant="variant"
                :disabled="!hasNextPage || loading"
                @click="$emit('goToNext')"
              />
            </IButtonGroup>
          </template>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  loading: { type: Boolean, required: false },
  isCurrentPageCheck: { type: Function, required: true },
  renderLinks: { type: Boolean, required: true },
  links: { type: Array },

  hasNextPage: { type: Boolean, required: true },
  hasPreviousPage: { type: Boolean, required: true },
  from: { type: Number, required: true },
  to: { type: Number, required: true },
  total: { type: Number, required: true },
  variant: { type: String, default: 'white' },
  size: {
    type: String,
    default: 'md',
    validator(value) {
      return ['sm', 'md', 'lg'].includes(value)
    },
  },
})

defineEmits(['goToPrevious', 'goToNext', 'goToPage'])
</script>
