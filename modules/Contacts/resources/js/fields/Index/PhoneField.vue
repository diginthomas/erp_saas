<template>
  <IndexFieldItem
    v-slot="{ hasValue, value }"
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <template v-if="hasValue">
      <div
        v-for="(phone, index) in value"
        :key="index"
        :class="!column.wrap ? 'mr-2.5 inline last:mr-0' : ''"
      >
        <IDropdown
          tag="a"
          class="link link-primary"
          :href="'tel:' + phone.number"
          no-caret
        >
          <template #toggle-content>
            <span
              v-i-tooltip="$t('contacts::fields.phone.types.' + phone.type)"
              v-text="phone.number"
            />
          </template>

          <slot name="start" :phone="phone" />

          <IButtonCopy
            :text="phone.number"
            :success-message="$t('contacts::fields.phone.copied')"
            tag="IDropdownItem"
          >
            {{ $t('core::app.copy') }}
          </IButtonCopy>

          <IDropdownItem
            :href="'tel:' + phone.number"
            :text="$t('core::app.open_in_app')"
          />
        </IDropdown>
      </div>
    </template>

    <span v-else>&mdash;</span>
  </IndexFieldItem>
</template>

<script setup>
defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
