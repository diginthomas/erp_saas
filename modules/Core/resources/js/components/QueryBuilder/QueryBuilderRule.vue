<template>
  <div class="relative mb-3.5 justify-between sm:flex sm:items-center">
    <div
      class="mb-1 flex w-full flex-col justify-start sm:mb-0 sm:mr-5 sm:w-auto sm:flex-row sm:items-center sm:space-x-5"
    >
      <div class="relative flex items-center">
        <div
          v-if="selectedRule && selectedRule.helpText"
          v-i-tooltip.bottom.light="selectedRule.helpText"
          class="absolute -left-[31.5px] z-20 rounded-full border border-white bg-white dark:border-neutral-800 dark:bg-neutral-700"
        >
          <Icon
            icon="QuestionMarkCircle"
            class="size-5 text-neutral-500 hover:text-neutral-700 dark:text-white dark:hover:text-neutral-300"
          />
        </div>

        <div class="w-[90%] sm:w-48">
          <ICustomSelect
            truncate
            :disabled="readOnly"
            :class="{ 'ring-warning-400': !query.rule }"
            :model-value="selectedRule"
            :placeholder="labels.selectRule"
            :clearable="false"
            :options="availableRules"
            size="sm"
            @update:model-value="handleRuleChanged"
          />
        </div>
      </div>

      <div v-if="showOperands" class="mt-1 w-full sm:mt-0 sm:w-44">
        <ICustomSelect
          :model-value="operand"
          size="sm"
          truncate
          :disabled="readOnly"
          :clearable="false"
          :option-label="option => option[option.labelKey]"
          :options="selectedRule.operands"
          @update:model-value="handleOperandChanged"
        />
      </div>

      <div
        v-if="selectedRule && !hasOnlyOneOperator && !selectedRule.isStatic"
        class="flex-none"
      >
        <IDropdownSelect
          v-slot="{ toggle }"
          condensed
          :disabled="readOnly"
          :model-value="selectedOperator"
          :items="
            operators.map(o => ({
              label: labels.operatorLabels[o] || o,
              value: o,
            }))
          "
          @update:model-value="selectedOperator = $event.value"
        >
          <button
            type="button"
            :disabled="readOnly"
            class="min-w-max text-sm font-medium text-neutral-700 hover:text-neutral-900 focus:outline-none disabled:pointer-events-none dark:text-neutral-100 dark:hover:text-neutral-300"
            @click="toggle"
          >
            {{ labels.operatorLabels[selectedOperator] || selectedOperator }}
          </button>
        </IDropdownSelect>
      </div>
    </div>

    <div v-if="selectedRule || operand" v-show="!isNullable" class="grow">
      <component
        :is="ruleComponent"
        :query="query"
        :index="index"
        :operand="operand"
        :rule="operand ? operand.rule : selectedRule"
        :operator="query.operator"
        :labels="labels"
        :read-only="readOnly"
        :is-nullable="isNullable"
        :is-between="isBetween"
      />
    </div>

    <IButtonIcon
      v-if="!readOnly"
      icon="Trash"
      class="absolute right-0 top-2 sm:relative sm:right-auto sm:top-auto sm:ml-3"
      icon-class="size-4"
      @click="remove"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useStore } from 'vuex'
import find from 'lodash/find'

import CheckboxRule from './Rules/CheckboxRule.vue'
import DateRule from './Rules/DateRule.vue'
import MultiSelectRule from './Rules/MultiSelectRule.vue'
import NullableRule from './Rules/NullableRule.vue'
import NumberRule from './Rules/NumberRule.vue'
import NumericRule from './Rules/NumericRule.vue'
import RadioRule from './Rules/RadioRule.vue'
import SelectRule from './Rules/SelectRule.vue'
import StaticRule from './Rules/StaticRule.vue'
import TextRule from './Rules/TextRule.vue'
import { isBetweenOperator, isNullableOperator } from './utils'

defineOptions({
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Rule',
  inheritAttrs: false,
})

const props = defineProps([
  'query',
  'index',
  'labels',
  'readOnly',
  'availableRules',
])

