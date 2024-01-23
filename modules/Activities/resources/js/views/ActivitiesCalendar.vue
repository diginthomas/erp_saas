<template>
  <MainLayout no-padding :overlay="isLoading">
    <template #actions>
      <NavbarSeparator class="hidden lg:block" />

      <NavbarItems>
        <ActivitiesNavbarViewSelector active="calendar" />

        <IButton
          icon="Plus"
          variant="primary"
          :text="$t('activities::activity.create')"
          @click="eventBeingCreated = true"
        />
      </NavbarItems>
    </template>

    <div
      class="space-y-3 px-3 py-4 sm:flex sm:flex-wrap sm:items-center sm:space-y-0 sm:px-5"
    >
      <div class="grow">
        <div
          class="space-y-2 sm:flex sm:flex-wrap sm:items-center sm:space-x-3 sm:space-y-0"
        >
          <div class="flex items-center">
            <p
              v-t="'activities::activity.type.type'"
              class="mr-2 text-sm text-neutral-600 dark:text-neutral-300"
            />

            <IDropdownSelect
              v-model="type"
              :items="typesForDropdown"
              :label="type ? type.name : $t('core::app.all')"
              label-key="name"
              value-key="id"
              @change="query.activity_type_id = $event.id"
            />
          </div>

          <div class="flex items-center">
            <p
              v-t="'activities::activity.owner'"
              class="mr-2 text-sm text-neutral-600 dark:text-neutral-300"
            />

            <IDropdownSelect
              v-if="$gate.userCan('view all activities')"
              v-model="user"
              :items="usersForDropdown"
              label-key="name"
              value-key="id"
              @change="query.user_id = $event.id"
            />
          </div>
        </div>
      </div>

      <div class="flex w-full justify-between sm:w-auto sm:justify-end">
        <IButtonGroup class="mr-3 inline">
          <IButton
            v-i-tooltip.left="
              $t('activities::calendar.fullcalendar.locale.buttonText.prev')
            "
            icon="ChevronLeft"
            variant="white"
            @click="$refs.calendarRef.getApi().prev()"
          />

          <IButton
            variant="white"
            :text="
              $t('activities::calendar.fullcalendar.locale.buttonText.today')
            "
            @click="$refs.calendarRef.getApi().today()"
          />

          <IButton
            v-i-tooltip.right="
              $t('activities::calendar.fullcalendar.locale.buttonText.next')
            "
            variant="white"
            icon="ChevronRight"
            @click="$refs.calendarRef.getApi().next()"
          />
        </IButtonGroup>

        <IDropdown :text="activeViewText" adaptive-width icon="Calendar">
          <IDropdownItem
            v-show="activeView !== 'timeGridWeek'"
            :text="
              $t('activities::calendar.fullcalendar.locale.buttonText.week')
            "
            @click="changeView('timeGridWeek')"
          />

          <IDropdownItem
            v-show="activeView !== 'dayGridMonth'"
            :text="
              $t('activities::calendar.fullcalendar.locale.buttonText.month')
            "
            @click="changeView('dayGridMonth')"
          />

          <IDropdownItem
            v-show="activeView !== 'timeGridDay'"
            :text="
              $t('activities::calendar.fullcalendar.locale.buttonText.day')
            "
            @click="changeView('timeGridDay')"
          />
        </IDropdown>
      </div>
    </div>

    <div class="fc-wrapper">
      <FullCalendar
        v-if="calendarOptions.initialView"
        ref="calendarRef"
        class="h-screen"
        :options="calendarOptions"
      />

      <CreateActivityModal
        :visible="eventBeingCreated"
        :due-date="createDueDate"
        :end-date="createEndDate"
        @created="onActivityCreatedEventHandler"
        @hidden="handleActivityCreateModalHidden"
      />
    </div>
  </MainLayout>
</template>

