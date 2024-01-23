<template>
  <ICard no-body class="mh-96">
    <template #header>
      <ICardHeading class="text-base">{{ heading }}</ICardHeading>
    </template>

    <template #actions>
      <div class="flex h-0.5 items-center">
        <IButton
          v-show="fieldsVisible"
          class="mr-2"
          :loading="saving"
          :disabled="requestInProgress"
          :text="$t('core::app.save')"
          @click="submit(true)"
        />

        <IButton
          v-show="fieldsVisible"
          variant="white"
          class="mr-2"
          :loading="resetting"
          :text="$t('core::app.reset')"
          :disabled="requestInProgress"
          @click="reset"
        />

        <IButton
          v-show="fieldsVisible"
          variant="white"
          :loading="fetching"
          icon="ChevronUp"
          :disabled="requestInProgress"
          @click="toggle"
        />
      </div>
    </template>

    <div
      class="cursor-pointer border-b border-neutral-200 px-4 py-4 sm:px-6 dark:border-neutral-800"
    >
      <div class="lg:flex lg:items-center lg:justify-between">
        <p
          class="text-sm text-neutral-600 dark:text-white"
          @click="toggle"
          v-text="subHeading"
        />

        <IButton
          v-show="!fieldsVisible"
          variant="white"
          :text="$t('core::fields.manage')"
          class="mt-4 shrink-0 lg:ml-5 lg:mt-0"
          @click="toggle"
        />
      </div>

      <div v-show="fieldsVisible" class="mb-0 mt-3">
        <SearchInput v-model="search" />
      </div>
    </div>

    <ul v-show="fieldsVisible" class="max-h-96 overflow-y-auto">
      <SortableDraggable
        v-bind="$draggable.scrollable"
        :list="filteredFields"
        :item-key="item => view + ' - ' + item.attribute"
        handle=".field-draggable-handle"
        :group="view"
      >
        <template #item="{ element }">
          <li
            class="border-b border-neutral-200 px-4 py-4 sm:px-6 dark:border-neutral-700"
            :class="{
              'bg-neutral-50 dark:bg-neutral-700/60': element.primary,
              'opacity-60': !element[visibilityKey],
            }"
          >
            <div class="flex items-center">
              <div class="grow">
                <div class="space-x-2">
                  <span
                    class="text-sm font-medium text-neutral-800 dark:text-white"
                  >
                    {{ element.label }}
                  </span>

                  <IBadge
                    v-show="element.customField"
                    variant="info"
                    :text="$t('core::fields.custom.field')"
                  />

                  <IBadge
                    v-show="element.isUnique"
                    variant="success"
                    :text="$t('core::fields.field_is_unique')"
                  />

                  <IBadge
                    v-show="element.readonly"
                    variant="warning"
                    :text="$t('core::fields.is_readonly')"
                  />
                </div>

                <span
                  v-show="element.helpText"
                  class="text-xs text-neutral-800 dark:text-white"
                >
                  <br />{{ element.helpText }}
                </span>

                <span
                  v-if="element.primary"
                  class="text-xs font-medium text-info-600 dark:text-white"
                >
                  <br />{{ $t('core::fields.primary') }}
                </span>
              </div>

              <div class="space-x-1 self-start">
                <IButtonCopy
                  v-i-tooltip="$t('core::app.copy_api_key')"
                  :text="element.attribute"
                  icon-class="size-4.5"
                />

                <IButtonIcon
                  v-if="element.customField || element.optionsViaResource"
                  v-i-tooltip="$t('core::app.edit')"
                  icon="PencilAlt"
                  icon-class="size-4.5"
                  @click="requestEdit(element)"
                />

                <IButtonIcon
                  v-if="element.customField"
                  v-i-tooltip="$t('core::app.delete')"
                  icon="Trash"
                  icon-class="size-4.5"
                  @click="requestDelete(element.customField.id)"
                />

                <IButtonIcon
                  icon="Selector"
                  icon-class="size-4.5"
                  class="field-draggable-handle cursor-move"
                />
              </div>
            </div>

            <div v-if="!element.primary" class="mt-3">
              <IFormCheckbox
                v-model:checked="element[visibilityKey]"
                :disabled="element.isRequired"
                :label="$t('core::fields.visible')"
              />

              <IFormCheckbox
                v-if="element.canUnmarkUnique"
                :checked="element.isUnique && !element.uniqueUnmarked"
                @change="element.uniqueUnmarked = !$event"
              >
                <span class="inline-flex items-center">
                  <span v-t="'core::fields.mark_as_unique'" />

                  <span
                    v-show="element.uniqueUnmarked"
                    class="ml-1.5 mt-0.5 text-xs text-neutral-500 dark:text-neutral-300"
                  >
                    {{
                      !isCreateView
                        ? $t('core::fields.option_disabled_will_propagate', {
                            view_name: isUpdateView
                              ? $t('core::fields.settings.detail')
                              : $t('core::fields.settings.update'),
                          })
                        : ''
                    }}
                  </span>
                </span>
              </IFormCheckbox>

              <IFormCheckbox
                v-if="collapseOption"
                v-model:checked="element.collapsed"
                :label="$t('core::fields.collapsed_by_default')"
              />

              <IFormCheckbox
                v-if="!element.isPrimary && !element.readonly"
                v-model:checked="element.isRequired"
                @change="$event ? (element[visibilityKey] = true) : ''"
              >
                <span class="inline-flex items-center">
                  <span v-t="'core::fields.is_required'" />

                  <span
                    v-show="element.isRequired"
                    class="ml-1.5 mt-0.5 text-xs text-neutral-500 dark:text-neutral-300"
                  >
                    {{
                      !isCreateView
                        ? $t('core::fields.option_enabled_will_propagate', {
                            view_name: isUpdateView
                              ? $t('core::fields.settings.detail')
                              : $t('core::fields.settings.update'),
                          })
                        : ''
                    }}
                  </span>
                </span>
              </IFormCheckbox>
            </div>
          </li>
        </template>
      </SortableDraggable>
    </ul>
  </ICard>
