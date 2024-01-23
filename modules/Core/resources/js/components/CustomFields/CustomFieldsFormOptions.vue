<template>
  <IFormGroup>
    <div class="flex">
      <IFormLabel
        class="grow text-neutral-900 dark:text-neutral-100"
        :label="$t('core::fields.options')"
        required
      />

      <IButtonIcon
        v-show="!addingOptionsViaText"
        v-i-tooltip="$t('core::app.add_another')"
        icon="Plus"
        @click="newOptionAndFocus"
      />
    </div>

    <div
      v-if="options && options.length === 0 && !addingOptionsViaText"
      class="mt-2"
    >
      <IAlert variant="info">
        <I18nT
          scope="global"
          :keypath="'core::fields.custom.create_option_icon'"
          tag="div"
          class="flex"
        >
          <template #icon>
            <Icon
              icon="Plus"
              class="mx-1 size-5 cursor-pointer"
              @click="newOptionAndFocus"
            />
          </template>
        </I18nT>

        <ILink
          class="mt-2 inline-flex items-center"
          variant="info"
          @click="addingOptionsViaText = true"
        >
          {{ $t('core::fields.custom.or_add_options_via_text') }}
          <Icon icon="ArrowLeft" class="ml-1 mt-0.5 size-3" />
        </ILink>
      </IAlert>
    </div>

    <div v-if="addingOptionsViaText">
      <IFormTextarea
        v-model="textOptions"
        :placeholder="$t('core::fields.custom.text_options_each_on_new_line')"
      />

      <div
        class="mt-1 flex items-center justify-end divide-x divide-neutral-200 text-right text-sm"
      >
        <ILink
          :text="$t('core::fields.custom.convert_text_to_options')"
          @click="convertTextToOptions"
        />

        <ILink
          :text="$t('core::app.cancel')"
          class="ml-2 pl-2"
          @click="cancelAddOptionsViaText"
        />
      </div>
    </div>

    <SortableDraggable
      v-bind="$draggable.common"
      :model-value="options"
      :item-key="(item, index) => index"
      handle=".option-draggable-handle"
      @update:model-value="emitUpdateEvent($event)"
      @sort="setDisplayOrder"
    >
      <template #item="{ element, index }">
        <div class="relative mt-3">
          <div class="group -mx-6 px-6">
            <div
              class="option-draggable-handle absolute -left-5 top-2 z-20 hidden focus-within:block group-hover:block hover:block"
            >
              <IButtonIcon
                icon="Selector"
                class="cursor-move text-neutral-400"
                icon-class="size-4"
              />
            </div>

            <div class="relative">
              <div
                v-if="element.id"
                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-neutral-500 dark:text-neutral-200"
                v-text="$t('core::app.id') + ': ' + element.id"
              />

              <IFormInput
                ref="optionNameRef"
                :model-value="element.name"
                :class="['pr-20', { 'pl-14': element.id }]"
                @update:model-value="updateOption(index, 'name', $event)"
                @keydown.enter.prevent.stop="newOptionAndFocus"
              />

              <IPopover auto-placement class="w-72">
                <IButtonIcon
                  v-i-tooltip="$t('core::app.colors.color')"
                  icon="ColorSwatch"
                  :style="{ color: element.swatch_color }"
                  class="absolute right-11 top-1.5 -mt-px"
                />

                <template #popper>
                  <div class="p-4">
                    <IColorSwatch
                      :model-value="element.swatch_color"
                      :swatches="$scriptConfig('favourite_colors')"
                      @update:model-value="
                        updateOption(index, 'swatch_color', $event)
                      "
                    />
                  </div>
                </template>
              </IPopover>

              <IButtonIcon
                icon="X"
                class="absolute right-3 top-1.5"
                @click="removeOption(index)"
              />
            </div>
          </div>
        </div>
      </template>
    </SortableDraggable>

    <IFormError :error="form.getError('options')" />
  </IFormGroup>
</template>

<script setup>
import { nextTick, ref } from 'vue'
import cloneDeep from 'lodash/cloneDeep'

const props = defineProps({
  form: { required: true, type: Object },
  options: { required: true },
})

const emit = defineEmits(['update:options'])

const optionNameRef = ref(null)
const addingOptionsViaText = ref(false)
const textOptions = ref('')

function cloneOptions() {
  return cloneDeep(props.options)
}

/** Set the display order of the options based at the current sorting */
function setDisplayOrder() {
  emitUpdateEvent(
    cloneOptions().map((option, index) => {
      option.display_order = index + 1

      return option
    })
  )
}

function updateOption(index, key, value) {
  let options = cloneOptions()
  options[index][key] = value
  emitUpdateEvent(options)
}

function newOptionAndFocus() {
  newOption()

  // Focus the last option
  nextTick(() => {
    optionNameRef.value.focus()
  })
}

function newOption(name = null) {
  emitUpdateEvent([
    ...props.options,
    {
      name,
      display_order: props.options.length + 1,
      swatch_color: null,
    },
  ])
}

function cancelAddOptionsViaText() {
  addingOptionsViaText.value = false
}

function convertTextToOptions() {
  if (!textOptions.value) {
    return
  }

  let options = cloneOptions()

  textOptions.value
    .split('\n')
    .filter(option => option)
    .forEach(option =>
      options.push({
        name: option,
        display_order: options.length + 1,
        swatch_color: null,
      })
    )

  emitUpdateEvent(options)
  cancelAddOptionsViaText()
}

async function removeOption(index) {
  if (props.options[index].id) {
    await Innoclapps.confirm()
  }

  let options = cloneOptions()
  options.splice(index, 1)
  emitUpdateEvent(options)
}

function emitUpdateEvent(options) {
  emit('update:options', options)
}
</script>
