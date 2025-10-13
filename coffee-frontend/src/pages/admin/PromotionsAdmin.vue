<script setup lang="ts">
import CrudTable from '@/components/admin/CrudTable.vue'
import { getPromotions, createPromotion, updatePromotion, deletePromotion, type PromotionDTO } from '@/api/promotions'
</script>

<template>
  <CrudTable
    title="Promotions"
    :columns="[
      { key: 'id', label: 'ID' },
      { key: 'code', label: 'Code' },
      { key: 'discountValue', label: 'Discount Value' },
      { key: 'discountType', label: 'Type' },
      { key: 'isActive', label: 'Active' },
    ]"
    :fetchAll="getPromotions"
    :createFields="[
      { key: 'code', label: 'Code', type: 'text', required: true },
      { key: 'description', label: 'Description', type: 'textarea' },
      { key: 'discountType', label: 'Discount Type', type: 'select', required: true, options: [{ label: 'Percent', value: 'percent' }, { label: 'Fixed', value: 'fixed' }] },
      { key: 'discountValue', label: 'Discount Value', type: 'number', required: true, min: 0, step: 0.01 },
      { key: 'startDate', label: 'Start Date', type: 'text' },
      { key: 'endDate', label: 'End Date', type: 'text' },
      { key: 'usageLimit', label: 'Usage Limit', type: 'number', min: 0 },
      { key: 'isActive', label: 'Active', type: 'checkbox' },
    ]"
    :onCreate="(payload) => createPromotion(payload as Pick<PromotionDTO, 'code' | 'description' | 'discountType' | 'discountValue' | 'startDate' | 'endDate' | 'usageLimit' | 'isActive'>)"
    :enableEdit="true"
    :enableDelete="true"
    :editTitle="'Edit Promotion'"
    :deleteTitle="'Delete Promotion'"
    :deleteMessage="'Are you sure you want to delete this promotion? This action cannot be undone.'"
    :onUpdate="(id, payload) => updatePromotion(id as number, payload as Partial<Pick<PromotionDTO, 'code' | 'description' | 'discountType' | 'discountValue' | 'startDate' | 'endDate' | 'usageLimit' | 'isActive'>>)"
    :onDelete="(id) => deletePromotion(id as number)"
    :enablePagination="true"
    :defaultPageSize="5"
  />
</template>