</template>

<script setup>
import { computed, ref, unref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'

import { useApp } from '@/Core/composables/useApp'

const props = defineProps({
  group: { required: true, type: String },
  view: { required: true, type: String },
  heading: { type: String, required: true },
  subHeading: { type: String, required: true },
  collapseOption: { default: true, type: Boolean },
  lazy: { default: true, type: Boolean },
})

const emit = defineEmits(['deleteRequested', 'updateRequested', 'saved'])

const { t } = useI18n()
const route = useRoute()
const { scriptConfig, resetStoreState } = useApp()

const search = ref(null)
const fieldsLoaded = ref(false)
const fieldsVisible = ref(false)
const fields = ref([])
const saving = ref(false)
const resetting = ref(false)
const fetching = ref(false)

const requestInProgress = computed(
  () => unref(saving) || unref(resetting) || unref(fetching)
)

const filteredFields = computed({
  set(value) {
    fields.value = value.map(field => {
      if (field.isRequired) {
        field[unref(visibilityKey)] = true
      }

      if (!field.isUnique && field.canUnmarkUnique) {
        field.uniqueUnmarked = true
      }

      return field
    })
  },
  get() {
    if (search.value) {
      return fields.value.filter(field =>
        field.label.toLowerCase().includes(search.value.toLowerCase())
      )
    }

    return fields.value
  },
})

const isUpdateView = computed(
  () => props.view === scriptConfig('fields.views.update')
)

const isDetailView = computed(
  () => props.view === scriptConfig('fields.views.detail')
)

const isCreateView = computed(
  () => props.view === scriptConfig('fields.views.create')
)

const visibilityKey = computed(() => {
  if (isCreateView.value) {
    return 'showOnCreation'
  } else if (isUpdateView.value) {
    return 'showOnUpdate'
  } else if (isDetailView.value) {
    return 'showOnDetail'
  }

  return ''
})

function createRequestUri() {
  return '/fields/settings/' + props.group + '/' + props.view
}

function createRequestData() {
  let data = {}

  fields.value.forEach((field, index) => {
    let fieldObject = {
      order: index + 1,
      [unref(visibilityKey)]: field.isRequired
        ? true
        : field[unref(visibilityKey)],
      isRequired: field.isRequired,
    }

    if (field.canUnmarkUnique) {
      fieldObject.uniqueUnmarked = field.uniqueUnmarked || false
    }

    if (props.collapseOption) {
      fieldObject.collapsed = field.collapsed
    }

    data[field.attribute] = fieldObject
  })

  return data
}

function requestEdit(field) {
  emit('updateRequested', field)
}

function requestDelete(id) {
  emit('deleteRequested', id)
}

function submit(userAction) {
  saving.value = true

  Innoclapps.request()
    .post(createRequestUri(), createRequestData(), {
      params: {
        intent: props.view,
      },
    })
    .then(() => {
      resetStoreState()

      if (userAction) {
        Innoclapps.success(t('core::fields.configured'))
        emit('saved')
      }
    })
    .finally(() => (saving.value = false))
}

function toggle() {
  if (props.lazy && fieldsLoaded.value === false) {
    fetch()
  }

  fieldsVisible.value = !fieldsVisible.value
}

function reset() {
  resetting.value = true

  Innoclapps.request()
    .delete(createRequestUri() + '/reset', {
      params: {
        intent: props.view,
      },
    })
    .then(({ data }) => {
      filteredFields.value = data
      resetStoreState()
      Innoclapps.success(t('core::fields.reseted'))
    })
    .finally(() => (resetting.value = false))
}

async function fetch() {
  fetching.value = true

  let { data } = await Innoclapps.request(createRequestUri(), {
    params: {
      intent: props.view,
    },
  })

  fetching.value = false
  filteredFields.value = data
  fieldsLoaded.value = true
}

if (!props.lazy) {
  fetch()
}

if (route.query.view && route.query.view === props.view) {
  toggle()
}

watch(() => props.group, fetch)

defineExpose({ fetch, submit })
</script>
