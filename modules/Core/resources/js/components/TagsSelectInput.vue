<template>
  <ICustomSelect
    :model-value="modelValue"
    :multiple="true"
    :options="options"
    :reduce="tag => tag.name"
    :simple="simple"
    class="[&_.toggle-icon]:size-5"
    :filterable="!showForm"
    :placeholder="simple ? $t('core::tags.search') : undefined"
    :toggle-icon="toggleIcon"
    :list-wrapper-class="simple ? 'min-w-[340px]' : undefined"
    label="name"
    :list-class="showForm ? 'hidden' : undefined"
    :reorderable="$gate.isSuperAdmin()"
    @update:model-value="emitUpdateModelValue"
    @update:draggable="handleTagsReordered"
  >
    <template #option-actions="{ index }">
      <ILink
        v-if="$gate.isSuperAdmin()"
        variant="neutral"
        @click="prepareEdit(options[index])"
      >
        <Icon icon="Pencil" class="size-4" />
      </ILink>
    </template>

    <template v-if="showForm" #header>
      <div
        class="border-b border-neutral-200 bg-neutral-50 px-5 py-3 text-sm font-semibold text-neutral-800 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-100"
      >
        {{
          tagBeingCreated ? $t('core::tags.new_tag') : $t('core::tags.edit_tag')
        }}
      </div>

      <div class="px-5 py-4">
        <IFormGroup
          label-for="tag_name"
          :label="$t('core::tags.tag_name')"
          required
        >
          <IFormInput
            id="tag_name"
            v-model="tagForm.name"
            name="tag_name"
            @keydown.enter="save"
          />

          <IFormError :error="tagForm.getError('name')" />
        </IFormGroup>

        <IFormLabel :label="$t('core::tags.color')" class="mb-1" required />

        <IColorSwatch v-model="tagForm.swatch_color" :swatches="swatches" />

        <IFormError :error="tagForm.getError('swatch_color')" />
      </div>

      <div
        class="border-t border-neutral-200 bg-neutral-50 px-5 py-2 dark:border-neutral-600 dark:bg-neutral-800"
      >
        <div class="flex items-center">
          <IButton
            v-if="!tagBeingCreated && $gate.isSuperAdmin()"
            variant="danger"
            :icon="deletitionConfirmed === null ? 'Trash' : null"
            :text="
              deletitionConfirmed === null ? undefined : $t('core::app.confirm')
            "
            @click="deleteTag(tagBeingEdited)"
          />

          <div class="ml-auto flex items-center space-x-2">
            <IButton
              variant="white"
              :text="$t('core::app.cancel')"
              @click="hideForm"
            />

            <IButton
              variant="primary"
              :text="$t('core::app.save')"
              :loading="tagForm.busy"
              :disabled="tagForm.busy"
              @click="save"
            />
          </div>
        </div>
      </div>
    </template>

    <template v-if="$gate.isSuperAdmin()" #footer>
      <ILink
        v-show="!showForm"
        class="block border-t border-neutral-200 px-4 py-2 text-sm hover:bg-neutral-50 dark:border-neutral-600 dark:hover:bg-neutral-700"
        @click="tagBeingCreated = true"
      >
        &plus; {{ $t('core::tags.add_new') }}
      </ILink>
    </template>
  </ICustomSelect>
</template>

<script setup>
import { computed, ref } from 'vue'
import cloneDeep from 'lodash/cloneDeep'

import { useApp } from '../composables/useApp'
import { useForm } from '../composables/useForm'
import { useTags } from '../composables/useTags'

const props = defineProps({
  modelValue: Array,
  type: String,
  simple: Boolean,
})

const emit = defineEmits(['update:modelValue'])

const { scriptConfig } = useApp()

const {
  tagsByDisplayOrder,
  findTagsByType,
  removeTag,
  setTag,
  findTagById,
  setTags,
  addTag,
} = useTags()

const options = computed(() => {
  if (props.type) {
    return findTagsByType(props.type)
  }

  return tagsByDisplayOrder.value
})

const swatches = scriptConfig('favourite_colors').slice(0, -2)

const { form: tagForm } = useForm(
  {
    name: '',
    swatch_color: swatches[1],
  },
  {
    resetOnSuccess: true,
  }
)

const deletitionConfirmed = ref(null)
const tagBeingCreated = ref(false)
const tagBeingEdited = ref(null)

const toggleIcon = computed(() => {
  if (!props.simple) {
    return 'Selector'
  }

  if (!props.modelValue || props.modelValue.length === 0) {
    return 'Tag'
  }

  return ''
})

const showForm = computed(
  () => tagBeingCreated.value || Boolean(tagBeingEdited.value)
)

function save() {
  tagBeingCreated.value ? createTag() : updateTag()
}

function prepareEdit(tag) {
  tagBeingEdited.value = tag.id
  tagForm.fill('name', tag.name)
  tagForm.fill('swatch_color', tag.swatch_color)
}

function handleTagsReordered(tags) {
  setTags(
    tags.map((tag, idx) => ({
      ...tag,
      display_order: idx + 1,
    }))
  )

  Innoclapps.request().post(
    '/tags/order',
    tags.map((tag, index) => ({
      id: tag.id,
      display_order: index + 1,
    }))
  )
}

function hideForm() {
  tagBeingCreated.value = false
  tagBeingEdited.value = null
  deletitionConfirmed.value = null
  tagForm.reset()
}

function createTag() {
  tagForm.post(`/tags${props.type ? `/${props.type}` : ''}`).then(tag => {
    if (findTagById(tag.id)) {
      setTag(tag.id, tag)
    } else {
      addTag(tag)
    }
    hideForm()
  })
}

function updateTag() {
  const oldTagName = findTagById(tagBeingEdited.value).name

  tagForm.put(`/tags/${tagBeingEdited.value}`).then(tag => {
    setTag(tag.id, tag)

    let tagInValueIndex = props.modelValue.findIndex(
      t => (t?.name || t) === oldTagName
    )

    if (tagInValueIndex !== -1) {
      let oldTagValue = cloneDeep(props.modelValue[tagInValueIndex])

      if (typeof oldTagValue === 'string') {
        oldTagValue = tag.name
      } else {
        oldTagValue.name = tag.name
      }
      let newModelValue = cloneDeep(props.modelValue)
      newModelValue[tagInValueIndex] = oldTagValue
      emitUpdateModelValue(newModelValue)
    }
    hideForm()
  })
}

function emitUpdateModelValue(data) {
  emit(
    'update:modelValue',
    data.map(tag => {
      if (typeof tag === 'string') {
        return tag
      }

      return tag.name
    })
  )
}

async function deleteTag(id) {
  if (deletitionConfirmed.value === null) {
    deletitionConfirmed.value = false

    return
  }

  let tag = findTagById(id)
  await Innoclapps.request().delete(`/tags/${id}`)

  deletitionConfirmed.value = null

  let tagInValueIndex = props.modelValue.findIndex(
    t => (t?.name || t) === tag.name
  )

  if (tagInValueIndex !== -1) {
    emitUpdateModelValue(
      props.modelValue.filter(t => (t?.name || t) !== tag.name)
    )
  }
  removeTag(id)
  hideForm()
}
</script>
