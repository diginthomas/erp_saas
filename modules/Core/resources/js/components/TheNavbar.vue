<template>
  <div
    class="relative z-20 flex h-navbar shrink-0 bg-white shadow dark:bg-neutral-700"
  >
    <button
      v-once
      type="button"
      class="border-r border-neutral-200 px-3 text-neutral-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 md:hidden dark:border-neutral-600 dark:text-neutral-200"
      @click="setSidebarOpenState(true)"
    >
      <span class="sr-only">Open sidebar</span>

      <Icon icon="Bars3BottomLeft" class="size-6" />
    </button>

    <div class="flex flex-1 justify-between pr-4 sm:pr-6 lg:pr-8">
      <div class="flex flex-1">
        <div v-show="navbarTitle" class="mx-8 hidden max-w-xs py-5 lg:block">
          <h1
            class="truncate font-semibold uppercase text-neutral-800 dark:text-neutral-100"
            v-text="navbarTitle"
          />
        </div>

        <span
          v-show="navbarTitle"
          class="hidden h-navbar border-l border-neutral-200 lg:block dark:border-neutral-600"
        />

        <div class="flex w-full sm:relative">
          <div class="relative flex w-full">
            <label for="navSearchInput" class="sr-only">Search</label>

            <div
              class="relative w-full text-neutral-400 focus-within:text-neutral-600 dark:focus-within:text-neutral-200"
            >
              <div
                class="pointer-events-none absolute inset-y-0 left-6 flex items-center"
              >
                <Icon
                  icon="SearchSolid"
                  :class="[
                    'size-5',
                    searchRequestInProgress ? 'animate-pulse' : '',
                  ]"
                />
              </div>

              <input
                id="navSearchInput"
                ref="searchInputRef"
                v-model="searchValue"
                v-memo="[searchValue]"
                autocomplete="off"
                class="peer block h-full w-full appearance-none border-transparent py-2 pl-14 pr-3 text-neutral-900 placeholder-neutral-500 focus:border-transparent focus:placeholder-neutral-400 focus:outline-none focus:ring-0 sm:text-sm dark:bg-neutral-700 dark:text-neutral-200 dark:placeholder-neutral-400 dark:focus:placeholder-neutral-500"
                :placeholder="$t('core::app.search')"
                type="search"
                name="search"
                @keydown.enter="performSearch(searchValue)"
                @input="performSearch($event.target.value)"
              />

              <div
                v-if="shouldUseSearchKeyboardShortcut"
                v-memo="[shouldUseSearchKeyboardShortcut, Boolean(searchValue)]"
                class="absolute left-56 top-[1.1rem] hidden peer-focus:hidden lg:block"
              >
                <kbd
                  v-show="Boolean(searchValue) === false"
                  class="inline-flex items-center rounded border border-neutral-300 px-2 font-sans text-sm font-bold text-neutral-500 dark:border-neutral-300 dark:text-neutral-300"
                >
                  {{ searchKeyboardShortcutMainKey }}&nbsp;{{
                    searchKeyboardShortcutKey
                  }}
                </kbd>
              </div>
            </div>
          </div>

          <Teleport to="body">
            <Transition
              enter-active-class="transition duration-200 ease-out"
              enter-from-class="translate-y-1 opacity-0"
              enter-to-class="translate-y-0 opacity-100"
              leave-active-class="transition duration-150 ease-in"
              leave-from-class="translate-y-0 opacity-100"
              leave-to-class="translate-y-1 opacity-0"
            >
              <div
                v-show="showSearchResults"
                ref="searchResultsRef"
                class="absolute left-0 top-[--navbar-height] z-30 w-screen max-w-sm transform md:left-[225px] lg:left-[340px] lg:max-w-lg"
              >
                <div
                  class="overflow-hidden rounded-b-lg shadow-lg ring-1 ring-neutral-600 ring-opacity-5 dark:ring-neutral-700"
                >
                  <div class="bg-white dark:bg-neutral-800">
                    <div
                      v-if="hasSearchResults"
                      class="max-h-screen overflow-y-auto px-5 py-3 lg:max-h-[40rem]"
                    >
                      <span v-for="result in searchResults" :key="result.title">
                        <p
                          class="mb-1.5 mt-3 text-sm font-medium text-neutral-900 dark:text-white"
                          v-text="result.title"
                        />

                        <ILink
                          v-for="resource in result.data"
                          :key="resource.path"
                          :to="resource.path"
                          class="group relative mb-2 block whitespace-normal rounded-lg border border-neutral-100 bg-neutral-50 py-3 pl-5 pr-12 text-sm text-neutral-800 hover:border-primary-700 hover:bg-primary-600 hover:text-white dark:border-neutral-600 dark:bg-neutral-700 dark:text-white dark:hover:border-primary-600 dark:hover:bg-primary-600"
                          plain
                          @click="showSearchResults = false"
                        >
                          <span class="block truncate font-medium">
                            {{ resource.display_name }}
                          </span>

                          <span
                            v-if="resource.created_at"
                            class="text-neutral-500 group-hover:text-primary-200 dark:text-neutral-300"
                          >
                            {{ $t('core::app.created_at') }}
                            {{ localizedDateTime(resource.created_at) }}
                          </span>

                          <Icon
                            icon="ChevronRight"
                            class="absolute right-4 top-6 size-4"
                          />
                        </ILink>
                      </span>
                    </div>

                    <div
                      v-if="!hasSearchResults"
                      v-t="'core::app.no_search_results'"
                      class="p-3 text-center text-sm text-neutral-700 dark:text-neutral-200"
                    />
                  </div>
                </div>
              </div>
            </Transition>
          </Teleport>
        </div>
      </div>

      <div class="ml-3 flex items-center lg:ml-6">
        <IButtonIcon
          v-once
          id="header__moon"
          v-i-tooltip.bottom="$t('core::app.theme.switch_light')"
          class="md:block"
          icon="Moon"
          @click="toLightMode"
        />

        <IButtonIcon
          v-once
          id="header__sun"
          v-i-tooltip.bottom="$t('core::app.theme.switch_system')"
          icon="Sun"
          @click="toSystemMode"
        />

        <IButtonIcon
          v-once
          id="header__indeterminate"
          v-i-tooltip.bottom="$t('core::app.theme.switch_dark')"
          icon="Sun"
          @click="toDarkMode"
        >
          <svg class="size-5 text-neutral-400" viewBox="0 0 24 24">
            <path
              fill="currentColor"
              d="M12 2A10 10 0 0 0 2 12A10 10 0 0 0 12 22A10 10 0 0 0 22 12A10 10 0 0 0 12 2M12 4A8 8 0 0 1 20 12A8 8 0 0 1 12 20V4Z"
            ></path>
          </svg>
        </IButtonIcon>

        <NavbarSeparator v-once />
        <!-- Notifications -->
        <div class="mr-1 lg:mr-3">
          <NavbarNotifications />
        </div>
        <!-- Quick create dropdown -->
        <div v-once class="hidden md:block">
          <NavbarQuickCreate />
        </div>
        <!-- Teleport target -->
        <div id="navbar-actions" class="hidden items-center lg:flex"></div>
        <!-- Profile dropdown -->
        <IDropdown placement="bottom-end">
          <template #toggle="{ toggle }">
            <button
              type="button"
              class="ml-1.5 max-w-xs items-center rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 md:hidden"
              @click="toggle"
            >
              <IAvatar
                :src="currentUser.avatar_url"
                :title="currentUser.name"
              />
            </button>
          </template>

          <div
            v-once
            class="divide-y divide-neutral-200 dark:divide-neutral-700"
          >
            <div class="py-1">
              <IDropdownItem
                :to="{ name: 'profile' }"
                :text="$t('users::profile.profile')"
              />

              <IDropdownItem
                :to="{ name: 'calendar-sync' }"
                :text="$t('activities::calendar.calendar_sync')"
              />

              <IDropdownItem
                :to="{ name: 'oauth-accounts' }"
                :text="$t('core::oauth.connected_accounts')"
              />

              <IDropdownItem
                v-if="currentUser.access_api"
                :to="{ name: 'personal-access-tokens' }"
                :text="$t('core::api.personal_access_tokens')"
              />
            </div>

            <div class="py-1">
              <IDropdownItem
                href="#"
                :text="$t('auth.logout')"
                @click="logout"
              />
            </div>
          </div>
        </IDropdown>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, shallowRef, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useStore } from 'vuex'
