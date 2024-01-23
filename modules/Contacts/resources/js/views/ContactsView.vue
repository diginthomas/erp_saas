<template>
  <MainLayout :overlay="!componentReady">
    <div class="mx-auto max-w-7xl">
      <IAlert
        v-if="componentReady && !resource.authorizations.view"
        class="mb-6"
        variant="warning"
        :text="$t('core::role.view_non_authorized_after_record_create')"
      />

      <div v-if="componentReady" class="relative">
        <div
          class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900"
        >
          <div class="bg-white px-3 py-4 sm:p-6 dark:bg-neutral-900">
            <div class="flex grow flex-col lg:flex-row lg:items-center">
              <div
                class="overflow-hidden text-center lg:flex lg:grow lg:items-center lg:space-x-4 lg:text-left"
              >
                <IAvatar
                  :src="resource.avatar_url"
                  :title="resource.name"
                  class="lg:shrink-0 lg:self-start"
                />

                <div class="space-y-2 overflow-hidden lg:space-y-0">
                  <div class="lg:flex lg:items-center lg:space-x-4">
                    <h1 class="relative truncate">
                      <span
                        :class="[
                          'text-2xl font-bold text-neutral-900 dark:text-white',
                          {
                            'cursor-pointer rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-700':
                              resource.authorizations.update,
                          },
                        ]"
                        @click="
                          resource.authorizations.update &&
                            $refs.namePopoverRef.show()
                        "
                        v-text="resource.display_name"
                      />

                      <!-- Use popover separately so the truncate class works fine on the h1 tag -->
                      <IPopover
                        v-if="resource.authorizations.update"
                        ref="namePopoverRef"
                        class="w-80"
                        @show="
                          ;(fullNameForm.first_name = resource.first_name),
                            (fullNameForm.last_name = resource.last_name)
                        "
                        @hide="fullNameForm.errors.clear()"
                      >
                        <!-- Absolute centering, a tricky way to center the dropdown -->
                        <button
                          type="button"
                          class="hide absolute left-1/2 top-1/2 mt-3 size-0 -translate-x-1/2 -translate-y-1/2 transform"
                        />

                        <template #popper>
                          <div class="p-4">
                            <IFormGroup
                              required
                              :label="
                                $t('contacts::fields.contacts.first_name')
                              "
                              label-for="editFirstName"
                            >
                              <IFormInput
                                id="editFirstName"
                                v-model="fullNameForm.first_name"
                                class="font-normal"
                                @keydown.enter="updateFullName"
                                @keydown="
                                  fullNameForm.errors.clear('first_name')
                                "
                              />

                              <IFormError
                                :error="fullNameForm.getError('first_name')"
                              />
                            </IFormGroup>

                            <IFormGroup
                              :label="$t('contacts::fields.contacts.last_name')"
                              label-for="editLastName"
                            >
                              <IFormInput
                                id="editLastName"
                                v-model="fullNameForm.last_name"
                                class="font-normal"
                                @keydown.enter="updateFullName"
                                @keydown="
                                  fullNameForm.errors.clear('last_name')
                                "
                              />

                              <IFormError
                                :error="fullNameForm.getError('last_name')"
                              />
                            </IFormGroup>
                          </div>

                          <div
                            class="flex justify-end space-x-1 bg-neutral-100 px-4 py-3 dark:bg-neutral-900"
                          >
                            <IButton
                              variant="white"
                              :disabled="fullNameForm.busy"
                              :text="$t('core::app.cancel')"
                              @click="() => $refs.namePopoverRef.hide()"
                            />

                            <IButton
                              variant="primary"
                              :loading="fullNameForm.busy"
                              :disabled="
                                fullNameForm.busy || !fullNameForm.first_name
                              "
                              :text="$t('core::app.save')"
                              @click="updateFullName"
                            />
                          </div>
                        </template>
                      </IPopover>
                    </h1>

                    <div class="inline-block lg:shrink-0">
                      <TagsSelectInput
                        :disabled="!resource.authorizations.update"
                        :type="$scriptConfig('contacts.tags_type')"
                        :model-value="resource.tags"
                        simple
                        @update:model-value="updateResource({ tags: $event })"
                      />
                    </div>
                  </div>

                  <span
                    v-if="resource.job_title"
                    class="block overflow-hidden truncate text-sm text-neutral-500 dark:text-neutral-200"
                  >
                    {{
                      isAssociateToOneCompany
                        ? $t('contacts::contact.works_at', {
                            job_title: resource.job_title,
                            company: resource.companies[0].name,
                          })
                        : resource.job_title
                    }}
                  </span>
                </div>
              </div>

              <div
                class="flex shrink-0 flex-col items-center lg:ml-6 lg:flex-row lg:space-x-2"
              >
                <IButton
                  v-show="resource.authorizations.update"
                  v-once
                  variant="success"
                  class="mr-3 mt-5 lg:mt-0 lg:shrink-0"
                  icon="Plus"
                  :to="{ name: 'createDealViaContact' }"
                  :text="$t('deals::deal.add')"
                />

                <div
                  class="mt-5 flex shrink-0 justify-center space-x-2 lg:mt-0 lg:items-center lg:justify-normal"
                >
                  <UserOwnerDropdown
                    :owner="resource.user"
                    :authorize-update="resource.authorizations.update"
                    @change="updateResource({ user_id: $event.id })"
                  />

                  <ActionSelector
                    type="dropdown"
                    :ids="resource.id || []"
                    :actions="resource.actions || []"
                    :resource-name="resourceName"
                    condensed
                    @run="handleActionExecuted"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <IMinimalDropdown
          v-if="$gate.isSuperAdmin()"
          v-once
          placement="bottom-end"
          class="absolute -top-5 right-2 rotate-90 lg:-right-7 lg:top-1.5 lg:rotate-0"
        >
          <IDropdownItem
            :text="$t('core::app.record_view.manage_sidebar')"
            @click="sidebarBeingManaged = true"
          />
        </IMinimalDropdown>
      </div>

      <div v-if="componentReady" class="mt-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-8">
          <div class="col-span-4 space-y-3">
            <div
              v-for="section in enabledSections"
              v-show="!sidebarBeingManaged"
              :key="section.id"
            >
              <component
                :is="sectionComponents[section.component] || section.component"
                :resource-name="resourceName"
                :resource-id="resource.id"
                :resource="resource"
                @updated="synchronizeResource($event, true)"
              />
            </div>

            <ManageViewSections
              v-model:sections="template.sections"
              v-model:show="sidebarBeingManaged"
              :identifier="resourceSingularName"
              class="-mt-3 inline"
              @saved="sidebarBeingManaged = false"
            />
          </div>

          <div class="col-span-8 mt-4 lg:mt-0">
            <ITabGroup :default-index="defaultTabIndex">
              <ITabList
                centered
                :bordered="false"
                list-wrapper-class="rounded-md border border-neutral-200 bg-white py-0.5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900"
              >
                <component
                  :is="tabComponents[tab.component] || tab.component"
                  v-for="tab in template.tabs"
                  :key="tab.id"
                  :resource-name="resourceName"
                  :resource-id="resource.id"
                  :resource="resource"
                />
              </ITabList>

              <ITabPanels>
                <component
                  :is="tabComponents[tab.panelComponent] || tab.panelComponent"
                  v-for="tab in template.tabs"
                  :id="'tabPanel-' + tab.id"
                  :key="tab.id"
                  :resource-name="resourceName"
                  :resource-id="resource.id"
                  :resource="resource"
                  scroll-element="#main"
                />
              </ITabPanels>
            </ITabGroup>
          </div>
        </div>
      </div>
    </div>

    <!-- Company, Deal Create -->
    <RouterView
      v-if="componentReady"
      :via-resource="resourceName"
      :parent-resource="resource"
      :go-to-list="false"
      @associated="fetchResource(), $router.back()"
      @created="
        ({ isRegularAction }) => (
          isRegularAction ? $router.back() : '', fetchResource()
        )
      "
      @hidden="$router.back"
    />
  </MainLayout>