const emit = defineEmits(['childDeletionRequested'])

const rulesComponents = {
  'numeric-rule': NumericRule,
  'checkbox-rule': CheckboxRule,
  'date-rule': DateRule,
  'number-rule': NumberRule,
  'radio-rule': RadioRule,
  'select-rule': SelectRule,
  'multi-select-rule': MultiSelectRule,
  'text-rule': TextRule,
  'static-rule': StaticRule,
  'nullable-rule': NullableRule,
}

const store = useStore()

const selectedOperand = computed({
  get() {
    return props.query.operand
  },
  set(value) {
    store.commit('queryBuilder/UPDATE_QUERY_OPERAND', {
      query: props.query,
      value: value,
    })
  },
})

const selectedOperator = computed({
  get() {
    return props.query.operator
  },
  set(value) {
    store.commit('queryBuilder/UPDATE_QUERY_OPERATOR', {
      query: props.query,
      value: value,
    })
  },
})

/**
 * Get the main selected rule.
 */
const selectedRule = computed(() => {
  return find(props.availableRules, ['id', props.query.rule])
})

/**
 * Get the selected operand object
 */
const operand = computed(() =>
  props.query.operand
    ? find(selectedRule.value.operands, ['value', props.query.operand])
    : null
)

/**
 * Get the rule component.
 */
const ruleComponent = computed(() => {
  let component = hasOperandWithRule.value
    ? operand.value.rule.component
    : selectedRule.value.component

  if (Object.hasOwn(rulesComponents, component)) {
    return rulesComponents[component]
  }

  return component
})

/**
 * Inicates whether the rule as operand with rule
 */
const hasOperandWithRule = computed(() =>
  Boolean(operand.value && operand.value.rule)
)

/**
 * Get the rule operators
 */
const operators = computed(() =>
  hasOperandWithRule.value
    ? operand.value.rule.operators
    : selectedRule.value.operators
)

/**
 * Indicates whether the rule has only one operator
 */
const hasOnlyOneOperator = computed(
  () => operators.value && operators.value.length === 1
)

/**
 * Indicates whether the operands should be shown
 */
const showOperands = computed(() => {
  if (!selectedRule.value) {
    return false
  }

  if (selectedRule.value.isStatic || selectedRule.value.hideOperands) {
    return false
  }

  return selectedRule.value.operands && selectedRule.value.operands.length > 0
})

/**
 * Indicates whether the rule operator is between
 */
const isBetween = computed(() => isBetweenOperator(selectedOperator.value))

/**
 * Indicates whether the rule operator is nullable
 */
const isNullable = computed(() => isNullableOperator(selectedOperator.value))

/**
 * Handle the rule changed event
 */
function handleRuleChanged(selectRule) {
  store.commit('queryBuilder/UPDATE_QUERY_RULE', {
    query: props.query,
    value: selectRule.id,
  })

  // Reset value on rule changed/
  store.commit('queryBuilder/UPDATE_QUERY_VALUE', {
    query: props.query,
    value: null,
  })

  store.commit('queryBuilder/UPDATE_QUERY_TYPE', {
    query: props.query,
    value: selectRule.type,
  })

  selectedOperator.value = selectRule.operators[0]

  if (selectRule.operands && selectRule.operands.length > 0) {
    selectedOperand.value =
      selectRule.operands[0][selectRule.operands[0].valueKey]

    if (selectRule.operands[0].rule) {
      selectedOperator.value = selectRule.operands[0].rule.operators[0]
    }
  } else {
    selectedOperand.value = null
  }
}

/**
 * Handle the operand changed event
 */
function handleOperandChanged(selectOperand) {
  selectedOperand.value = selectOperand[selectOperand.valueKey]

  // When operand is changed, set the first operator as active
  if (selectOperand.rule.operators && selectOperand.rule.operators.length > 0) {
    selectedOperator.value = selectOperand.rule.operators[0]
  }
}

/**
 * Request rule remove.
 */
function remove() {
  emit('childDeletionRequested', props.index)
}
</script>