<script setup>
import { computed, onMounted, ref, shallowReactive, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import luxonPlugin from '@fullcalendar/luxon3'
import timeGridPlugin from '@fullcalendar/timegrid'
import FullCalendar from '@fullcalendar/vue3'
import { useStorage } from '@vueuse/core'

import { useApp } from '@/Core/composables/useApp'
import { usePrivateChannel } from '@/Core/composables/useBroadcast'
import { useDates } from '@/Core/composables/useDates'
import { useFloatingResourceModal } from '@/Core/composables/useFloatingResourceModal'
import { useGlobalEventListener } from '@/Core/composables/useGlobalEventListener'
import { useLoader } from '@/Core/composables/useLoader'
import { useResourceable } from '@/Core/composables/useResourceable'

import ActivitiesNavbarViewSelector from '../components/ActivitiesNavbarViewSelector.vue'
import { useActivityTypes } from '../composables/useActivityTypes'

const resourceName = Innoclapps.resourceName('activities')

const calendarDefaultView = useStorage('default-calendar-view', 'timeGridWeek')
const { t } = useI18n()
const { setLoading, isLoading } = useLoader()
const { floatResourceInEditMode } = useFloatingResourceModal()
const { currentUser, users, locale: currentLocale } = useApp()
const { DateTime, localizedTime } = useDates()
const { typesByName } = useActivityTypes()
const { updateResource } = useResourceable(resourceName)

const calendarRef = ref(null)

const createDueDate = ref(null)
const createEndDate = ref(null)
const activeView = ref(null)
const user = currentUser.value
const type = ref(null)
const eventBeingCreated = ref(false)

const query = ref({
  activity_type_id: null,
  user_id: currentUser.value.id,
})

const calendarOptions = shallowReactive({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, luxonPlugin],
  locale: currentLocale.value.toLowerCase().replace('_', '-'),
  locales: [
    {
      code: currentLocale.value.toLowerCase().replace('_', '-'),
      ...lang[currentLocale.value].activities.calendar.fullcalendar.locale,
    },
  ],
  headerToolbar: {
    left: 'title',
    center: false,
    end: false,
  },
  dayMaxEventRows: true, // for all non-TimeGrid views
  views: {
    day: {
      dayMaxEventRows: false,
    },
  },
  eventDisplay: 'block',
  initialView: null,
  timeZone: currentUser.value.timezone,
  lazyFetching: false,
  editable: true,
  droppable: true,

  scrollTime: '00:00:00', // not scroll to current time e.q. on day view

  // Remove the top left all day text as it's not suitable
  allDayContent: arg => {
    arg.text = ''

    return arg
  },

  moreLinkClick: arg => {
    calendarRef.value.getApi().gotoDate(arg.date)

    calendarRef.value.getApi().changeView('dayGridDay')
  },

  viewDidMount: arg => {
    activeView.value = arg.view.type

    // We don't remember the dayGridDay as
    // this is the more link redirect view
    if (arg.view.type !== 'dayGridDay') {
      rememberDefaultView(arg.view.type)
    }
  },

  eventContent: createEventTitleDomNodes,

  eventClick: info => {
    floatResourceInEditMode({
      resourceName: resourceName,
      resourceId: parseInt(info.event.id),
    })
  },

  dateClick: data => {
    createDueDate.value = data.allDay
      ? data.dateStr
      : DateTime.fromISO(data.dateStr).toUTC().toISO()

    // On end date, we will format with the user timezone as the end date
    // has not time when on dateClick click and for this reason, we must get the actual date
    // to be displayed in the create modal e.q. if user click on day view 19th April 12 AM
    // the dueDate will be shown properly but not the end date as if we format the end date
    // with UTC will 18th April e.q. 18th April 22:00 (UTC)
    createEndDate.value = data.allDay
      ? data.dateStr
      : DateTime.fromISO(data.dateStr).toUTC().toISODate()

    eventBeingCreated.value = true
  },

  eventResize: resizeInfo => {
    let payload = {}

    if (resizeInfo.event.allDay) {
      payload = {
        due_date: resizeInfo.event.startStr,
        end_date: endDateForStorage(resizeInfo.event.endStr),
      }
    } else {
      const dueDateTimeInstance = DateTime.fromISO(
        resizeInfo.event.startStr
      ).toUTC()

      const endDateTimeInstance = DateTime.fromISO(
        resizeInfo.event.endStr
      ).toUTC()

      payload = {
        due_date: dueDateTimeInstance.toISO(),
        end_date: endDateTimeInstance.toISO(),
      }
    }

    updateResource(payload, resizeInfo.event.id)
  },

  eventDrop: dropInfo => {
    const payload = {}
    const event = calendarRef.value.getApi().getEventById(dropInfo.event.id)

    if (dropInfo.event.allDay) {
      payload.due_date = dropInfo.event.startStr

      // When dropping event from time column to all day e.q. on week view
      // there is no end date as it's the same day, for this reason, we need to update the
      // end date to be the same like the start date for the update request payload
      if (!dropInfo.event.end) {
        payload.end_date = payload.due_date
      } else {
        // Multi days event, we will remove the one day to store
        // the end date properly in database as here for the calendar they are endDate + 1 day so they are
        // displayed properly see prepareEventsForCalendar method
        payload.end_date = endDateForStorage(dropInfo.event.endStr)
      }

      event.setExtendedProp('isAllDay', true)
      event.setEnd(endDateForCalendar(payload.end_date))
    } else {
      const dueDateUTCInstance = DateTime.fromISO(
        dropInfo.event.startStr
      ).toUTC()

      payload.due_date = dueDateUTCInstance.toISO()

      // When dropping all day event to non all day e.q. on week view from top to the timeline
      // we need to update the end date as well
      if (dropInfo.oldEvent.allDay && !dropInfo.event.allDay) {
        let endDateLocalInstance = DateTime.fromISO(
          dropInfo.event.startStr
        ).plus({ hours: 1 })

        payload.end_date = endDateLocalInstance.toUTC().toISO()
        event.setEnd(endDateLocalInstance.toISO())
        event.setExtendedProp('hasEndTime', true)
      } else {
        // We will check if the actual endStr is set, if not will use the due dates as due time
        // because this may happen when the activity due and end
        // date are the same, in this case, fullcalendar does not provide the endStr
        payload.end_date = dropInfo.event.endStr
          ? DateTime.fromISO(dropInfo.event.endStr).toUTC().toISODate()
          : payload.due_date

        // Time can be modified on week and day view, on month view we will
        // only modify the time on actual activities with time
        if (
          activeView.value !== 'dayGridMonth' ||
          dropInfo.event.extendedProps.hasEndTime
        ) {
          payload.end_date = dropInfo.event.endStr
            ? DateTime.fromISO(dropInfo.event.endStr).toUTC().toISO()
            : payload.due_date
          event.setExtendedProp('hasEndTime', true)
        }
      }

      event.setExtendedProp('isAllDay', false)
    }

    updateResource(payload, dropInfo.event.id)
  },

  loading: setLoading,

  events: (info, successCallback, failureCallback) => {
    Innoclapps.request('/calendar', {
      params: {
        resourceName,
        ...query.value,
        start_date: DateTime.fromJSDate(info.start)
          .toUTC()
          .toFormat('yyyy-MM-dd HH:mm:ss'),
        end_date: DateTime.fromJSDate(info.end)
          .toUTC()
          .toFormat('yyyy-MM-dd HH:mm:ss'),
      },
    })
      .then(({ data }) => successCallback(prepareEventsForCalendar(data)))
      .catch(error => {
        console.error(error)
        failureCallback('Error while retrieving events', error)
      })
  },
})

