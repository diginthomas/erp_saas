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
import NotesTab from './components/RecordTabNote.vue'
import NotesTabPanel from './components/RecordTabNotePanel.vue'
import RecordTabTimelineNote from './components/RecordTabTimelineNote.vue'

if (window.Innoclapps) {
  Innoclapps.booting(app => {
    app.component('NotesTab', NotesTab)
    app.component('NotesTabPanel', NotesTabPanel)
    app.component('RecordTabTimelineNote', RecordTabTimelineNote)
  })
}
