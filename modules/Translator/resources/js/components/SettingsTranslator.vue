<template>
  <ICard :header="$t('translator::translator.translator')" no-body>
    <template #actions>
      <div class="flex items-center space-x-3">
        <IDropdownSelect
          v-model="locale"
          :items="locales"
          placement="bottom-end"
          @change="getTranslations"
        />

        <IButton
          v-dialog="'newLocaleModal'"
          icon="plus"
          :text="$t('translator::translator.new_locale')"
        />
      </div>
    </template>

    <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
      <li
        v-for="(groupTranslations, group) in translations.current.groups"
        v-show="!activeGroup || activeGroup === group"
        :key="group"
      >
        <div class="hover:bg-neutral-100 dark:hover:bg-neutral-700/60">
          <div class="flex items-center">
            <div class="grow">
              <ILink
                variant="neutral"
                class="block px-6 py-2 font-medium"
                :text="strTitle(group.replace('_', ' '))"
                @click="toggleGroup(group)"
              />
            </div>

            <div class="ml-2 py-2 pr-6">
              <IButton
                variant="white"
                icon="ChevronDown"
                @click="toggleGroup(group)"
              />
            </div>
          </div>
        </div>

        <form
          v-show="activeGroup === group"
          novalidate="true"
          @submit.prevent="saveGroup(group)"
        >
          <ITable bordered="y">
            <thead>
              <tr>
                <th class="text-left" width="15%">Key</th>

                <th class="text-left" width="30%">Source</th>

                <th class="text-left" width="55%">{{ locale }}</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="(translation, key) in groupTranslations" :key="key">
                <td width="15%" class="font-medium" v-text="key" />

                <td
                  width="30%"
                  v-text="translations.source.groups[group][key]"
                />

                <td width="55%">
                  <SettingsTranslatorTranslate
                    v-model="translations.current.groups[group][key]"
                    :source="translations.source.groups[group][key]"
                  />
                </td>
              </tr>
            </tbody>
          </ITable>

          <div
            class="-mt-px flex items-center justify-end space-x-3 bg-neutral-50 px-6 py-3 dark:bg-neutral-700"
          >
            <IButton
              :disabled="groupIsBeingSaved"
              :text="$t('core::app.cancel')"
              variant="white"
              @click="deactivateGroup(group, true)"
            />

            <IButton
              type="submit"
              :text="$t('core::app.save')"
              :disabled="groupIsBeingSaved"
              :loading="groupIsBeingSaved"
            />
          </div>
        </form>
      </li>
    </ul>

    <template
      v-for="(namespaceGroupTranslations, namespace) in translations.current
        .namespaces"
      :key="namespace"
    >
      <p
        v-show="!activeGroup"
        class="border-y border-neutral-200 bg-neutral-100 px-6 py-3 font-semibold text-neutral-700 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-300"
      >
        {{ strTitle(namespace) }}
      </p>

      <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
        <li
          v-for="(groupTranslations, group) in translations.current.namespaces[
            namespace
          ]"
          v-show="
            !activeGroup ||
            (activeGroup === group && activeNamespace === namespace)
          "
          :key="group"
        >
          <div class="hover:bg-neutral-100 dark:hover:bg-neutral-700/60">
            <div class="flex items-center">
              <div class="grow">
                <ILink
                  variant="neutral"
                  class="block px-6 py-2 font-medium"
                  :text="strTitle(group.replace('_', ' '))"
                  @click="toggleGroup(group, namespace)"
                />
              </div>

              <div class="ml-2 py-2 pr-6">
                <IButton
                  variant="white"
                  icon="ChevronDown"
                  @click="toggleGroup(group, namespace)"
                />
              </div>
            </div>
          </div>

          <form
            v-show="activeGroup === group && activeNamespace === namespace"
            novalidate="true"
            @submit.prevent="saveGroup(group, namespace)"
          >
            <ITable bordered="y">
              <thead>
                <tr>
                  <th class="text-left" width="15%">Key</th>

                  <th class="text-left" width="30%">Source</th>

                  <th class="text-left" width="55%">{{ locale }}</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="(translation, key) in groupTranslations" :key="key">
                  <td width="15%" class="font-medium" v-text="key" />

                  <td
                    width="30%"
                    v-text="
                      translations.source.namespaces[namespace][group][key]
                    "
                  />

                  <td width="55%">
                    <SettingsTranslatorTranslate
                      v-model="
                        translations.current.namespaces[namespace][group][key]
                      "
                      :source="
                        translations.source.namespaces[namespace][group][key]
                      "
                    />
                  </td>
                </tr>
              </tbody>
            </ITable>

            <div
              class="-mt-px flex items-center justify-end space-x-3 bg-neutral-50 px-6 py-3 dark:bg-neutral-700"
            >
              <IButton
                :disabled="groupIsBeingSaved"
                :text="$t('core::app.cancel')"
                variant="white"
                @click="deactivateGroup(group, true)"
              />

              <IButton
                type="submit"
                :text="$t('core::app.save')"
                :disabled="groupIsBeingSaved"
                :loading="groupIsBeingSaved"
              />
            </div>
          </form>
        </li>
      </ul>
    </template>

    <IModal
      id="newLocaleModal"
      size="sm"
      :ok-text="$t('core::app.create')"
      :title="$t('translator::translator.create_new_locale')"
      form
      @submit="createLocale"
      @shown="() => $refs.inputNameRef.focus()"
    >
      <IFormGroup
        label-for="localeName"
        :label="$t('translator::translator.locale_name')"
        required
      >
        <IFormInput
          id="localeName"
          ref="inputNameRef"
          v-model="localeForm.name"
        />

        <IFormError :error="localeForm.getError('name')" />
      </IFormGroup>
    </IModal>
  </ICard>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { onBeforeRouteLeave, useRoute } from 'vue-router'
