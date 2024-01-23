<template>
  <div class="table-responsive">
    <div
      class="border border-neutral-200 sm:rounded-md dark:border-neutral-800"
    >
      <!-- https://github.com/SortableJS/Vue.Draggable/issues/160 -->
      <SortableDraggable
        v-model="localProducts"
        tag="table"
        class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700"
        :item-key="item => item._key"
        handle=".product-draggable-handle"
        v-bind="$draggable.common"
        @end="updateProductsOrder"
        @start="productIdxNoteBeingAdded = null"
      >
        <template #header>
          <thead class="bg-neutral-50 dark:bg-neutral-800">
            <tr>
              <th
                v-t="'billable::product.table_heading'"
                class="bg-neutral-50 py-3 pl-4 pr-2 text-left text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-800 dark:text-neutral-200"
              />

              <th
                v-t="'billable::product.qty'"
                class="bg-neutral-50 p-2 text-right text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-800 dark:text-neutral-200"
              />

              <th
                v-t="'billable::product.unit_price'"
                class="bg-neutral-50 px-2 py-3 text-right text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-800 dark:text-neutral-200"
              />

              <th
                v-show="hasTax"
                v-t="'billable::product.tax'"
                class="bg-neutral-50 px-2 py-3 text-right text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-800 dark:text-neutral-200"
              />

              <th
                v-t="'billable::product.discount'"
                class="bg-neutral-50 px-2 py-3 text-right text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-800 dark:text-neutral-200"
              />

              <th
                v-t="'billable::product.amount'"
                class="bg-neutral-50 px-2 py-3 text-center text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-800 dark:text-neutral-200"
              />

              <th />
            </tr>
          </thead>

          <tbody v-show="!localProducts.length">
            <tr>
              <td
                v-t="'billable::product.resource_has_no_products'"
                :colspan="hasTax ? 6 : 5"
                class="bg-white p-3 text-center text-sm text-neutral-900 dark:bg-neutral-700 dark:text-neutral-100"
              />
            </tr>
          </tbody>
        </template>

        <template #item="{ element, index }">
          <tbody class="bg-white dark:bg-neutral-800">
            <BillableFormTableProductRow
              v-model="localProducts[index]"
              :tax-type="taxType"
              :index="index"
              @product-selected="$emit('productSelected', $event)"
            >
              <template #after-product-select="slotProps">
                <slot name="after-product-select" v-bind="slotProps" />
              </template>

              <td
                class="bg-white p-2 text-right align-top text-sm font-medium text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100"
              >
                <div class="flex items-center justify-between space-x-2">
                  <IMinimalDropdown horizontal class="mt-2">
                    <IDropdownItem
                      v-show="
                        (productIdxNoteBeingAdded === null ||
                          productIdxNoteBeingAdded !== index) &&
                        !localProducts[index].note
                      "
                      :text="$t('core::app.add_note')"
                      @click="addNote(index)"
                    />

                    <IDropdownItem
                      :text="$t('core::app.remove')"
                      @click="removeProduct(index)"
                    />
                  </IMinimalDropdown>
                  <!-- Disabled for now, as there are 2 <tr> when not exists and cannot be sorted (multiple elements) -->
                  <IButtonIcon
                    icon="Selector"
                    class="product-draggable-handle mt-2 cursor-move"
                  />
                </div>
              </td>
            </BillableFormTableProductRow>

            <tr
              v-if="
                productIdxNoteBeingAdded === index ||
                element.note ||
                localProducts[index].note
              "
            >
              <td :colspan="hasTax ? 7 : 6" class="px-1">
                <div
                  class="relative z-auto -mt-2 mb-1 rounded-sm bg-white p-2 dark:bg-neutral-800"
                >
                  <IFormLabel :for="'product-note-' + index" class="mb-1">
                    {{ $t('core::app.note_is_private') }}
                  </IFormLabel>

                  <IFormTextarea
                    ref="productNoteRef"
                    v-model="localProducts[index].note"
                    rows="2"
                    class="bg-warning-100 ring-warning-300 focus:ring-warning-400 dark:bg-warning-200 dark:text-neutral-800 dark:focus:ring-warning-400"
                  />
                </div>
              </td>
            </tr>
          </tbody>
        </template>
      </SortableDraggable>
    </div>
  </div>

  <ILink class="mt-3 inline-block font-medium" @click="insertNewLine">
    &plus; {{ $t('core::app.insert_new_line') }}
  </ILink>

  <BillableTotalSection
    :tax-type="taxType"
    :total="total"
    :total-discount="totalDiscount"
    :subtotal="subtotal"
    :taxes="taxes"
  />
</template>

<script setup>
import { computed, nextTick, onUnmounted, ref, watch } from 'vue'
import filter from 'lodash/filter'
import sortBy from 'lodash/sortBy'
import unionBy from 'lodash/unionBy'