const usersForDropdown = computed(() => [
  ...users.value,
  {
    id: null,
    name: t('core::app.all'),
    class: 'border-t border-neutral-200 dark:border-neutral-700',
  },
])

const typesForDropdown = computed(() => [
  ...typesByName.value,
  {
    id: null,
    name: t('core::app.all'),
    class: 'border-t border-neutral-200 dark:border-neutral-700',
  },
])

const activeViewText = computed(() => {
  switch (activeView.value) {
    case 'timeGridWeek':
      return t('activities::calendar.fullcalendar.locale.buttonText.week')
    case 'dayGridMonth':
      return t('activities::calendar.fullcalendar.locale.buttonText.month')
    case 'dayGridDay':
    case 'timeGridDay':
      return t('activities::calendar.fullcalendar.locale.buttonText.day')
  }

  return ''
})

function onActivityCreatedEventHandler() {
  refreshEvents()
  eventBeingCreated.value = false
}

function changeView(viewName) {
  calendarRef.value.getApi().changeView(viewName)
  activeView.value = viewName
  rememberDefaultView(viewName)
}

/**
 * Create end date for the calendar
 *
 * @see  prepareEventsForCalendar
 *
 * @param  {string} date
 *
 * @returns {string}
 */
function endDateForCalendar(date) {
  return DateTime.fromISO(date).plus({ days: 1 }).toISODate()
}

/**
 * Create end date for storage
 *
 * @see  prepareEventsForCalendar
 *
 * @param  {string} date
 *
 * @returns {string}
 */
function endDateForStorage(date) {
  return DateTime.fromISO(date).minus({ days: 1 }).toISODate()
}

/**
 * @see https://fullcalendar.io/docs/event-render-hooks
 */
