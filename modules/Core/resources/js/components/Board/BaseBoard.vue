<template>
  <div>
    <div class="board-top px-4 py-4 sm:px-6">
      <slot name="top"></slot>
    </div>

    <div
      :id="boardId"
      class="board block space-x-4 overflow-x-scroll whitespace-nowrap px-4 pb-2 sm:px-6"
      :class="boardClass"
    >
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  boardClass: [String, Array, Object],
  boardId: { required: true, type: String },
})
</script>

<style>
:root {
  --board-top-height: 70px;
}

.board {
  height: calc(100vh - (var(--navbar-height) + var(--board-top-height)));
}

@media (min-width: 1024px) {
  .board-top {
    height: var(--board-top-height);
    max-height: var(--board-top-height);
  }
}
</style>
