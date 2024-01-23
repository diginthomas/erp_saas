/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.5
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2024 KONKORD DIGITAL
 */
import find from 'lodash/find'
import findIndex from 'lodash/findIndex'

import { emitGlobal } from '@/Core/composables/useGlobalEventListener'
import { translate } from '@/Core/i18n'

function currentUserIndex(state) {
  return findIndex(state.collection, [
    'id',
    parseInt(Innoclapps.scriptConfig('user_id')),
  ])
}

const state = {
  collection: [],
  endpoint: '/users',
  notificationsEndpoint: '/notifications',
}

const mutations = {
  /**
   * Set the available users.
   *
   * @param {object} state
   * @param {Array} collection
   */
  SET(state, collection) {
    state.collection = collection
  },

  /**
   * Add user to the available users collection.
   *
   * @param {object} state
   * @param {object} item
   */
  ADD(state, item) {
    state.collection.push(item)
  },

  /**
   * Update user in store.
   *
   * @param {object} state
   * @param {object} data
   */
  UPDATE(state, data) {
    const index = findIndex(state.collection, ['id', parseInt(data.id)])

    if (index !== -1) {
      state.collection[index] = data.item
    }
  },

  /**
   * Remove user from store.
   *
   * @param {object} state
   * @param {number} id
   */
  REMOVE(state, id) {
    const index = findIndex(state.collection, ['id', parseInt(id)])

    if (index != -1) {
      state.collection.splice(index, 1)
    }
  },

  /**
   * Add new notification to the current user.
   */
  NEW_NOTIFICATION(state, item) {
    const index = currentUserIndex(state)

    state.collection[index].notifications.latest.unshift(item)

    state.collection[index].notifications.unread_count =
      state.collection[index].notifications.unread_count + 1

    emitGlobal('new-notification-added', item)
  },

  /**
   * Remove notifications from the current user.
   */
  REMOVE_NOTIFICATION(state, notification) {
    const index = currentUserIndex(state)

    if (
      !notification.read_at &&
      state.collection[index].notifications.unread_count > 0
    ) {
      state.collection[index].notifications.unread_count =
        state.collection[index].notifications.unread_count - 1
    }

    const notificationIndex = findIndex(
      state.collection[index].notifications.latest,
      ['id', notification.id]
    )

    // Perhaps not in the top navigation dropdown
    if (notificationIndex !== -1) {
      state.collection[index].notifications.latest.splice(notificationIndex, 1)
    }
  },

  /**
   * Set the unread count notifications for the current user.
   */
  SET_TOTAL_UNREAD_NOTIFICATIONS(state, total) {
    const index = currentUserIndex(state)

    state.collection[index].notifications.unread_count = total
  },

  /**
   * Set all notifications are read for the current user.
   */
  SET_ALL_NOTIFICATIONS_AS_READ(state) {
    const index = currentUserIndex(state)

    state.collection[index].notifications.latest.forEach(notification => {
      notification.read_at = new Date().toISOString()
    })
  },

  /**
   * Add current user dashboard.
   */
  ADD_DASHBOARD(state, dashboard) {
    const index = currentUserIndex(state)

    state.collection[index].dashboards.push(dashboard)

    if (dashboard.is_default) {
      // Update previous is_default to false
      state.collection[index].dashboards.forEach((d, index) => {
        if (d.id != dashboard.id) {
          state.collection[index].dashboards[index].is_default = false
        }
      })
    }
  },

  /**
   * Update current user dashboard.
   */
  UPDATE_DASHBOARD(state, dashboard) {
    const index = currentUserIndex(state)

    const dashboardIndex = findIndex(state.collection[index].dashboards, [
      'id',
      parseInt(dashboard.id),
    ])

    state.collection[index].dashboards[dashboardIndex] = Object.assign(
      {},
      state.collection[index].dashboards[dashboardIndex],
      dashboard
    )

    if (dashboard.is_default) {
      // Update previous is_default to false
      state.collection[index].dashboards.forEach((d, didx) => {
        if (d.id != dashboard.id) {
          state.collection[index].dashboards[didx].is_default = false
        }
      })
    }
  },

  /**
   * Add current user dashboard.
   */
  REMOVE_DASHBOARD(state, id) {
    const index = currentUserIndex(state)

    const dashboardIndex = findIndex(state.collection[index].dashboards, [
      'id',
      parseInt(id),
    ])

    state.collection[index].dashboards.splice(dashboardIndex, 1)
  },
}

const getters = {
  /**
   * Get user by given ID.
   */
  getById: state => id => {
    return find(state.collection, ['id', parseInt(id)])
  },

  /**
   * Get the current user.
   */
  current(state) {
    return state.collection[currentUserIndex(state)]
  },

  /**
   * Current user total notifications.
   */
  totalNotifications(state) {
    const index = currentUserIndex(state)

    if (!state.collection[index].notifications.latest) {
      return 0
    }

    return state.collection[index].notifications.latest.length
  },

  /**
   * Indicates whether the current user has unread notifications.
   */
  hasUnreadNotifications(state) {
    const index = currentUserIndex(state)

    return state.collection[index].notifications.unread_count > 0
  },

  /**
   * Localize the given notification.
   */
  localizeNotification: () => notification => {
    if (notification.data.lang) {
      return translate(notification.data.lang.key, notification.data.lang.attrs)
    }

    return notification.data.message
  },
}

const actions = {
  /**
   * Destroy notification.
   */
  async destroyNotification({ commit, state }, notification) {
    await Innoclapps.confirm()

    await Innoclapps.request().delete(
      `${state.notificationsEndpoint}/${notification.id}`
    )

    commit('REMOVE_NOTIFICATION', notification)

    return notification
  },

  /**
   * Mark all notifications are read for the current user.
   */
  async markAllNotificationsAsRead({ commit, state, getters }) {
    if (getters.hasUnreadNotifications) {
      await Innoclapps.request().put(state.notificationsEndpoint)

      commit('SET_ALL_NOTIFICATIONS_AS_READ')
      commit('SET_TOTAL_UNREAD_NOTIFICATIONS', 0)
    }
  },
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
}