import { onClickOutside } from '@vueuse/core'
import debounce from 'lodash/debounce'

import { useApp } from '@/Core/composables/useApp'
import { useDates } from '@/Core/composables/useDates'

import { usePageTitle } from '../composables/usePageTitle'

import NavbarNotifications from './TheNavbarNotifications.vue'
import NavbarQuickCreate from './TheNavbarQuickCreate.vue'

const route = useRoute()
const store = useStore()
const { localizedDateTime } = useDates()
const { currentUser, logout } = useApp()

const searchResultsRef = ref(null)

onClickOutside(searchResultsRef, () => clearSearch())

function setSidebarOpenState(value) {
  store.commit('SET_SIDEBAR_OPEN', value)
}

const searchValue = ref(null)
const searchResults = shallowRef([])
const hasSearchResults = computed(() => searchResults.value.length > 0)
const showSearchResults = ref(false)
const searchRequestInProgress = ref(false)
const navbarTitle = usePageTitle()
const isMacintosh = /mac/.test(window.userAgent)
const searchKeyboardShortcutMainKey = isMacintosh ? '⌘' : 'Ctrl'

const searchKeyboardShortcutKey = 'K'

const shouldUseSearchKeyboardShortcut =
  !/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(window.navigator.userAgent)

const searchInputRef = ref(null)

