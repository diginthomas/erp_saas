<template>
  <div
    v-if="swatchColor"
    class="bottom-hidden p-2"
    :style="{ background: swatchColor }"
  />

  <div
    class="bottom-hidden group px-4 py-2"
    :class="{
      'bg-warning-200/20 dark:bg-warning-400/30': fallsBehindExpectedCloseDate,
      'bg-danger-50 dark:bg-danger-500/30': status == 'lost',
      'bg-success-50/70 dark:bg-success-500/30': status == 'won',
    }"
  >
    <div class="flex">
      <div class="grow truncate">
        <ILink
          class="truncate font-medium"
          :href="path"
          variant="neutral"
          :text="displayName"
          @click="
            floatResourceInDetailMode({
              resourceName,
              resourceId: dealId,
            })
          "
        />

        <div class="mt-1 flex text-sm">
          <IDropdown
            no-caret
            tag="button"
            :class="{
              'text-warning-500': incompleteActivitiesCount > 0,
              'text-success-500': incompleteActivitiesCount === 0,
            }"
          >
            <template #toggle-content>
              {{
                $t('activities::activity.count', incompleteActivitiesCount, {
                  count: incompleteActivitiesCount,
                })
              }}
            </template>

            <div
              v-if="nextActivityDate"
              class="border-b border-neutral-200 dark:border-neutral-700"
            >
              <div class="px-4 py-3 text-xs">
                <p class="text-neutral-500 dark:text-neutral-300">
                  {{ $t('activities.activity.next_activity_date') }}
                </p>

                <p class="font-medium text-neutral-700 dark:text-neutral-100">
                  {{
                    isMidnight(nextActivityDate)
                      ? localizedDate(nextActivityDate)
                      : localizedDateTime(nextActivityDate)
                  }}
                </p>
              </div>
            </div>

            <IDropdownItem
              icon="Clock"
              :text="$t('activities::activity.create')"
              @click="$emit('createActivityRequested', dealId)"
            />
          </IDropdown>

          <div class="relative">
            <BillableFormProductsModal
              v-if="manageProducts"
              :resource-name="resourceName"
              :resource-id="dealId"
              visible
              prefetch
              @saved="handleBillableModelSavedEvent"
              @hidden="manageProducts = false"
            />

            <IPopover v-model:visible="showAmountUpdatePopover" class="w-80">
              <a
                href="#"
                class="link link-neutral ml-2"
                @click.prevent.stop="handleDealAmountEdit"
                v-text="formatMoney(amount)"
              />

              <template #popper>
                <div class="p-4">
                  <IFormGroup
                    required
                    :label="$t('deals::fields.deals.amount')"
                    label-for="editDealAmount"
                  >
                    <IFormNumericInput
                      id="editDealAmount"
                      v-model="updateForm.amount"
                    />

                    <IFormError :error="updateForm.getError('amount')" />
                  </IFormGroup>

                  <ILink
                    class="inline-flex items-center"
                    @click="
                      (manageProducts = true), (showAmountUpdatePopover = false)
                    "
                  >
                    <span v-t="'billable::product.manage'"></span>

                    <Icon icon="Window" class="ml-2 mt-px size-4" />
                  </ILink>
                </div>

                <div
                  class="flex justify-end space-x-1 bg-neutral-100 px-4 py-3 dark:bg-neutral-900"
                >
                  <IButton
                    variant="white"
                    :disabled="updateForm.busy"
                    :text="$t('core::app.cancel')"
                    @click="showAmountUpdatePopover = false"
                  />

                  <IButton
                    variant="primary"
                    :loading="updateForm.busy"
                    :disabled="updateForm.busy"
                    :text="$t('core::app.save')"
                    @click="updateDeal"
                  />
                </div>
              </template>
            </IPopover>
          </div>
        </div>

        <p
          v-if="expectedCloseDate"
          :class="[
            'mt-1 text-xs',
            fallsBehindExpectedCloseDate
              ? 'animate-[pulse_4s_cubic-bezier(0.4,_0,_0.6,_1)_infinite] text-warning-600 dark:text-warning-300'
              : 'text-neutral-700 dark:text-neutral-300',
          ]"
          v-text="localizedDate(expectedCloseDate)"
        />
      </div>

      <div class="mt-1 flex flex-col items-center space-y-1">
        <IMinimalDropdown items-class="min-w-[180px]">
          <div class="border-b border-neutral-200 dark:border-neutral-700">
            <div class="px-4 py-3 text-xs">
              <p class="text-neutral-500 dark:text-neutral-300">
                {{ $t('core::app.created_at') }}
              </p>

              <p class="font-medium text-neutral-700 dark:text-neutral-100">
                {{ localizedDateTime(createdAt) }}
              </p>

              <div v-if="updatedAt && updatedAt !== createdAt" class="mt-1">
                <p class="text-neutral-500 dark:text-neutral-300">
                  {{ $t('core::app.updated_at') }}
                </p>

                <p class="font-medium text-neutral-700 dark:text-neutral-100">
                  {{ localizedDateTime(updatedAt) }}
                </p>
              </div>
            </div>
          </div>

          <IDropdownItem
            icon="Clock"
            :text="$t('activities::activity.create')"
            @click="$emit('createActivityRequested', dealId)"
          />

          <IDropdownItem
            icon="Eye"
            :text="$t('core::app.view_record')"
            :to="path"
          />

          <IDropdownItem
            icon="PencilAlt"
            :text="$t('core::app.edit') + ' ' + $t('deals::deal.deal')"
            @click="
              floatResourceInEditMode({
                resourceName,
                resourceId: dealId,
              })
            "
          />
        </IMinimalDropdown>

        <IPopover class="w-56" auto-placement>
          <IButtonIcon
            icon="ColorSwatch"
            class="opacity-100 md:opacity-0 md:group-hover:opacity-100"
          />

          <template #popper>
            <div class="p-4">
              <IColorSwatch
                :model-value="swatchColor"
                :swatches="$scriptConfig('favourite_colors')"
                :customable="false"
                @input="$emit('update:swatchColor', $event)"
              />
            </div>
          </template>
        </IPopover>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

