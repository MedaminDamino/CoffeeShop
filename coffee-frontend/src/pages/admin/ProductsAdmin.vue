<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import CrudTable from '@/components/admin/CrudTable.vue'
import { getProducts, createProduct } from '@/api/products'
import type { Product } from '@/interfaces/Product'
import { getCategories, type CategoryDTO } from '@/api/categories'

const categories = ref<CategoryDTO[]>([])
onMounted(async () => {
  categories.value = await getCategories()
})

const productFields = computed(() => [
  { key: 'name', label: 'Name', type: 'text' as const, required: true },
  { key: 'price', label: 'Price', type: 'number' as const, required: true, min: 0, step: 0.01 },
  { key: 'description', label: 'Description', type: 'textarea' as const },
  { key: 'imageUrl', label: 'Image URL', type: 'text' as const },
  { key: 'isActive', label: 'Active', type: 'checkbox' as const },
  {
    key: 'categoryId',
    label: 'Category',
    type: 'select' as const,
    required: true,
    options: categories.value.map((c) => ({ label: c.name, value: c.id })),
  },
])
</script>

<template>
  <CrudTable
    title="Products"
    :columns="[
      { key: 'id', label: 'ID' },
      { key: 'name', label: 'Name' },
      { key: 'price', label: 'Price' },
      { key: 'description', label: 'Description' },
      { key: 'isActive', label: 'Active' },
    ]"
    :fetchAll="getProducts"
    :createFields="productFields"
    :onCreate="(payload) => createProduct(payload as Pick<Product, 'name' | 'price' | 'description' | 'imageUrl' | 'isActive' | 'categoryId'>)"
  />
</template>
