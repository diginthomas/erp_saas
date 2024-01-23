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
import CommentsAdd from './components/CommentsAdd.vue'
import CommentsCollapse from './components/CommentsCollapse.vue'
import CommentsList from './components/CommentsList.vue'
import CommentsStore from './store/Comments'

if (window.Innoclapps) {
  Innoclapps.booting(function (app, router, store) {
    app.component('CommentsAdd', CommentsAdd)
    app.component('CommentsCollapse', CommentsCollapse)
    app.component('CommentsList', CommentsList)

    store.registerModule('comments', CommentsStore)
  })
}
