<template>
  <DetailFieldItem
    v-slot="{ hasValue, value }"
    :field="field"
    :is-floating="isFloating"
    :resource="resource"
    :resource-name="resourceName"
    :resource-id="resourceId"
  >
    <template v-if="hasValue">
      <div v-for="(phone, index) in value" :key="index">
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
  </DetailFieldItem>
</template>

<script setup>
defineProps(['resource', 'resourceName', 'resourceId', 'field', 'isFloating'])
</script>
