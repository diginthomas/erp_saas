<template>
  <Editor
    v-if="displayEditorFlag"
    ref="tinymceRef"
    v-model="internalContent"
    :disabled="disabled"
    :init="editorConfig"
  />

  <ISpinner v-else class="size-5 text-primary-500" />
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import Editor from '@tinymce/tinymce-vue'
import castArray from 'lodash/castArray'
import find from 'lodash/find'
import map from 'lodash/map'
import reject from 'lodash/reject'

import { useApp } from '@/Core/composables/useApp'
import { randomString } from '@/Core/utils'

import localeMaps from './localeMaps'

const props = defineProps({
  modelValue: {},
  placeholder: { type: String, default: '' },
  disabled: Boolean,
  defaultTag: { type: String, default: 'p' },
  withImage: { default: true, type: Boolean },
  withMention: Boolean,
  autoCompleter: [Array, Object],
  config: Object,
})

const emit = defineEmits(['update:modelValue', 'input', 'init'])

const { t } = useI18n()
const { users, currentUser, isDarkMode, locale } = useApp()

const tinymceRef = ref(null)
const imagesDraftId = randomString()
const internalContent = ref(null)
const displayEditorFlag = ref(false)

let editor = null

watch(
  () => props.autoCompleter,
  newVal => {
    if (newVal) {
      tinymceRef.value.rerender(editorConfig.value)
    }
  }
)

watch(internalContent, newVal => {
  if (newVal != props.modelValue) {
    emit('update:modelValue', newVal)
    emit('input', newVal)
  }
})

watch(
  () => props.modelValue,
  newVal => {
    if (newVal != internalContent.value) {
      // When the newVal is null and there is content in the editor, TinymCE won't trigger the update
      // because expect the value to be string in order to trigger reactivity to update the editor content
      internalContent.value = newVal || ''
    }
  },
  { immediate: true }
)

const defaultConfig = ref({
  menubar: false,
  visual: false,
  statusbar: false,
  width: '100%',
  height: '200px',
  contextmenu: false,
  branding: false,
  forced_root_block: props.defaultTag,
  images_upload_handler: handleImageUpload,
  language: Object.hasOwn(localeMaps, locale.value)
    ? localeMaps[locale.value]
    : locale.value,
  automatic_uploads: true,
  images_reuse_filename: true,
  paste_data_images: props.withImage,
  relative_urls: false,
  remove_script_host: false,
  placeholder: props.placeholder,
  format_noneditable_selector: '.not-used',
  plugins: [
    'lists',
    'autolink',
    'link',
    'autoresize',
    props.withImage ? 'image' : '',
  ].filter(plugin => plugin !== ''),
  toolbar: `
    blocks |
    bold italic underline strikethrough |
    forecolor backcolor |
    link${props.withImage ? ' image' : ''} |
    alignment | bullist numlist | removeformat
    `,
  setup: instance => {
    instance.concordCommands = {}

    if (props.withMention) {
      initializeMentions(instance)
    }

    if (props.autoCompleter) {
      initializeCustomAutoCompleter(props.autoCompleter, instance)
    }

    instance.on('init', e => {
      editor = e.target
      emit('init')
    })

    // Not visible on mobile as the group toolbar buttons are supporting only on floating type toolbar
    instance.ui.registry.addGroupToolbarButton('alignment', {
      icon: 'align-left',
      tooltip: tinymce.util.I18n.translate('Alignment'),
      items: 'alignleft aligncenter alignright | alignjustify',
    })
  },
  content_style: `
  .mention {
    color: #212529;
    background-color: #f4f5f7;
    height: 24px;
    width: 65px;
    border-radius: 6px;
    padding: 3px 3px;
    margin-right: 2px;
    -webkit-user-select: all;
    -moz-user-select: all;
    -ms-user-select: all;
    user-select: all;
  }
  `,
  block_formats: `${tinymce.util.I18n.translate(
    'Paragraph'
  )}=p; ${tinymce.util.I18n.translate(
    'Heading 1'
  )}=h1; ${tinymce.util.I18n.translate(
    'Heading 2'
  )}=h2; ${tinymce.util.I18n.translate(
    'Heading 3'
  )}=h3;  ${tinymce.util.I18n.translate('Heading 4')}=h4`,
})

