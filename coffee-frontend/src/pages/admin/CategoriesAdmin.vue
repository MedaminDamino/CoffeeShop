<script setup lang="ts">
import CrudTable from '@/components/admin/CrudTable.vue'
import { getCategories, createCategory, updateCategory, deleteCategory, type CategoryDTO } from '@/api/categories'
</script>

<template>
  <CrudTable
    title="Categories"
    :columns="[
      { key: 'id', label: 'ID' },
      { key: 'name', label: 'Name' },
      { key: 'description', label: 'Description' },
    ]"
    :fetchAll="getCategories"
    :createFields="[
      { key: 'name', label: 'Name', type: 'text', required: true },
      { key: 'description', label: 'Description', type: 'textarea' },
    ]"
    :onCreate="(payload) => createCategory(payload as Pick<CategoryDTO, 'name' | 'description'>)"
    :enableEdit="true"
    :enableDelete="true"
    :editTitle="'Edit Category'"
    :deleteTitle="'Delete Category'"
    :deleteMessage="'Are you sure you want to delete this category? This action cannot be undone.'"
    :onUpdate="(id, payload) => updateCategory(id as number, payload as Partial<Pick<CategoryDTO, 'name' | 'description'>>)"
    :onDelete="(id) => deleteCategory(id as number)"
    :enablePagination="true"
    :defaultPageSize="5"
  />
</template>