import { useAccounting } from '@/Core/composables/useAccounting'
import { useDates } from '@/Core/composables/useDates'
import { useFloatingResourceModal } from '@/Core/composables/useFloatingResourceModal'
import { useForm } from '@/Core/composables/useForm'
import { useResourceable } from '@/Core/composables/useResourceable'

import BillableFormProductsModal from '@/Billable/components/BillableFormProductsModal.vue'

const props = defineProps({
  dealId: { required: true, type: Number },
  displayName: { required: true, type: String },
  amount: { required: true, type: [String, Number] },
  path: { required: true, type: String },
  status: { required: true, type: String },
  incompleteActivitiesCount: { required: true, type: Number },
  productsCount: { required: true, type: Number },
  expectedCloseDate: String,
  nextActivityDate: String,
  swatchColor: String,
  fallsBehindExpectedCloseDate: Boolean,
  updatedAt: String,
  createdAt: String,
})

const emit = defineEmits([
  'createActivityRequested',
  'update:amount',
  'update:productsCount',
  'update:swatchColor',
])

const resourceName = Innoclapps.resourceName('deals')

const manageProducts = ref(false)
const showAmountUpdatePopover = ref(false)

const { localizedDate, localizedDateTime, isMidnight } = useDates()
const { formatMoney } = useAccounting()

const { floatResourceInDetailMode, floatResourceInEditMode } =
  useFloatingResourceModal()

const { form: updateForm } = useForm({
  amount: props.amount,
})

const { updateResource } = useResourceable(resourceName)

function updateDeal() {
  updateResource(updateForm, props.dealId).then(deal => {
    emit('update:amount', deal.amount)
    updateForm.set('amount', deal.amount)
    showAmountUpdatePopover.value = false
  })
}

function handleDealAmountEdit() {
  if (props.productsCount > 0) {
    initiateProductsModal()
  } else {
    showAmountUpdatePopover.value = !showAmountUpdatePopover.value
  }
}

async function handleBillableModelSavedEvent(billable) {
  emit('update:amount', billable.total)
  updateForm.set('amount', billable.total)
  emit('update:productsCount', billable.products.length)
}

function initiateProductsModal() {
  manageProducts.value = true
}
</script>
