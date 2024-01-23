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
import filter from 'lodash/filter'
import find from 'lodash/find'
import findIndex from 'lodash/findIndex'
import orderBy from 'lodash/orderBy'

const state = {
  collection: [],
  collectionSet: false,
  endpoint: '/mail/accounts',
  syncInProgress: false,
  activeInboxAccount: {},
}

const mutations = {
  /**
   * Set email accounts in store.
   */
  SET(state, collection) {
    state.collection = collection

    state.collectionSet = true
  },

  /**
   * Reset the email accounts in store.
   */
  RESET(state) {
    state.collection = []

    state.collectionSet = false
  },

  /**
   * Add email account in store.
   */
  ADD(state, item) {
    state.collection.push(item)
  },

  /**
   * Update email account in store.
   */
  UPDATE(state, data) {
    const index = findIndex(state.collection, ['id', parseInt(data.id)])

    if (index !== -1) {
      state.collection[index] = data.item
    }
  },

  /**
   * Remove email account from store.
   */
  REMOVE(state, id) {
    const index = findIndex(state.collection, ['id', parseInt(id)])

    if (index != -1) {
      state.collection.splice(index, 1)
    }
  },

  /**
   * Set that indicator that synchronization is in progress.
   */
  SET_SYNC_IN_PROGRESS(state, bool) {
    state.syncInProgress = bool
  },

  /**
   * Set the active inbox account.
   */
  SET_INBOX_ACCOUNT(state, account) {
    if (typeof account != 'number') {
      account = account.id
    }

    state.activeInboxAccount = account
  },

  /**
   * Set the given account id as primary
   * The function unsets any previous primary accounts from the store
   * and updates the given account id to be as primary.
   *
   * @param {number} id|null When passing null, all accounts are marked as not primary.
   */
  SET_ACCOUNT_AS_PRIMARY(state, id) {
    // Update previous is_primary to false and set passed id as primary
    // this helps the getter "accounts" to properly perform the sorting
    state.collection.forEach((account, index) => {
      state.collection[index].is_primary = account.id == id
    })
  },
}

const getters = {
  /**
   * Get the shared accounts the user is able to view.
   */
  shared(state) {
    return filter(state.collection, ['type', 'shared'])
  },

  /**
   * Get the user personal accounts.
   */
  personal(state) {
    return filter(state.collection, ['type', 'personal'])
  },

  /**
   * Get account OAuth Connect URL.
   */
  OAuthConnectUrl: () => (connection_type, type) => {
    if (connection_type == 'Gmail') {
      return (
        Innoclapps.scriptConfig('url') +
        '/mail/accounts/' +
        type +
        '/google/connect'
      )
    } else if (connection_type == 'Outlook') {
      return (
        Innoclapps.scriptConfig('url') +
        '/mail/accounts/' +
        type +
        '/microsoft/connect'
      )
    }
  },

  /**
   * Get the active inbox acccount.
   */
  activeInboxAccount(state) {
    return (
      find(state.collection, ['id', parseInt(state.activeInboxAccount)]) || {}
    )
  },

  /**
   * Get all accounts sorted by first primary acccounts then by email.
   */
  accounts(state) {
    return orderBy(state.collection, ['is_primary', 'email'], ['desc', 'asc'])
  },

  /**
   * Check whether there are accounts configured for the current user.
   */
  hasConfigured(state) {
    return state.collection.length > 0
  },

  /**
   * Check whether the current user has primary account configured.
   */
  hasPrimary(state) {
    return (
      filter(state.collection, { is_primary: true, is_sync_stopped: false })
        .length > 0
    )
  },

  /**
   * Get the latest created account.
   */
  latest(state) {
    return orderBy(state.collection, account => new Date(account.created_at), [
      'desc',
    ])[0]
  },
}

const actions = {
  /**
   * Fetch accounts from storage.
   */
  async fetch({ state, commit }, options = {}) {
    if (state.collectionSet) {
      return state.collection
    }

    let { data: accounts } = await Innoclapps.request(state.endpoint, options)

    commit('SET', accounts)

    return accounts
  },

  /**
   * Remove primary account.
   */
  removePrimary({ state, commit }) {
    Innoclapps.request()
      .delete(`${state.endpoint}/primary`)
      .then(() => {
        commit('SET_ACCOUNT_AS_PRIMARY', null)
      })
  },

  /**
   * Set the account is primary state.
   */
  setPrimary({ state, commit }, id) {
    Innoclapps.request()
      .put(`${state.endpoint}/${id}/primary`)
      .then(() => {
        commit('SET_ACCOUNT_AS_PRIMARY', id)
      })
  },

  /**
   * Enable account synchronization.
   */
  enableSync({ state, commit }, id) {
    Innoclapps.request()
      .post(`${state.endpoint}/${id}/sync/enable`)
      .then(({ data: account }) => {
        commit('UPDATE', {
          id: account.id,
          item: account,
        })
      })
  },

  /**
   * Disable account synchronization.
   */
  disableSync({ state, commit }, id) {
    Innoclapps.request()
      .post(`${state.endpoint}/${id}/sync/disable`)
      .then(({ data: account }) => {
        commit('UPDATE', {
          id: account.id,
          item: account,
        })
      })
  },

  /**
   * Syncs shared email account.
   */
  async syncAccount({ commit }, accountId) {
    commit('SET_SYNC_IN_PROGRESS', true)

    let { data } = await Innoclapps.request(
      `/mail/accounts/${accountId}/sync`
    ).finally(() => commit('SET_SYNC_IN_PROGRESS', false))

    return data
  },

  /**
   * Delete a record.
   */
  async destroy(context, id) {
    let { data } = await Innoclapps.request().delete(`${state.endpoint}/${id}`)

    context.commit('REMOVE', id)
    context.dispatch('updateUnreadCountUI', data.unread_count)

    return data
  },

  /**
   * Update the total unread count UI.
   */
  updateUnreadCountUI(context, unreadCount) {
    context.commit(
      'UPDATE_SIDEBAR_MENU_ITEM',
      {
        id: 'inbox',
        data: {
          badge: unreadCount,
        },
      },
      { root: true }
    )
  },

  /**
   * Decrement total unread count updateUnreadCountUI.
   */
  decrementUnreadCountUI(context) {
    let item = context.rootGetters.getSidebarMenuItem('inbox')

    if (item.badge < 1) {
      return
    }

    context.dispatch('updateUnreadCountUI', item.badge - 1)
  },
}

export default {
  namespaced: true,
  state,
  mutations,
  getters,
  actions,
}
