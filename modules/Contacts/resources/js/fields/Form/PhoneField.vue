<template>
  <BaseFormField
    v-slot="{ fieldId }"
    :resource-name="resourceName"
    :field="field"
    :value="modelValue"
    :is-floating="isFloating"
  >
    <FormFieldGroup
      :field="field"
      :label="field.label"
      :field-id="fieldId"
      :validation-errors="validationErrors"
      class="multiple"
    >
      <IFormGroup
        v-for="(phone, index) in phones"
        :key="index"
        class="rounded-md"
      >
        <div class="flex">
          <div class="relative flex grow items-stretch focus-within:z-10">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3">
              <Icon
                :icon="phone.type == 'mobile' ? 'DeviceMobile' : 'Phone'"
                class="size-5 text-neutral-500 dark:text-neutral-300"
              />
            </div>

            <IFormInput
              v-model="phones[index].number"
              rounded="left"
              class="pl-10"
              :name="field.attribute + '.' + index + '.number'"
              @input="searchDuplicateRecord(index, phones[index].number)"
            />
          </div>

          <IDropdown
            adaptive-width
            class="-ml-px w-28 shadow-sm"
            truncate
            size="md"
            :text="
              phones[index].type
                ? field.types[phones[index].type]
                : $t('contacts::fields.phone.types.type')
            "
            :full-width="false"
            :rounded="false"
          >
            <IDropdownItem
              v-for="(typeLabel, id) in field.types"
              :key="id"
              :text="typeLabel"
              @click="phones[index].type = id"
            />
          </IDropdown>

          <IButton
            icon="X"
            rounded="right"
            variant="white"
            class="-ml-px shadow-sm"
            @click="removePhone(index)"
          />
        </div>

        <IFormError
          v-if="
            validationErrors &&
            validationErrors.array &&
            validationErrors.array[field.attribute + '.' + index + '.number']
          "
          :error="
            validationErrors.array[field.attribute + '.' + index + '.number']
          "
        />

        <IAlert
          v-if="duplicates[index]"
          v-slot="{ variant }"
          dismissible
          class="mt-1"
          @dismissed="duplicates[index] = null"
        >
          <I18nT
            scope="global"
            :keypath="field.checkDuplicatesWith.lang_keypath"
          >
            <template #display_name>
              <span class="font-medium">
                {{ duplicates[index].display_name }}
              </span>
            </template>
          </I18nT>

          <IAlertActions>
            <IButtonMinimal
              tag="a"
              :href="duplicates[index].path"
              target="_blank"
              rel="noopener noreferrer"
              :variant="variant"
              icon="ExternalLink"
              :text="$t('core::app.view_record')"
            />
          </IAlertActions>
        </IAlert>
      </IFormGroup>

      <ILink
        :text="$t('contacts::fields.phone.add')"
        class="mr-2 block text-right"
        @click="newPhone"
      />
    </FormFieldGroup>
  </BaseFormField>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import debounce from 'lodash/debounce'
import omit from 'lodash/omit'

import { defineFormDataObjectUniqueId } from '@/Core/composables/useForm'
import FormFieldGroup from '@/Core/fields/FormFieldGroup.vue'

const props = defineProps({
  field: { type: Object, required: true },
  modelValue: { type: Array, default: () => [] },
  resourceName: String,
  resourceId: [String, Number],
  validationErrors: Object,
  isFloating: Boolean,
})

const emit = defineEmits(['update:modelValue', 'setInitialValue'])

const phones = ref([])
const duplicates = ref({})

const callingPrefix = computed(() => props.field.callingPrefix)
const totalPhones = computed(() => phones.value.length)

watch(totalPhones, newVal => {
  if (newVal === 0) newPhone()
})

watch(
  phones,
  newVal =>
    updateModelValue(
      newVal
        .map(phone =>
          defineFormDataObjectUniqueId(omit(phone, 'id'), phone._formUniqueId)
        )
        .filter(phone => Boolean(phone.number))
        .filter(phone => {
          return (
            !callingPrefix.value ||
            phone.number.trim() !== callingPrefix.value.trim()
          )
        })
    ),
  { deep: true }
)

function updateModelValue(value) {
  emit('update:modelValue', value)
}

function setInitialValue() {
  let initialValue = props.field.value || []

  emit('setInitialValue', initialValue.map(toNumber))
}

function removePhone(index) {
  duplicates.value[index] = null
  phones.value.splice(index, 1)

  if (totalPhones.value === 0) newPhone()
}

function newPhone() {
  phones.value.push(
    defineFormDataObjectUniqueId({
      number: callingPrefix.value || '',
      type: props.field.type,
    })
  )
}

function toNumber(phone) {
  return {
    number: phone.number,
    type: phone.type,
  }
}

function prepareField() {
  phones.value = (props.field.value || []).map(toNumber)

  phones.value.forEach(defineFormDataObjectUniqueId)

  setInitialValue()

  if (phones.value.length === 0) newPhone()
}

const searchDuplicateRecord = debounce((index, number) => {
  if (
    !checksForDuplicates.value ||
    props.resourceId ||
    !number ||
    (callingPrefix.value && callingPrefix.value === number)
  ) {
    duplicates.value[index] = null

    return
  }

  makeDuplicateCheckRequest(number).then(
    duplicate => (duplicates.value[index] = duplicate)
  )
}, 700)

const checksForDuplicates = computed(
  () =>
    props.field.checkDuplicatesWith &&
    Object.keys(props.field.checkDuplicatesWith).length > 0
)

async function makeDuplicateCheckRequest(query) {
  const { data } = await Innoclapps.request(
    props.field.checkDuplicatesWith.url,
    {
      params: {
        q: query,
        ...props.field.checkDuplicatesWith.params,
      },
    }
  )

  return data.length > 0 ? data[0] : null
}

prepareField()
</script>