function toLightMode() {
  localStorage.theme = 'light'
  window.updateTheme()
}

function toDarkMode() {
  localStorage.theme = 'dark'
  window.updateTheme()
}

function toSystemMode() {
  localStorage.theme = 'system'
  window.updateTheme()
}

function clearSearch() {
  searchResults.value = []
  showSearchResults.value = false
}

const performSearch = debounce(function (value) {
  if (!value) {
    searchResults.value = []
    showSearchResults.value = false

    return
  }

  searchRequestInProgress.value = true

  Innoclapps.request('/search', { params: { q: value } })
    .then(({ data }) => {
      searchResults.value = data
      showSearchResults.value = true
    })
    .finally(() => (searchRequestInProgress.value = false))
}, 650)

onMounted(() => {
  window.addEventListener('keydown', e => {
    if (e.key === 'esc' && showSearchResults.value) {
      clearSearch()
    }
  })

  if (shouldUseSearchKeyboardShortcut) {
    document.addEventListener('keydown', e => {
      if (
        (e.ctrlKey || e.metaKey) &&
        e.key === searchKeyboardShortcutKey.toLowerCase()
      ) {
        e.preventDefault()

        if (document.activeElement === searchInputRef.value) {
          searchInputRef.value.blur()
          clearSearch()
        } else {
          searchInputRef.value.focus()
        }
      }
    })
  }
})

// Clear the search value when navigating to different route
watch(
  () => route.path,
  (newVal, oldVal) => {
    if (oldVal !== '/' && searchValue.value) {
      searchValue.value = null
    }
  }
)
</script>

<style>
#header__sun,
#header__moon,
#header__indeterminate {
  display: none;
}

html[color-theme='dark'] #header__moon {
  display: block;
}

html[color-theme='light'] #header__sun {
  display: block;
}

html[color-theme='system'] #header__indeterminate {
  display: block;
}
</style>