function createEventTitleDomNodes(arg) {
  let event = document.createElement('span')

  if (arg.event.allDay) {
    event.innerHTML = arg.event.title
  } else {
    let localDateTimeStartInstance = DateTime.fromISO(arg.event.startStr)

    let startTime = localizedTime(localDateTimeStartInstance.toUTC().toISO())
    let localDateTimeEndInstance

    if (arg.isMirror && arg.isDragging && arg.event.extendedProps.isAllDay) {
      // Dropping from all day to non-all day
      // In this case, there is no end date, we will automatically add 1 hour to the start date
      localDateTimeEndInstance = DateTime.fromISO(arg.event.startStr).plus({
        hours: 1,
      })
    } else if (
      ((arg.isMirror && arg.isResizing) ||
        (arg.isMirror && arg.isDragging) ||
        (arg.event.endStr && arg.event.extendedProps.hasEndTime === true)) &&
      // This may happen when the activity due and end
      // date are the same, in this case, fullcalendar does not provide the endStr
      // attribute and the time will be shown only from the startStr
      arg.event.endStr != arg.event.startStr
    ) {
      localDateTimeEndInstance = DateTime.fromISO(arg.event.endStr)
    }

    if (localDateTimeEndInstance) {
      let endTime = localizedTime(localDateTimeEndInstance.toUTC().toISO())

      if (localDateTimeEndInstance.day != localDateTimeStartInstance.day) {
        startTime +=
          ' - ' + endTime + ' ' + localDateTimeEndInstance.toFormat('LLL d')
      } else {
        startTime += ' - ' + endTime
      }
    }

    event.innerHTML = startTime + ' ' + arg.event.title
  }

  return {
    domNodes: [event],
  }
}

function prepareEventsForCalendar(events) {
  return events.map(event => {
    // @see https://stackoverflow.com/questions/30323397/fullcalendar-event-shows-wrong-end-date-by-one-day
    // @see https://fullcalendar.io/docs/event-parsing
    // e.q. event with start 2021-04-01 and end date 2021-04-03 in the calendar is displayed
    // from 2021-04-01 to 2021-04-02, in this case on fetch, we will add 1 days so they are
    // displayed properly and on update, we will remove 1 day so they are saved properly
    event.extendedProps.isAllDay = event.allDay

    if (event.allDay) {
      event.end = endDateForCalendar(event.end)
    } else if (!/\d{4}-\d{2}-\d{2}\T?\d{2}:\d{2}:\d{2}$/.test(event.end)) {
      // no end time, is not in y-m-dTh:i:s format
      // to prevent clogging the calendar with events showing
      // over the week/day view, we will just add the start hour:minute
      // as end hour:minute + 30 minutes to be shown in one simple box
      // this can usually happen when to due and the end date are the same and there is no end time
      const dateTimeStart = DateTime.fromISO(event.start)
      const dateTimeEnd = DateTime.fromISO(event.end)

      event.end = dateTimeEnd
        .set({
          hour: dateTimeStart.hour,
          minute: dateTimeStart.minute,
          second: 0,
        })
        .plus({ minutes: 30 })
        .toFormat("yyyy-MM-dd'T'HH:mm:ss")
      event.extendedProps.hasEndTime = false
    } else {
      event.extendedProps.hasEndTime = true
    }

    // We need to set endEditable on events displayed on the month view as for some reason
    // when the calendar option {editable: true} is set the month view events are not resizable
    // note this is only applicable for all day events as non-all days events cannot be dragged
    // on month view (fullcalendar limitation)
    if (activeView.value === 'dayGridMonth') {
      event.endEditable = true
    }

    if (event.isReadOnly) {
      event.editable = false
    }

    return event
  })
}

function handleActivityCreateModalHidden() {
  eventBeingCreated.value = false
  createDueDate.value = null
  createEndDate.value = null
}

function refreshEvents() {
  calendarRef.value.getApi().refetchEvents()
}

function rememberDefaultView(viewName) {
  calendarDefaultView.value = viewName
}

function setCalendarAsDefaultView() {
  calendarOptions.initialView = calendarDefaultView.value
}

watch(query, refreshEvents, { deep: true })

usePrivateChannel(
  `Modules.Users.Models.User.${currentUser.value.id}`,
  '.Modules\\Activities\\Events\\CalendarSyncFinished',
  refreshEvents
)

useGlobalEventListener('floating-resource-hidden', () => {
  refreshEvents()
})

onMounted(() => {
  setCalendarAsDefaultView()
})
</script>