</template>

<script setup>
import { computed, provide, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import ActionSelector from '@/Core/components/Actions/ActionSelector.vue'
import ManageViewSections from '@/Core/components/ManageViewSections.vue'
import TagsSelectInput from '@/Core/components/TagsSelectInput.vue'
import { usePrivateChannel } from '@/Core/composables/useBroadcast'
import { useForm } from '@/Core/composables/useForm'
import { useGlobalEventListener } from '@/Core/composables/useGlobalEventListener'
import { usePageTitle } from '@/Core/composables/usePageTitle'
import { useResource } from '@/Core/composables/useResource'
import TimelineTab from '@/Core/views/Timeline/RecordTabTimeline.vue'
import TimelineTabPanel from '@/Core/views/Timeline/RecordTabTimelinePanel.vue'

import UserOwnerDropdown from '@/Users/components/UserOwnerDropdown.vue'

import CompaniesSection from '../components/ContactsViewCompanies.vue'
import DealsSection from '../components/ContactsViewDeals.vue'
import DetailsSection from '../components/ContactsViewDetails.vue'
import MediaSection from '../components/ContactsViewMedia.vue'

const tabComponents = {
  'timeline-tab': TimelineTab,
  'timeline-tab-panel': TimelineTabPanel,
}

const sectionComponents = {
  'details-section': DetailsSection,
  'deals-section': DealsSection,
  'companies-section': CompaniesSection,
  'media-section': MediaSection,
}

const resourceName = Innoclapps.resourceName('contacts')

const router = useRouter()
const route = useRoute()
const { form: fullNameForm } = useForm({ first_name: null, last_name: null })

const namePopoverRef = ref(null)
const sidebarBeingManaged = ref(false)
const template = ref(Innoclapps.resource('contacts').pages.detail)

const defaultTabIndex = route.query.section
  ? template.value.tabs.findIndex(tab => tab.id === route.query.section)
  : 0

const contactId = computed(() => route.params.id)

const {
  resource,
  resourceSingularName,
  synchronizeResource,
  detachResourceAssociations,
  incrementResourceCount,
  decrementResourceCount,
  fetchResource,
  updateResource,
  resourceReady: componentReady,
} = useResource(resourceName, contactId)

const broadcastChannel = computed(() =>
  resource.value?.authorizations?.view
    ? `Modules.Contacts.Models.Contact.${contactId.value}`
    : null
)

provide('synchronizeResource', synchronizeResource)
provide('detachResourceAssociations', detachResourceAssociations)
provide('incrementResourceCount', incrementResourceCount)
provide('decrementResourceCount', decrementResourceCount)

usePageTitle(computed(() => resource.value.display_name))
usePrivateChannel(broadcastChannel, '.ContactUpdated', () => fetchResource())

useGlobalEventListener('refresh-details-view', () => fetchResource())

useGlobalEventListener('floating-resource-updated', () => fetchResource())

const isAssociateToOneCompany = computed(
  () => resource.value.companies.length == 1
)

const enabledSections = computed(() =>
  template.value.sections.filter(section => section.enabled === true)
)

function handleActionExecuted(action) {
  if (!action.destroyable) {
    fetchResource()
  } else {
    router.push({ name: 'contact-index' })
  }
}

function updateFullName() {
  updateResource(fullNameForm).then(() => namePopoverRef.value.hide())
}

fetchResource()
</script>