import { useAccounting } from '@/Core/composables/useAccounting'
import { useApp } from '@/Core/composables/useApp'
import { randomString } from '@/Core/utils'

import { useProducts } from '../composables/useProducts'

import BillableFormTableProductRow from './BillableFormTableProductRow.vue'
import BillableTotalSection from './BillableTotalSection.vue'
import {
  blankProduct,
  totalProductAmountWithDiscount,
  totalProductDiscountAmount,
  totalTaxInAmount,
} from './utils'

const props = defineProps({
  products: { type: Array, default: () => [] },
  removedProducts: { type: Array, default: () => [] },
  taxType: { required: true, type: String },
})

const emit = defineEmits([
  'update:products',
  'update:removedProducts',
  'productSelected',
  'productRemoved',
])

const { toFixed } = useAccounting()
const { scriptConfig, setting } = useApp()

const {
  limitedNumberOfActiveProductsRetrieved,
  limitedNumberOfActiveProducts,
} = useProducts()

const productNoteRef = ref(null)
const precision = scriptConfig('currency.precision')
const productIdxNoteBeingAdded = ref(null)
const localProducts = ref([])

// Reset each time the component is unmounted
onUnmounted(() => {
  limitedNumberOfActiveProductsRetrieved.value = false
  limitedNumberOfActiveProducts.value = []
})

watch(
  localProducts,
  newVal => {
    emit('update:products', newVal)

    ensureCurrentProductsHasDraggableKey()
  },
  { deep: true }
)

watch(
  () => props.products,
  newVal => {
    localProducts.value = newVal
  },
  { immediate: true }
)

const hasTax = computed(() => props.taxType !== 'no_tax')

const isTaxInclusive = computed(() => props.taxType === 'inclusive')

const total = computed(() => {
  let total =
    parseFloat(subtotal.value) +
    parseFloat(!isTaxInclusive.value ? totalTax.value : 0)

  return parseFloat(toFixed(total, precision))
})

const totalDiscount = computed(() => {
  return parseFloat(
    toFixed(
      localProducts.value.reduce((total, product) => {
        return total + totalProductDiscountAmount(product, isTaxInclusive.value)
      }, 0),
      precision
    )
  )
})

const subtotal = computed(() => {
  return parseFloat(
    toFixed(
      localProducts.value.reduce((total, product) => {
        return (
          total + totalProductAmountWithDiscount(product, isTaxInclusive.value)
        )
      }, 0),
      precision
    )
  )
})

/**
 * Get the unique applied taxes
 */
const taxes = computed(() => {
  if (!hasTax.value) {
    return []
  }

  return sortBy(
    unionBy(localProducts.value, product => {
      // Track uniqueness by tax label and tax rate
      return product.tax_label + product.tax_rate
    }),
    'tax_rate'
  )
    .filter(tax => tax.tax_rate > 0)
    .reduce((groups, tax) => {
      let group = {
        key: tax.tax_label + tax.tax_rate,
        rate: tax.tax_rate,
        label: tax.tax_label,
        // We will get all products that are using the current tax in the loop
        raw_total: filter(localProducts.value, {
          tax_label: tax.tax_label,
          tax_rate: tax.tax_rate,
        })
          // Calculate the total tax based on the product
          .reduce((total, product) => {
            total += totalTaxInAmount(
              totalProductAmountWithDiscount(product, isTaxInclusive.value),
              product.tax_rate,
              isTaxInclusive.value
            )

            return total
          }, 0),
      }

      groups.push(group)

      return groups
    }, [])
})

const totalTax = computed(() => {
  return parseFloat(
    toFixed(
      taxes.value.reduce((total, tax) => {
        return total + parseFloat(toFixed(tax.raw_total, precision))
      }, 0),
      precision
    )
  )
})

function ensureCurrentProductsHasDraggableKey() {
  localProducts.value
    .filter(p => !p._key)
    .forEach(product => {
      product._key = randomString()
    })
}

function addNote(index) {
  productIdxNoteBeingAdded.value = index

  nextTick(() => productNoteRef.value.focus())
}

/**
 * Queue product for removal
 */
function removeProduct(index) {
  let product = localProducts.value[index]
  localProducts.value.splice(index, 1)

  if (productIdxNoteBeingAdded.value === index) {
    productIdxNoteBeingAdded.value = null
  }

  emit('productRemoved', { product, index })

  if (product.id) {
    emit('update:removedProducts', [...props.removedProducts, ...[product.id]])
  }
}

function insertNewLine() {
  localProducts.value.push(
    blankProduct({
      discount_type: setting('discount_type'),
      tax_rate: setting('tax_rate'),
      tax_label: setting('tax_label'),
    })
  )
  updateProductsOrder()
}

function updateProductsOrder() {
  localProducts.value.forEach(
    (product, index) => (product.display_order = index + 1)
  )
}

defineExpose({ insertNewLine })
</script>
