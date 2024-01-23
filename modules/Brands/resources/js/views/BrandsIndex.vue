<template>
  <ICard
    :header="$t('brands::brand.brands')"
    :overlay="brandsAreBeingFetched"
    no-body
  >
    <template #actions>
      <IButton
        :to="{ name: 'create-brand' }"
        icon="plus"
        :text="$t('brands::brand.create')"
      />
    </template>

    <ITable>
      <thead>
        <tr>
          <th v-t="'core::app.id'" class="text-left" width="5%" />

          <th v-t="'brands::brand.brand'" class="text-left" />

          <th />
        </tr>
      </thead>

      <tbody>
        <tr v-for="brand in brands" :key="brand.id">
          <td v-text="brand.id" />

          <td>
            <ILink
              :to="{ name: 'edit-brand', params: { id: brand.id } }"
              class="font-medium"
              :text="brand.name"
            />

            <IBadge
              v-if="brand.is_default"
              :text="$t('core::app.is_default')"
              variant="primary"
              class="ml-2"
            />
          </td>

          <td class="flex justify-end">
            <IMinimalDropdown>
              <IDropdownItem
                :to="{ name: 'edit-brand', params: { id: brand.id } }"
                :text="$t('core::app.edit')"
              />

              <IDropdownItem
                :text="$t('core::app.delete')"
                @click="$confirm(() => deleteBrand(brand.id))"
              />
            </IMinimalDropdown>
          </td>
        </tr>
      </tbody>
    </ITable>
  </ICard>
</template>

<script setup>
import { useBrands } from '../composables/useBrands'

const {
  orderedBrands: brands,
  brandsAreBeingFetched,
  deleteBrand,
} = useBrands()
</script>
