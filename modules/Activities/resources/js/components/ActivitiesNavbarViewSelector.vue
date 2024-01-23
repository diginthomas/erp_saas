<template>
  <IButtonGroup>
    <IButton
      v-i-tooltip.bottom="$t('core::app.list_view')"
      :to="{ name: 'activity-index' }"
      variant="white"
      icon="TableCells"
      :active="active === 'index'"
    />

    <IButton
      v-i-tooltip.bottom="$t('activities::calendar.calendar')"
      :to="{ name: 'activity-calendar' }"
      variant="white"
      icon="Calendar"
      :active="active === 'calendar'"
    />
  </IButtonGroup>
</template>

<script setup>
defineProps({ active: { type: String, required: true } })
</script>
