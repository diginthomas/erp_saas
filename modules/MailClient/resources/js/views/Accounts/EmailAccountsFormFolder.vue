<!-- eslint-disable vue/no-mutating-props -->
<template>
  <div>
    <IFormCheckbox
      :id="'form-folder' + index + '-' + folder.name"
      :key="'form-folder' + index + '-' + folder.name"
      v-model:checked="folders[index].syncable"
      class="mb-1"
      :disabled="!folder.selectable"
      :label="folder.display_name"
    />

    <template v-if="folder.children">
      <EmailAccountsFormFolder
        v-for="(child, childIndex) in folder.children"
        :key="childIndex + child.name"
        class="ml-4 mt-2"
        :folder="child"
        :index="childIndex"
        :folders="folder.children"
      />
    </template>
  </div>
</template>

<script setup>
defineOptions({
  name: 'EmailAccountsFormFolder',
})

defineProps({
  folder: { required: true, type: Object },
  folders: { required: true, type: Array },
  index: { required: true, type: Number },
})
</script>
