<template>
  <div
    :class="[
      depth > 1 ? 'mb-1 ml-1 mt-7' : '',
      totalChildren > 1 || depth > 1 ? 'flex items-center' : 'hidden',
    ]"
  >
    <I18nT
      scope="global"
      class="mb-2 flex w-full flex-wrap items-start text-sm font-medium text-neutral-800 sm:items-center dark:text-neutral-100"
      :keypath="
        depth <= 1
          ? 'core::filters.show_matching_records_conditions'
          : 'core::filters.or_match_any_conditions'
      "
      tag="p"
    >
      <template v-if="depth > 1" #condition>
        {{ $t('core::filters.conditions.' + previousMatchType) }}
      </template>

      <template #match_type>
        <select
          v-model="selectedQueryCondition"
          :class="[
            'border-1 mx-1 rounded-md border-neutral-300 bg-none px-2 py-0 text-sm focus:shadow-none focus:ring-primary-500 dark:border-neutral-500 dark:bg-neutral-500 dark:text-white',
            readOnly ? 'pointer-events-none' : '',
          ]"
        >
          <option value="and">{{ labels.matchTypeAll }}</option>

          <option value="or">{{ labels.matchTypeAny }}</option>
        </select>
      </template>
    </I18nT>

    <IButtonIcon
      v-if="depth > 1"
      icon="Trash"
      class="ml-auto"
      icon-class="size-4"
      @click.prevent.stop="requestRemove"
    />
  </div>

  <div :class="['p-5', depth > 0 ? 'mb-4' : '', groupWrapperClasses]">
    <component
      :is="components[child.type]"
      v-for="(child, childIndex) in children"
      :key="child.query.rule + '-' + childIndex"
      :query="child.query"
      :index="childIndex"
      :previous-match-type="query.condition"
      :read-only="readOnly"
      :max-depth="maxDepth"
      :depth="nextDepth"
      :available-rules="availableRules"
      :labels="labels"
      @child-deletion-requested="removeChild"
    />

    <div :class="['inline-flex', readOnly ? 'hidden' : '']">
      <ILink
        v-if="totalChildren === 0 || !(depth < maxDepth)"
        variant="neutral"
        @click="addCondition"
      >
        &plus;
        {{
          totalChildren === 0 ? labels.addCondition : labels.addAnotherCondition
        }}
      </ILink>

      <IDropdown v-else tag="a" href="#" no-caret class="link link-neutral">
        <template #toggle-content>
          &plus;
          {{
            totalChildren === 0
              ? labels.addCondition
              : labels.addAnotherCondition
          }}
        </template>

        <IDropdownItem @click="addCondition">
          &plus; {{ labels.addCondition }}
        </IDropdownItem>

        <IDropdownItem
          v-if="depth < maxDepth && totalChildren > 0"
          :description="$t('core::filters.add_group_info')"
          @click="addGroup"
        >
          &plus; {{ labels.addGroup }}
        </IDropdownItem>
      </IDropdown>
    </div>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useStore } from 'vuex'

import QueryBuilderRule from './QueryBuilderRule.vue'

defineOptions({
  name: 'QueryBuilderGroup',
})

const props = defineProps([
  'index',
  'query',
  'availableRules',
  'maxDepth',
  'depth',
  'labels',
  'readOnly',
  'previousMatchType',
])

const emit = defineEmits(['childDeletionRequested'])

const components = { rule: QueryBuilderRule, group: 'QueryBuilderGroup' }

const store = useStore()

const selectedQueryCondition = computed({
  get() {
    return props.query.condition
  },
  set(value) {
    store.commit('queryBuilder/UPDATE_QUERY_CONDITION', {
      query: props.query,
      value: value,
    })
  },
})

/**
 * Get the group wrapper classes
 */
const groupWrapperClasses = computed(() => ({
  'border rounded-md bg-neutral-50/60 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-600':
    props.depth === 1,
  'border rounded bg-neutral-100/80 dark:bg-neutral-700 border-neutral-200 dark:border-neutral-600':
    props.depth === 2,
  'border rounded bg-neutral-200/40 dark:bg-neutral-800 border-neutral-200 dark:border-neutral-600':
    props.depth > 2,
}))

/**
 * Get/set the child rules in the group
 */
const children = computed({
  get() {
    return props.query.children
  },
  set(value) {
    store.commit('queryBuilder/SET_QUERY_CHILDREN', {
      query: props.query,
      children: value,
    })
  },
})

/**
 * The number of total child rules in the group
 */
const totalChildren = computed(() => children.value.length)

/**
 * When the user removed the last child from nested group, remove the group
 */
watch(totalChildren, newVal => {
  if (props.depth > 1 && newVal == 0) {
    requestRemove()
  }
})

/**
 * Get the next depth
 */
const nextDepth = computed(() => props.depth + 1)

/**
 * Add new empty rule condition to the group
 */
function addCondition() {
  store.commit('queryBuilder/ADD_QUERY_CHILD', {
    query: props.query,
    child: {
      type: 'rule',
      query: {
        type: null,
        rule: null,
        operator: null,
        operand: null,
        value: null,
      },
    },
  })
}

/**
 * Add new group
 */
function addGroup() {
  if (props.depth < props.maxDepth) {
    store.commit('queryBuilder/ADD_QUERY_GROUP', props.query)
  }
}

/**
 * Remove group
 */
function requestRemove() {
  emit('childDeletionRequested', props.index)
}

/**
 * Remove child
 */
function removeChild(index) {
  store.commit('queryBuilder/REMOVE_QUERY_CHILD', {
    query: props.query,
    index: index,
  })
}
</script>
