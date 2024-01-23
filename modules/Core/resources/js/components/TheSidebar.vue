<template>
  <!-- Sidebar for mobile -->
  <TransitionRoot as="template" :show="isSidebarOpen">
    <Dialog
      as="div"
      static
      class="fixed inset-0 z-50 flex md:hidden"
      :open="isSidebarOpen"
      @close="setSidebarOpenState(false)"
    >
      <TransitionChild
        as="template"
        enter="transition-opacity ease-linear duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="transition-opacity ease-linear duration-300"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div
          class="fixed inset-0 bg-neutral-900/75 transition-opacity dark:bg-neutral-700/90"
        />
      </TransitionChild>

      <TransitionChild
        as="template"
        enter="transition ease-in-out duration-300 transform"
        enter-from="-translate-x-full"
        enter-to="translate-x-0"
        leave="transition ease-in-out duration-300 transform"
        leave-from="translate-x-0"
        leave-to="-translate-x-full"
      >
        <DialogPanel
          class="relative flex w-56 max-w-xs flex-col bg-neutral-800 pb-4 pt-5 dark:bg-neutral-900"
        >
          <TransitionChild
            as="template"
            enter="ease-in-out duration-300"
            enter-from="opacity-0"
            enter-to="opacity-100"
            leave="ease-in-out duration-300"
            leave-from="opacity-100"
            leave-to="opacity-0"
          >
            <div class="absolute right-0 top-0 -mr-12 pt-2">
              <button
                type="button"
                class="ml-1 flex size-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                @click="setSidebarOpenState(false)"
              >
                <span class="sr-only">Close sidebar</span>

                <Icon icon="X" class="size-6 text-white" />
              </button>
            </div>
          </TransitionChild>

          <div class="flex shrink-0 items-center px-4">
            <ILink class="whitespace-normal" :to="{ name: 'dashboard' }" plain>
              <span v-if="!logo" class="font-bold text-white">
                {{ companyName }}
              </span>

              <img v-else :src="logo" class="h-10 max-h-14 w-auto" />
            </ILink>
          </div>

          <div class="mt-5 h-0 flex-1 overflow-y-auto">
            <nav id="sidebar-sm" class="space-y-0.5 px-2 text-sm">
              <ILink
                v-for="item in sidebarNavigation"
                :key="item.id"
                :class="[
                  'group relative flex items-center rounded-md px-2 py-2 focus:outline-none',
                  'sidebar-sm-item-' + item.id,
                ]"
                active-class="bg-neutral-700 text-white"
                inactive-class="text-neutral-50 hover:bg-neutral-600"
                :to="item.route"
                plain
              >
                <Icon
                  v-if="item.icon"
                  :icon="item.icon"
                  class="mr-4 size-6 shrink-0 text-neutral-300"
                />

                {{ item.name }}

                <IBadge
                  v-if="item.badge"
                  :variant="item.badgeVariant"
                  size="circle"
                  wrapper-class="absolute -left-px -top-px"
                  :text="item.badge"
                />

                <ILink
                  v-if="item.inQuickCreate"
                  :to="item.quickCreateRoute"
                  :class="[
                    'ml-auto rounded-md hover:bg-neutral-800',
                    $route.path === item.quickCreateRoute ? 'hidden' : '',
                  ]"
                  plain
                >
                  <Icon icon="Plus" class="size-5"></Icon>
                </ILink>
              </ILink>
            </nav>
          </div>

          <TheSidebarMetrics />
        </DialogPanel>
      </TransitionChild>
    </Dialog>
  </TransitionRoot>

  <!-- Static sidebar for desktop -->
  <div
    v-show="['404', '403', 'not-found'].indexOf($route.name) === -1"
    class="hidden bg-neutral-800 md:flex md:shrink-0 dark:bg-neutral-900"
  >
    <div class="flex w-56 flex-col">
      <!-- Sidebar component, swap this element with another sidebar if you like -->
      <div class="flex grow flex-col overflow-y-auto pb-4 pt-5">
        <div class="flex shrink-0 items-center px-4">
          <ILink class="whitespace-normal" :to="{ name: 'dashboard' }" plain>
            <span v-if="!logo" class="font-bold text-white">
              {{ companyName }}
            </span>

            <img v-else :src="logo" class="h-10 max-h-14 w-auto" />
          </ILink>
        </div>

        <!-- Profile dropdown -->
        <div class="relative mt-4 inline-block px-3 text-left">
          <IDropdown items-class="max-w-[200px]">
            <template #toggle="{ toggle }">
              <button
                type="button"
                class="group mt-3 w-full rounded-md bg-neutral-200 px-3.5 py-2 text-left text-sm font-medium text-neutral-700 ring-1 ring-neutral-200 hover:bg-neutral-300 focus:bg-neutral-300 focus:outline-none dark:bg-neutral-800 dark:ring-neutral-600 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                @click="toggle"
              >
                <span class="flex w-full items-center justify-between">
                  <span
                    class="flex min-w-0 items-center justify-between space-x-3"
                  >
                    <IAvatar
                      :src="currentUser.avatar_url"
                      :title="currentUser.name"
                    />

                    <span class="flex min-w-0 flex-1 flex-col">
                      <span
                        class="truncate text-sm font-medium text-neutral-800 dark:text-white"
                      >
                        {{ currentUser.name }}
                      </span>

                      <span
                        class="truncate text-sm text-neutral-600 dark:text-neutral-300"
                      >
                        {{ currentUser.email }}
                      </span>
                    </span>
                  </span>

                  <Icon
                    icon="Selector"
                    class="size-5 shrink-0 text-neutral-500 group-hover:text-neutral-600 dark:text-neutral-400 dark:group-hover:text-neutral-300"
                  />
                </span>
              </button>
            </template>

            <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
              <div v-if="currentUser.teams.length > 0" class="px-4 py-3">
                <p
                  class="inline-flex items-center text-sm text-neutral-800 dark:text-neutral-100"
                >
                  <Icon
                    icon="UserGroup"
                    class="mr-1 size-5 text-neutral-600 dark:text-neutral-400"
                  />

                  <span
                    v-text="
                      $t('users::team.your_teams', currentUser.teams.length)
                    "
                  />
                </p>

                <p
                  v-for="team in currentUser.teams"
                  :key="team.id"
                  class="flex text-sm font-medium text-neutral-900 dark:text-neutral-300"
                >
                  <span
                    :class="[
                      'truncate',
                      team.user_id === currentUser.id
                        ? 'text-primary-600 dark:text-primary-400'
                        : '',
                    ]"
                    v-text="team.name"
                  />
                </p>
              </div>

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

        <!-- Sidebar links -->
        <div class="mt-6 flex h-0 flex-1 flex-col overflow-y-auto">
          <nav id="sidebar-lg" class="flex-1 space-y-1 px-2">
            <ILink
              v-for="item in sidebarNavigation"
              :key="item.id"
              :class="[
                'group relative flex items-center rounded-md px-2 py-2 text-sm focus:outline-none',
                'sidebar-lg-item-' + item.id,
              ]"
              active-class="bg-neutral-700 text-white"
              inactive-class="text-neutral-50 hover:bg-neutral-600"
              :to="item.route"
              plain
            >
              <Icon
                v-if="item.icon"
                :icon="item.icon"
                class="mr-3 size-6 shrink-0 text-neutral-300"
              />

              {{ item.name }}

              <IBadge
                v-if="item.badge"
                :variant="item.badgeVariant"
                size="circle"
                wrapper-class="absolute -left-px -top-px"
                :text="item.badge"
              />

              <ILink
                v-if="item.inQuickCreate"
                :to="item.quickCreateRoute"
                :class="[
                  'ml-auto hidden rounded-md hover:bg-neutral-800',
                  $route.path !== item.quickCreateRoute
                    ? 'group-hover:block'
                    : '',
                ]"
                plain
              >
                <Icon icon="Plus" class="size-5"></Icon>
              </ILink>
            </ILink>
          </nav>
        </div>

        <TheSidebarMetrics />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useStore } from 'vuex'
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'

import { useApp } from '@/Core/composables/useApp'

import TheSidebarMetrics from './TheSidebarMetrics.vue'

const { currentUser, logout, setting } = useApp()
const store = useStore()

const sidebarNavigation = computed(() => store.state.sidebarMenuItems)
const isSidebarOpen = computed(() => store.state.sidebarOpen)
const companyName = computed(() => setting('company_name'))
const logo = setting('logo_light')

function setSidebarOpenState(value) {
  store.commit('SET_SIDEBAR_OPEN', value)
}
</script>
