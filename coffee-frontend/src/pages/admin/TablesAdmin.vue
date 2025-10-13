<script setup lang="ts">
import CrudTable from '@/components/admin/CrudTable.vue'
import { getTables, createTable, type TableDTO, updateTable, deleteTable } from '@/api/tables'
import { getBranches, type BranchDTO } from '@/api/branches'
import { onMounted, ref, computed } from 'vue'

const branches = ref<BranchDTO[]>([])
onMounted(async () => {
  branches.value = await getBranches()
})

const tableFields = computed(() => [
  { key: 'number', label: 'Table Number', type: 'text' as const, required: true },
  { key: 'capacity', label: 'Capacity', type: 'number' as const, required: true, min: 1 },
  { key: 'status', label: 'Status', type: 'select' as const, required: true, options: [
    { label: 'Available', value: 'available' },
    { label: 'Reserved', value: 'reserved' },
    { label: 'Out of Service', value: 'out_of_service' }
  ]},
  {
    key: 'branchId',
    label: 'Branch',
    type: 'select' as const,
    required: true,
    options: branches.value.map((b) => ({ label: b.name, value: b.id })),
  },
])
</script>

<template>
  <CrudTable
    title="Tables"
    :columns="[
      { key: 'id', label: 'ID' },
      { key: 'number', label: 'Number' },
      { key: 'capacity', label: 'Capacity' },
      { key: 'status', label: 'Status' },
    ]"
    :fetchAll="getTables"
    :createFields="tableFields"
    :onCreate="(payload) => createTable(payload as Pick<TableDTO, 'number' | 'capacity' | 'status' | 'branchId'>)"
    :enableEdit="true"
    :enableDelete="true"
    :editTitle="'Edit Table'"
    :deleteTitle="'Delete Table'"
    :deleteMessage="'Are you sure you want to delete this Table? This action cannot be undone.'"
    :onUpdate="(id, payload) => updateTable(id as number, payload as Partial<Pick<TableDTO, 'number' | 'capacity' | 'status' | 'branchId'>>)"
    :onDelete="(id) => deleteTable(id as number)"
    :enablePagination="true"
    :defaultPageSize="5"
  />
</template>