import cloneDeep from 'lodash/cloneDeep'
import isEqual from 'lodash/isEqual'

import { useApp } from '@/Core/composables/useApp'
import { useForm } from '@/Core/composables/useForm'

import SettingsTranslatorTranslate from './SettingsTranslatorTranslate.vue'

const route = useRoute()
const { t } = useI18n()
const { appUrl, scriptConfig, locales } = useApp()

const { form: localeForm } = useForm({ name: null })

let originalTranslations = {}

// Active locale
const locale = ref(route.query.locale || scriptConfig('locale'))

// Active locale groups translation
const translations = ref({
  source: {
    groups: {},
    namespaces: {},
  },
  current: {
    groups: {},
    namespaces: {},
  },
})

const activeGroup = ref(null)
const activeNamespace = ref(null)
const groupIsBeingSaved = ref(false)

onBeforeRouteLeave((to, from, next) => {
  const unsaved = getUnsavedTranslationGroups()

  if (unsaved.length > 0) {
    Innoclapps.confirm({
      message: t('translator::translator.changes_not_saved'),
      title: 'Are you sure you want to leave this page?',
      confirmText: t('core::app.discard_changes'),
    })
      .then(() => next())
      .catch(() => next(false))
  } else {
    next()
  }
})

function getUnsavedTranslationGroups() {
  let groups = []
  let originalGroups = {}
  let currentGroups = {}

  if (activeNamespace.value) {
    groups = Object.keys(originalTranslations.namespaces[activeNamespace.value])
    originalGroups = originalTranslations.namespaces[activeNamespace.value]
    currentGroups = translations.value.current.namespaces[activeNamespace.value]
  } else {
    groups = Object.keys(originalTranslations.groups)
    originalGroups = originalTranslations.groups
    currentGroups = translations.value.current.groups
  }

  let unsaved = []

  groups.forEach(group => {
    if (!isEqual(originalGroups[group], currentGroups[group])) {
      unsaved.push(group)
    }
  })

  return unsaved
}

function saveGroup(group, namespace = null) {
  groupIsBeingSaved.value = true

  let payload = null

  if (namespace) {
    payload = translations.value.current.namespaces[namespace][group]
  } else {
    payload = translations.value.current.groups[group]
  }

  Innoclapps.request()
    .put(`/translation/${locale.value}/${group}`, {
      translations: payload,
      namespace: namespace,
    })
    .then(() => {
      window.location.href = `${appUrl}/settings/translator?locale=${locale.value}`
    })
    .finally(() => setTimeout(() => (groupIsBeingSaved.value = false), 1000))
}

function getTranslations(locale) {
  Innoclapps.request(`/translation/${locale}`).then(({ data }) => {
    originalTranslations = cloneDeep(data.current)
    translations.value = data
  })
}

function createLocale() {
  localeForm.post('/translation').then(data => {
    locales.value.push(data.locale)
    locale.value = data.locale
    getTranslations(data.locale)
    Innoclapps.dialog().hide('newLocaleModal')
  })
}

function toggleGroup(group, namespace = null) {
  if (activeGroup.value) {
    deactivateGroup(group)

    return
  }

  activateGroup(group, namespace)
}

function activateGroup(group, namespace = null) {
  activeGroup.value = group
  activeNamespace.value = namespace
}

function deactivateGroup(group, skipConfirmation = false) {
  const unsaved = getUnsavedTranslationGroups()
  let namespace = activeNamespace.value
  const groupIsModified = unsaved.indexOf(group) > -1

  if (skipConfirmation || !groupIsModified) {
    activeGroup.value = null
    activeNamespace.value = null

    // Replace only when group group modified
    if (groupIsModified) {
      replaceOriginalTranslations(group, namespace)
    }

    return
  }

  Innoclapps.confirm({
    message: t('translator::translator.changes_not_saved'),
    title: 'The group has unsaved translations!',
    confirmText: t('core::app.discard_changes'),
  }).then(() => {
    activeNamespace.value = null
    activeGroup.value = null
    replaceOriginalTranslations(group, namespace)
  })
}

function replaceOriginalTranslations(group, namespace = null) {
  if (namespace) {
    translations.value.current.namespaces[namespace][group] = cloneDeep(
      originalTranslations.namespaces[namespace][group]
    )

    return
  }

  translations.value.current.groups[group] = cloneDeep(
    originalTranslations.groups[group]
  )
}

function strTitle(str) {
  str = str.toLowerCase().split(' ')

  for (var i = 0; i < str.length; i++) {
    str[i] = str[i].charAt(0).toUpperCase() + str[i].slice(1)
  }

  return str.join(' ')
}

getTranslations(locale.value)
</script>
