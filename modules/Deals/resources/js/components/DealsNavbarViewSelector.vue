<template>
  <IButtonGroup>
    <IButton
      v-i-tooltip.bottom="$t('core::app.list_view')"
      :to="{ name: 'deal-index' }"
      variant="white"
      icon="TableCells"
      :active="active === 'index'"
    />

    <IButton
      v-i-tooltip.bottom="$t('deals::board.board')"
      :to="{ name: 'deal-board' }"
      variant="white"
      icon="ViewColumns"
      :active="active === 'board'"
    />
  </IButtonGroup>
</template>

<script setup>
defineProps({ active: { type: String, required: true } })
</script>