const editorConfig = computed(() => ({
  ...(!props.config
    ? defaultConfig.value
    : Object.assign({}, defaultConfig.value, props.config)),
  ...getDarkModeConfig(),
}))

// Excludes the logged in user as cannot mention himself
const usersAvailableForMentioning = computed(() =>
  reject(
    map(users.value, user => ({ id: user.id, name: user.name })),
    user => user.id == currentUser.value.id
  )
)

function initializeCustomAutoCompleter(completers, editor) {
  let arrayOfCompleters = castArray(completers)

  if (arrayOfCompleters.length) {
    arrayOfCompleters.forEach(completer => {
      if (Object.keys(completer).length > 0) {
        editor.ui.registry.addAutocompleter(completer.id, {
          trigger: completer.trigger, // the trigger character to open the autocompleter
          minChars: completer.minChars || 0, // 0 to open the dropdown immediately after the char is typed
          columns: 1, // must be 1 for text-based results
          // eslint-disable-next-line no-unused-vars
          fetch: function (pattern) {
            return new Promise(resolve => resolve(completer.list))
          },
          // Executed when value is selected from the dropdown
          onAction: function (autocompleteApi, rng, value) {
            editor.selection.setRng(rng || 0)
            editor.insertContent(`${value} `)
            // Hide the autocompleter
            autocompleteApi.hide()
          },
        })
      }
    })
  }
}

function getDarkModeConfig() {
  return isDarkMode.value ? { skin: 'oxide-dark', content_css: 'dark' } : {}
}

function initializeMentions(editor) {
  editor.concordCommands.insertMentionUser = function (id, name, rng) {
    // Insert in to the editor
    editor.selection.setRng(rng || 0)

    editor.insertContent(`<span class="mention"
                      data-mention-id="${id}"
                      contenteditable="false"
                      data-notified="false"><span data-mention-char>@</span><span data-mention-value>${name}</span>
                      </span> `)
  }

  editor.ui.registry.addAutocompleter('mentions', {
    trigger: '@', // the trigger character to open the autocompleter
    minChars: 0, // 0 to open the dropdown immediately after the @ is typed
    columns: 1, // must be 1 for text-based results
    // Retrieve the available users
    // eslint-disable-next-line no-unused-vars
    fetch: function (pattern) {
      return new Promise(resolve =>
        resolve(
          map(usersAvailableForMentioning.value, user => ({
            value: user.id.toString(),
            text: user.name,
          }))
        )
      )
    },

    // Executed when user is selected from the dropdown
    onAction: function (autocompleteApi, rng, value) {
      // Find the selected user via the user id
      let user = find(usersAvailableForMentioning.value, [
        'id',
        parseInt(value),
      ])

      editor.concordCommands.insertMentionUser(value, user.name, rng)
      // Hide the autocompleter
      autocompleteApi.hide()
    },
  })
}

// eslint-disable-next-line no-unused-vars
function handleImageUpload(blobInfo, progress) {
  const file = blobInfo.blob()

  return new Promise((resolve, reject) => {
    if (!/^image\//.test(file.type)) {
      reject({
        message: t('validation.image', {
          attribute: file.name,
        }),
        remove: true,
      })

      return
    }

    const fd = new FormData()
    fd.append('file', file)

    Innoclapps.request()
      .post(`/media/pending/${imagesDraftId}`, fd)
      .then(({ data }) => resolve(data.preview_url))
      .catch(error => {
        // Nginx 413 Request Entity Too Large
        let message =
          error.message && error.message.includes('413')
            ? t('core::app.file_too_large')
            : error.response.data.message

        reject({ message: message, remove: true })
      })
  })
}

function setContent(content) {
  internalContent.value = content
}

function clearContent() {
  setContent('')
}

function focus() {
  editor.focus()
}

onMounted(() => {
  setTimeout(() => {
    // https://github.com/tinymce/tinymce-vue/issues/230
    displayEditorFlag.value = true
  }, 200)
})

defineExpose({ focus, setContent, clearContent })
</script>
