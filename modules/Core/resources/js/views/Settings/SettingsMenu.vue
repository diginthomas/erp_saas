<template>
  <IVerticalNavigation
    id="settings-menu"
    class="mb-4 sm:sticky sm:top-10 lg:mb-0"
  >
    <IVerticalNavigationItem
      class="settings-menu-item-general"
      :title="$t('core::settings.general')"
      icon="Cog"
      :link-class="{
        'bg-neutral-50 dark:bg-neutral-600 text-primary-600 dark:text-primary-400':
          $route.name === 'settings-general',
      }"
      :icon-class="{
        'text-primary-600 dark:text-primary-300':
          $route.name === 'settings-general',
      }"
      href="/settings/general"
      :to="{ name: 'settings-general' }"
    />

    <IVerticalNavigationItem
      v-for="item in items"
      :key="item.id"
      :class="'settings-menu-item-' + item.id"
      :title="item.title"
      :to="item.route"
      :href="item.route ? $router.resolve(item.route).href : undefined"
      :icon="item.icon"
    >
      <template v-if="item.children.length">
        <IVerticalNavigationItem
          v-for="child in item.children"
          :key="item.id + '-' + child.id"
          :class="'settings-menu-item-' + child.id"
          :to="child.route"
          :href="child.route ? $router.resolve(child.route).href : undefined"
          :title="child.title"
          :icon="child.icon"
        />
      </template>
    </IVerticalNavigationItem>
  </IVerticalNavigation>
</template>

<script setup>
import { useApp } from '@/Core/composables/useApp'

const { scriptConfig } = useApp()

const items = scriptConfig('menu.settings')
</script>
